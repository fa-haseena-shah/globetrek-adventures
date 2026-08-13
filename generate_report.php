<?php 
    session_start();
    // admin-only guard
    if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
        if(!isset($_SESSION['user_id'])) {
            header("Location: login.php");
        } elseif($_SESSION['role'] === 'Customer') {
            header("Location: traveller_dashboard.php");
        } elseif($_SESSION['role'] === 'Staff') {
            header("Location: dashboard.php");
        } else {
            header("Location: login.php");
        }
        exit();
    }
    include "db_helper.php";

    // using GET instead of POST because the links can be saved as bookmarks for later reference
    $reportType = $_GET['report_type'] ?? 'customer';
    $startDate = $_GET['start_date'] ?? date('Y-m-d', strtotime('-30 days'));
    $endDate = $_GET['end_date'] ?? date('Y-m-d');
    $endDateTime = $endDate . ' 23:59:59';

    // only run queries and generate PDF when export is requested
    if(isset($_GET['export_pdf'])) {

        if($reportType === 'sales') {
            $totalRevenue = fetchOne($conn, "
            SELECT SUM(p.amount) AS total
            FROM payment p
            WHERE p.status = 'Successful'
            AND p.created_at BETWEEN ? AND ?
            ", "ss", [$startDate, $endDateTime]);

            $revenueByType = fetchAll($conn, "
            SELECT 
                CASE 
                    WHEN fb.booking_id IS NOT NULL THEN 'Tour Package'
                    WHEN fb.cpkg_id IS NOT NULL THEN 'Custom Tour'
                    WHEN bs.accomm_booking_id IS NOT NULL THEN 'Accommodation'
                    WHEN bs.transport_booking_id IS NOT NULL THEN 'Transport'
                END AS service_type,
                SUM(p.amount) AS revenue
            FROM payment p
            JOIN final_booking fb ON p.final_booking_id = fb.final_booking_id
            LEFT JOIN booking_service bs 
                ON bs.final_booking_id = fb.final_booking_id
                AND fb.booking_id IS NULL 
                AND fb.cpkg_id IS NULL
            WHERE p.status = 'Successful'
            AND p.created_at BETWEEN ? AND ?
            GROUP BY service_type", "ss", [$startDate, $endDateTime]);

            $bestSellingPackages = fetchAll($conn, "
            SELECT tour_packages.pkg_name, COUNT(*) AS bookings_count, SUM(payment.amount) AS revenue
            FROM tour_booking
            JOIN tour_packages ON tour_booking.pkg_id = tour_packages.pkg_id
            JOIN final_booking ON final_booking.booking_id = tour_booking.booking_id
            JOIN payment ON payment.final_booking_id = final_booking.final_booking_id
            WHERE tour_booking.status IN ('Confirmed', 'Payment Complete')
            AND payment.status = 'Successful'
            AND payment.created_at BETWEEN ? AND ?
            GROUP BY tour_packages.pkg_id
            ORDER BY bookings_count DESC
            LIMIT 5
            ", "ss", [$startDate, $endDateTime]);

            $popularAccommodations = fetchAll($conn, "
            SELECT accommodation.name, COUNT(*) AS booking_count
            FROM accommodation_booking
            JOIN accommodation ON accommodation_booking.accommodation_id = accommodation.accommodation_id
            JOIN booking_service ON booking_service.accomm_booking_id = accommodation_booking.accomm_booking_id
            WHERE accommodation_booking.created_at BETWEEN ? AND ?
            GROUP BY accommodation.accommodation_id
            ORDER BY booking_count DESC", "ss", [$startDate, $endDateTime]);

            $popularTransports = fetchAll($conn, "
            SELECT transport.name, COUNT(*) AS booking_count
            FROM transport_booking
            JOIN booking_service ON booking_service.transport_booking_id = transport_booking.transport_booking_id
            JOIN transport ON booking_service.transport_id = transport.transport_id
            WHERE transport_booking.created_at BETWEEN ? AND ?
            GROUP BY transport.transport_id
            ORDER BY booking_count DESC
            ", "ss", [$startDate, $endDateTime]);

        } else {
            $registeredCustomers = fetchOne($conn, "
            SELECT COUNT(*) AS total 
            FROM users WHERE role = 'Customer'
            AND created_at BETWEEN ? AND ?
            ", "ss", [$startDate, $endDateTime]);

            $topCustomers = fetchAll($conn, "
            SELECT users.user_id, users.full_name, SUM(payment.amount) AS total_spent
            FROM payment
            JOIN users ON payment.user_id = users.user_id
            WHERE payment.status = 'Successful'
            AND payment.created_at BETWEEN ? AND ?
            GROUP BY users.user_id
            ORDER BY total_spent DESC
            LIMIT 5", "ss", [$startDate, $endDateTime]);
        }

        // using fdf for pdf download
        require 'fpdf/fpdf.php';

        // create new instance of fpdf
        $pdf = new FPDF('L', 'mm', 'A4');
        // format pages
        $pdf->AddPage();
        $pdf->SetFont('Arial', 'B', 16);
        $pdf->Cell(0, 10, ($reportType === 'sales' ? 'Sales Report' : 'Customer Report'), 0, 1, 'C');

        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(0, 8, date('d M Y', strtotime($startDate)) . ' - ' . date('d M Y', strtotime($endDate)), 0, 1, 'C');
        $pdf->Ln(5);

        // manually set tables thru cells
        if($reportType === 'sales') {
            $pdf->SetFont('Arial', 'B', 12);
            $pdf->Cell(0, 8, 'Total Revenue Made', 0, 1);
            $pdf->SetFont('Arial', '', 10);
            $pdf->Cell(0, 8, 'LKR ' . number_format($totalRevenue['total'] ?? 0, 2), 1, 1);
            $pdf->Ln(5);

            $pdf->SetFont('Arial', 'B', 12);
            $pdf->Cell(0, 8, 'Revenue Per Service', 0, 1);
            $pdf->SetFont('Arial', 'B', 10);
            $pdf->Cell(95, 8, 'Service', 1);
            $pdf->Cell(95, 8, 'Revenue (LKR)', 1, 1);
            $pdf->SetFont('Arial', '', 10);
            if(!empty($revenueByType)) {
                foreach($revenueByType as $rbt) {
                    $pdf->Cell(95, 8, $rbt['service_type'], 1);
                    $pdf->Cell(95, 8, number_format($rbt['revenue'], 2), 1, 1);
                }
            } else {
                $pdf->Cell(190, 8, 'No reportable information found...', 1, 1, 'C');
            }
            $pdf->Ln(5);

            $pdf->SetFont('Arial', 'B', 12);
            $pdf->Cell(0, 8, 'Best Sellers - Packages', 0, 1);
            $pdf->SetFont('Arial', 'B', 10);
            $pdf->Cell(95, 8, 'Package Name', 1);
            $pdf->Cell(45, 8, 'Bookings', 1);
            $pdf->Cell(50, 8, 'Revenue (LKR)', 1, 1);
            $pdf->SetFont('Arial', '', 10);
            if(!empty($bestSellingPackages)) {
                foreach($bestSellingPackages as $bsp) {
                    $pdf->Cell(95, 8, $bsp['pkg_name'], 1);
                    $pdf->Cell(45, 8, $bsp['bookings_count'], 1);
                    $pdf->Cell(50, 8, number_format($bsp['revenue'], 2), 1, 1);
                }
            } else {
                $pdf->Cell(190, 8, 'No reportable information found...', 1, 1, 'C');
            }
            $pdf->Ln(5);

            $pdf->SetFont('Arial', 'B', 12);
            $pdf->Cell(0, 8, 'Best Sellers - Accommodation', 0, 1);
            $pdf->SetFont('Arial', 'B', 10);
            $pdf->Cell(95, 8, 'Accommodation Name', 1);
            $pdf->Cell(95, 8, 'Bookings Count', 1, 1);
            $pdf->SetFont('Arial', '', 10);
            if(!empty($popularAccommodations)) {
                foreach($popularAccommodations as $pa) {
                    $pdf->Cell(95, 8, $pa['name'], 1);
                    $pdf->Cell(95, 8, $pa['booking_count'], 1, 1);
                }
            } else {
                $pdf->Cell(190, 8, 'No reportable information found...', 1, 1, 'C');
            }
            $pdf->Ln(5);

            $pdf->SetFont('Arial', 'B', 12);
            $pdf->Cell(0, 8, 'Best Sellers - Transport', 0, 1);
            $pdf->SetFont('Arial', 'B', 10);
            $pdf->Cell(95, 8, 'Transport Name', 1);
            $pdf->Cell(95, 8, 'Bookings Count', 1, 1);
            $pdf->SetFont('Arial', '', 10);
            if(!empty($popularTransports)) {
                foreach($popularTransports as $pt) {
                    $pdf->Cell(95, 8, $pt['name'], 1);
                    $pdf->Cell(95, 8, $pt['booking_count'], 1, 1);
                }
            } else {
                $pdf->Cell(190, 8, 'No reportable information found...', 1, 1, 'C');
            }

        } else {
            $pdf->SetFont('Arial', 'B', 12);
            $pdf->Cell(0, 8, 'Total Registered Customers', 0, 1);
            $pdf->SetFont('Arial', '', 10);
            $pdf->Cell(0, 8, $registeredCustomers['total'] ?? 0, 1, 1);
            $pdf->Ln(5);

            $pdf->SetFont('Arial', 'B', 12);
            $pdf->Cell(0, 8, 'Highest Spending Customers - VIP', 0, 1);
            $pdf->SetFont('Arial', 'B', 10);
            $pdf->Cell(60, 8, 'Customer ID', 1);
            $pdf->Cell(95, 8, 'Customer Name', 1);
            $pdf->Cell(45, 8, 'Total Spent (LKR)', 1, 1);
            $pdf->SetFont('Arial', '', 10);
            if(!empty($topCustomers)) {
                foreach($topCustomers as $tc) {
                    $pdf->Cell(60, 8, $tc['user_id'], 1);
                    $pdf->Cell(95, 8, $tc['full_name'], 1);
                    $pdf->Cell(45, 8, number_format($tc['total_spent'], 2), 1, 1);
                }
            } else {
                $pdf->Cell(200, 8, 'No reportable information found...', 1, 1, 'C');
            }
        }

        $pdf->Ln(10);
        $pdf->SetFont('Arial', 'I', 9);
        $pdf->Cell(0, 8, 'Generated on ' . date('d M Y'), 0, 1, 'C');

        $pdf->Output('D', $reportType . "_report_" . date('Y-m-d') . ".pdf");
        exit();
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generate Sales & Customer Reports</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include "staff_navbar.php"; ?>
    <div class="container">
        <div class="report-card">
            <h1 class="mb-4 mt-5">Generate Report</h1>
            <p class="text-muted">
                Select a report type and date range, then download it as a PDF.
            </p>
            <form action="generate_report.php" method="get">
                <div class="row step-row align-items-center mb-4">
                    <div class="col-md-3">
                        <h5 class="mt-3">STEP 1</h5>
                        <p>Select report type</p>
                    </div>
                    <div class="col-md-9">
                        <div class="d-flex gap-5">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="report_type"
                                    id="customer" value="customer" <?= $reportType === 'customer' ? 'checked' : '' ?>>
                                <label class="form-check-label" for="customer">Customer</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="report_type"
                                    id="sales" value="sales" <?= $reportType === 'sales' ? 'checked' : '' ?>>
                                <label class="form-check-label" for="sales">Sales</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row step-row align-items-center mb-4">
                    <div class="col-md-3">
                        <h5 class="mt-3">STEP 2</h5>
                        <p>Select dates</p>
                    </div>
                    <div class="col-md-9">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <input type="date" name="start_date" class="form-control" value="<?= htmlspecialchars($startDate) ?>">
                            </div>
                            <div class="col-md-4">
                                <input type="date" name="end_date" class="form-control" value="<?= htmlspecialchars($endDate) ?>">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row step-row align-items-center">
                    <div class="col-md-3">
                        <h5 class="mt-3">STEP 3</h5>
                        <p>Download Report!</p>
                    </div>
                    <div class="col-md-9">
                        <input type="hidden" name="export_pdf" value="1">
                        <button type="submit" class="btn btn-primary m-0">
                            Generate Report
                        </button>
                        <p class="text-muted">Will be downloaded as PDF</p>
                    </div>
                </div>
            </form>
        </div>
    </div>
</body>
</html>