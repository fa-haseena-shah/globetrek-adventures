<?php
    session_start();
    include "db_helper.php";
    include "fetch_booking_details.php";
    $type = $_GET['type'];
    $finalBookingId = $_GET['id'];

    $bd = getBookingDetails($conn, $type, $finalBookingId);

    if(!$bd && ($_SESSION['role']==='Admin' || $_SESSION['role'] === 'Staff')) {
        header("Location: dashboard.php");
        exit();
    } if(!$bd && $_SESSION['role']==='Customer') {
        header("Location: traveller_dashboard.php");
        exit();
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Details</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <script defer src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="style.css">
    <script defer src="https://kit.fontawesome.com/e5a3f33c9d.js" crossorigin="anonymous"></script>
</head>
<body>
    <?php if($_SESSION['role']!=='Admin' && $_SESSION['role']!=='Staff'):?>
        <?php include "navbar.php";?>
    <?php else:?>
        <?php include "staff_navbar.php";?>
    <?php endif;?>
    <section class="booking-details-card m-5">
        <div class="text-center mb-4">
            <h3 class="summary-title">Confirmed Booking Details - <?= ucfirst($type) ?></h3>
            <p class="text-muted small mb-0">Reference #<?= $finalBookingId ?></p>
        </div>

        <div class="row justify-content-center">
            <div class="col-12 col-lg-6">
                <div class="p-4 booking-summary">

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
                        <span class="fw-bold">TOTAL AMOUNT</span>
                        <span class="fw-bold fs-5">LKR <?= number_format($bd['total_amount'], 2) ?></span>
                    </div>
                </div>
            </div>
        </div>
    </section>
</body>
</html>