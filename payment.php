<?php session_start();
    $errors = [];
    $success = "";
    $userId = $_SESSION['user_id'] ?? null;
    
    if(!$userId) {
        header("Location: login.php");
        exit();
    }

    if($_SESSION['role'] === 'Staff' || $_SESSION['role'] === 'Admin') {
        header("Location: dashboard.php");
        exit();
    }

    include "db_helper.php";
    include "fetch_booking_details.php";

    $type = $_GET['type'] ?? '';
    $id = $_GET['id'] ?? '';

    switch($type) {
        case 'tour':
            $finalBookingId = fetchOne($conn, "
            SELECT final_booking.final_booking_id
            FROM final_booking
            WHERE final_booking.booking_id = ?
            ", "i", [$id]);
            if (!$finalBookingId) {
                die("Final booking not found.");
            }

            $finalBookingId = $finalBookingId['final_booking_id'];

            
            $bookingDetails = fetchOne($conn, "
            SELECT * FROM tour_booking 
            WHERE booking_id = ?
            ", "i", [$id]);

            break;

        case 'customized':
            $finalBookingId = fetchOne($conn, "
            SELECT final_booking.final_booking_id
            FROM final_booking
            WHERE final_booking.cpkg_id = ?
            ", "i", [$id]);
            if (!$finalBookingId) {
                die("Final booking not found.");
            }

            $finalBookingId = $finalBookingId['final_booking_id'];

            $bookingDetails = fetchOne($conn, "
            SELECT * FROM customized_tours
            WHERE cpkg_id = ?
            ", "i", [$id]);
            break;

        case 'accommodation':
            $finalBookingId = fetchOne($conn, "
            SELECT final_booking.final_booking_id
            FROM final_booking
            JOIN booking_service ON booking_service.final_booking_id = final_booking.final_booking_id
            WHERE booking_service.accomm_booking_id = ?
            ", "i", [$id]);
            if (!$finalBookingId) {
                die("Final booking not found.");
            }

            $finalBookingId = $finalBookingId['final_booking_id'];


            $bookingDetails = fetchOne($conn, "
            SELECT * FROM accommodation_booking 
            WHERE accomm_booking_id = ?
            ", "i", [$id]);
            break;

        case 'transport':
            $finalBookingId = fetchOne($conn, "
            SELECT final_booking.final_booking_id
            FROM final_booking
            JOIN booking_service ON booking_service.final_booking_id = final_booking.final_booking_id
            WHERE booking_service.transport_booking_id = ?
            ", "i", [$id]);
            if (!$finalBookingId) {
                die("Final booking not found.");
            }

            $finalBookingId = $finalBookingId['final_booking_id'];


            $bookingDetails = fetchOne($conn, "
            SELECT * FROM transport_booking 
            WHERE transport_booking_id = ?
            ", "i", [$id]);

            break;
    }

    $finalBooking = fetchOne($conn, "
    SELECT * FROM final_booking
    WHERE final_booking_id = ?
    ", "i", [$finalBookingId]);

    $bd = getBookingDetails($conn, $type, $finalBookingId);
    if(!$bd && $_SESSION['role']==='Customer') {
        header("Location: traveller_dashboard.php");
        exit();
    }

    if(isset($_POST['pay'])) {
        $paymentAmount = trim($_POST['pay_amnt'] ?? '');
        $accountNumber = trim($_POST['acc_num'] ?? '');
        $cardName = trim($_POST['name_card'] ?? '');
        $expiryDate = trim($_POST['expiry_date'] ?? '');
        $cvv = trim($_POST['cvv'] ?? '');

        if (empty($paymentAmount)) {
            $errors[] = "Payment amount is required.";
        }

        if (empty($accountNumber)) {
            $errors[] = "Card number is required.";
        }

        if (empty($cardName)) {
            $errors[] = "Cardholder name is required.";
        }

        if (empty($expiryDate)) {
            $errors[] = "Expiry date is required.";
        }

        if (empty($cvv)) {
            $errors[] = "CVV is required.";
        }
        
        if(count($errors) === 0) {
            $paymentId = insertRow($conn, "
            INSERT INTO payment (final_booking_id,user_id,amount,status) VALUES (?, ?, ?, ?)
            ", "iids", [$finalBookingId,$userId,$paymentAmount,'Successful']);
        switch($type) {
                case 'tour':
                    updateRow($conn, "
                        UPDATE tour_booking
                        SET status = 'Payment Complete'
                        WHERE booking_id = ?
                    ", "i", [$id]);
                    break;

                case 'customized':
                    updateRow($conn, "
                        UPDATE customized_tours
                        SET status = 'Payment Complete'
                        WHERE cpkg_id = ?
                    ", "i", [$id]);
                    break;

                case 'accommodation':
                    updateRow($conn, "
                        UPDATE accommodation_booking
                        SET status = 'Payment Complete'
                        WHERE accomm_booking_id = ?
                    ", "i", [$id]);
                    break;

                case 'transport':
                    updateRow($conn, "
                        UPDATE transport_booking
                        SET status = 'Payment Complete'
                        WHERE transport_booking_id = ?
                    ", "i", [$id]);
                    break;
            }
            $_SESSION['payment_id'] = $paymentId;
            $_SESSION['total_paid'] = $paymentAmount;
            header("Location: payment_success.php");
            exit();
        }
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Now Paying</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="style.css">
    <script defer src="https://kit.fontawesome.com/e5a3f33c9d.js" crossorigin="anonymous"></script>
    <script defer src="validation_script.js"></script>
</head>
<body class="bg-light">
    <?php include "navbar.php";?>
     <?php if(!empty($success)): ?>
        <div class="alert alert-success"><?= $success ?></div>
    <?php endif; ?>
    <?php if(!empty($errors)): ?>
        <?php foreach($errors as $error): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endforeach; ?>
    <?php endif; ?>
    <main class="container py-5">
        <div class="row justify-content-center mb-4">
            <div class="col-12 col-lg-8">
                <section class="section-header text-center text-lg-start">
                    <h2><i class="fa-regular fa-credit-card"></i> Now Paying</h2>
                </section>
            </div>
        </div>

        <div class="row g-4 justify-content-center">
            <!-- payment form-->
            <div class="col-12 col-lg-5">
                <div class="bg-white shadow-sm p-4 h-100">
                    <div>
                        <form action="payment.php?type=<?= $type?>&id=<?=$id?>" method="post" onsubmit="return validatePaymentForm()">
                            <div class="section-title">Card Details</div>
                            <div class="row g-3">
                                <div class="col-md-12">
                                    <label class="form-label">Account Number</label>
                                    <input type="number" class="form-control" name="acc_num" id="acc_num">
                                </div>
                            </div>
                            <div class="row g-3">
                                <div class="col-md-12">
                                    <label class="form-label">Name On Card</label>
                                    <input type="text" class="form-control" name="name_card" id="name_card">
                                </div>
                            </div>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Expiry Date</label>
                                    <input type="text" class="form-control" placeholder="MM/YY" name="expiry_date" id="expiry_date">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">CVV</label>
                                    <input type="text" class="form-control" placeholder="3-digit code" name="cvv" id="cvv">
                                </div>
                            </div>
                            <input type="hidden" name="pay_amnt" id="pay_amnt" value="<?= $finalBooking['total_amount'] ?>">
                            <button type="submit" name="pay" class="btn btn-booking w-100">Pay</button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- final booking summary -->
            <div class="col-12 col-lg-5">
                <div class="p-4 h-100 booking-summary">
                    <div class="text-center mb-4">
                        <h5 class="fw-bold">Booking Summary</h5>
                        <p class="text-muted small mb-0">Reference #<?= $finalBookingId ?></p>
                    </div>

                    <?php if($type === 'tour'): ?>
                        <div class="summary-section mb-3">
                            <h6 class="text-uppercase small text-muted mb-2">Package</h6>
                            <p class="fw-semibold mb-0"><?= $bd['pkg_name'] ?></p>
                        </div>
                    <?php elseif($type === 'customized'): ?>
                        <div class="summary-section mb-3">
                            <h6 class="text-uppercase small text-muted mb-2">Trip Preferences</h6>
                            <p class="mb-1"><strong>Destinations:</strong> <?= $bd['destination_notes'] ?: '—' ?></p>
                            <p class="mb-0"><strong>Activities:</strong> <?= $bd['activity_notes'] ?: '—' ?></p>
                        </div>
                    <?php elseif($type === 'transport'): ?>
                        <div class="summary-section mb-3">
                            <h6 class="text-uppercase small text-muted mb-2">Transport</h6>
                            <p class="fw-semibold mb-0"><?= $bd['transports'] ?></p>
                            <p class="text-muted small mb-0"><?= $bd['vehicle_type'] ?></p>
                        </div>
                    <?php elseif($type === 'accommodation'): ?>
                        <div class="summary-section mb-3">
                            <h6 class="text-uppercase small text-muted mb-2">Accommodation</h6>
                            <p class="fw-semibold mb-0"><?= $bd['accomm_name'] ?></p>
                        </div>
                    <?php endif; ?>

                    <?php if($type === 'tour' || $type === 'customized'): ?>
                        <div class="summary-section mb-3">
                            <h6 class="text-uppercase small text-muted mb-2">Included Services</h6>
                            <div class="d-flex justify-content-between py-1">
                                <span class="text-muted">Accommodation</span>
                                <span><?= $bd['accommodations'] ?: 'Not Assigned' ?></span>
                            </div>
                            <div class="d-flex justify-content-between py-1">
                                <span class="text-muted">Transport</span>
                                <span><?= $bd['transports'] ?: 'Not Assigned' ?></span>
                            </div>
                            <div class="d-flex justify-content-between py-1">
                                <span class="text-muted">Guide</span>
                                <span><?= $bd['guides'] ?: 'Not Assigned' ?></span>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if(!empty($bd['notes'])): ?>
                        <div class="summary-section mb-3">
                            <h6 class="text-uppercase small text-muted mb-2">Additional Notes</h6>
                            <p class="mb-0 text-muted small"><?= $bd['notes'] ?></p>
                        </div>
                    <?php endif; ?>

                    <div class="d-flex justify-content-between align-items-center border-top pt-3 mt-3">
                        <span class="fw-bold">TOTAL PRICE</span>
                        <span class="fw-bold fs-5">LKR <?= number_format($finalBooking['total_amount'], 2) ?></span>
                    </div>
                    <p class="text-center text-muted small mt-2">Pay this amount to confirm your booking</p>
                </div>
            </div>
        </div>
    </main>
</body>
</html>