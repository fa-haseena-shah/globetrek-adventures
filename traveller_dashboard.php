<?php 
session_start();
    $errors = [];
    $success = [];
    $userId = $_SESSION['user_id'] ?? null;
    $userName = $_SESSION['name'];
    $role = $_SESSION['role'];
    
    if(!$userId) {
        header("Location: login.php");
        exit();
    }

    if($role === 'Staff' || $role === 'Admin') {
        header("Location: dashboard.php");
        exit();
    }

    include "db_helper.php";

    if(isset($_POST['cancel'])) {
        $type = $_POST['type'] ?? '';
        $id = $_POST['id'] ?? '';

        if(!$type || !$id) {
            $errors[] = "Invalid request.";
        } else {
            switch($type) {
                case 'tour':
                    $table = 'tour_booking';
                    $idField = 'booking_id';
                    break;
                case 'accommodation':
                    $table = 'accommodation_booking';
                    $idField = 'accomm_booking_id';
                    break;
                case 'transport':
                    $table = 'transport_booking';
                    $idField = 'transport_booking_id';
                    break;
                case 'custom':
                    $table = 'customized_tours';
                    $idField = 'cpkg_id';
                    break;
                default:
                    $errors[] = "Invalid booking type.";
            }
        }

        if(count($errors) === 0) {
            updateRow($conn, "
            UPDATE $table
            SET status = ?
            WHERE $idField = ? AND user_id = ?
            ", "sii", ['Cancelled', $id, $userId]);
            $success[] = "Booking cancelled successfully."; 
            
            header("Location: traveller_dashboard.php");
            exit();
        }
    }

    // fetch all bookings made by this user
    // we use left join with final_booking to get the total amount
    $tourBookings = fetchAll($conn, "
    SELECT tour_booking.*, tour_packages.pkg_name, final_booking.final_booking_id, final_booking.total_amount AS total
    FROM tour_booking
    JOIN tour_packages
    ON tour_booking.pkg_id = tour_packages.pkg_id
    LEFT JOIN final_booking 
    ON final_booking.booking_id = tour_booking.booking_id
    WHERE tour_booking.user_id = ?
    ORDER BY tour_booking.created_at DESC
    ", "i", [$userId]);

    // for accommodation and transport standalone bookings, we need to ledft join with booking_service
    // since services are referred in booking_service which links to final_booking where the total amount is
    $accommodationBookings = fetchAll($conn, "
    SELECT accommodation_booking.*,accommodation.name AS accomm_name, final_booking.final_booking_id, final_booking.total_amount AS total
    FROM accommodation_booking
    JOIN accommodation
    ON accommodation_booking.accommodation_id = accommodation.accommodation_id
    LEFT JOIN booking_service
    ON booking_service.accomm_booking_id =
    accommodation_booking.accomm_booking_id
    LEFT JOIN final_booking
    ON final_booking.final_booking_id = booking_service.final_booking_id
    WHERE accommodation_booking.user_id = ?
    ORDER BY accommodation_booking.created_at DESC
    ", "i", [$userId]);

    $transportBookings = fetchAll($conn, "
    SELECT transport_booking.*, final_booking.final_booking_id, final_booking.total_amount AS total
    FROM transport_booking
    LEFT JOIN booking_service
    ON booking_service.transport_booking_id =
    transport_booking.transport_booking_id
    LEFT JOIN final_booking
    ON final_booking.final_booking_id = booking_service.final_booking_id
    WHERE transport_booking.user_id = ?
    ORDER BY transport_booking.created_at DESC
    ", "i", [$userId]);

    $customTours = fetchAll($conn, "
    SELECT customized_tours.*, final_booking.final_booking_id, final_booking.total_amount AS total
    FROM customized_tours
    LEFT JOIN final_booking
    ON final_booking.cpkg_id = customized_tours.cpkg_id
    WHERE customized_tours.user_id = ?
    ORDER BY customized_tours.created_at DESC
    ", "i", [$userId]);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Bookings</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <script defer src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="style.css">
    <script defer src="https://kit.fontawesome.com/e5a3f33c9d.js" crossorigin="anonymous"></script>
</head>
<body>
    <?php include "navbar.php";?>
    <section class="dashboard-container">
        <div class="container">
            <?php if(!empty($success)): ?>
                <?php foreach($success as $scs): ?>
                    <div class="alert alert-success"><?= $scs ?></div>
                <?php endforeach; ?>
            <?php endif; ?>
            <?php if(!empty($errors)): ?>
                <?php foreach($errors as $error): ?>
                    <div class="alert alert-danger"><?= $error ?></div>
                <?php endforeach; ?>
            <?php endif; ?>
            <div class="bookings-header mb-5">
                <h1 class="mb-5 mt-4">Hi, <?=$userName?>!</h1>
                <h4 class="text-start mb-4 text-decoration-underline">Your Booking History</h4>
                <p class="fw-bold fs-5"><i class="fa-solid fa-circle-exclamation"></i>&nbsp;&nbsp;Important Notes</p>
                <ul>
                    <li>Agency Staff take a maximum of 24 hours to review your bookings</li>
                    <li>Once your booking is confirmed, make your payment</li>
                    <li>Bookings may not be cancelled once payment is made</li>
                </ul>
            </div>

            <?php if(empty($tourBookings) && empty($accommodationBookings) && empty($transportBookings) && empty($customTours)):?>
                <p><i class="fa-regular fa-face-frown"></i>You have not made any bookings yet</p>
            <?php endif;?>
            
            <!-- tour booking cards -->
            <?php foreach($tourBookings as $booking): ?>
                <div class="booking-card">
                    <div class="booking-header">
                        <h5><?= $booking['pkg_name'] ?> | ID: #<?= $booking['booking_id']?></h5>
                        <div>
                            <div class="booking-price mb-3">
                               Total: LKR <?=$booking['total']?>
                            </div>
                            <div class="d-flex">
                                <?php if($booking['status'] === 'Confirmed'): ?>
                                    <a href="payment.php?type=tour&id=<?= $booking['booking_id'] ?>" class="action-link text-success">Make Payment</a>&nbsp;&nbsp;&nbsp;
                                    <a href="view_booking_details.php?type=tour&id=<?= $booking['final_booking_id'] ?>" class="action-link">View Details</a>&nbsp;&nbsp;&nbsp;
                                <?php endif; ?>
                                <?php if($booking['status'] !== 'Payment Complete' && $booking['status'] !== 'Cancelled'): ?>
                                    <form action="traveller_dashboard.php" method="post" class="d-inline">
                                        <input type="hidden" name="type" value="tour">
                                        <input type="hidden" name="id" value="<?= $booking['booking_id'] ?>">
                                        <button type="submit" name="cancel" class="action-link" onclick="return confirm('Do you really want to cancel this booking?');">Cancel</button>
                                    </form>
                                <?php elseif($booking['status']==='Payment Complete'):?>
                                    <a href="view_booking_details.php?type=tour&id=<?= $booking['final_booking_id'] ?>" class="action-link">View Details</a>
                                <?php else: ?>
                                    <a href="view_booking_details.php?type=tour&id=<?= $booking['final_booking_id'] ?>" class="action-link">View Details</a>&nbsp;&nbsp;&nbsp;
                                    <span class="action-link text-muted">Cancel</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="booking-dates"><?= date('d M', strtotime($booking['arrival_date'])) ?> - <?= date('d M Y', strtotime($booking['departure_date'])) ?></div>
                    <div class="booking-status">
                        <span class="status-badge"><?= $booking['status']?></span>
                    </div>
                </div>
            <?php endforeach;?>

            <!-- accommodation booking cards -->
            <?php foreach($accommodationBookings as $booking): ?>
                <div class="booking-card">
                    <div class="booking-header">
                        <h5><?= $booking['accomm_name'] ?> | ID: #<?= $booking['accomm_booking_id']?></h5>
                        <div>
                            <div class="booking-price mb-3">
                               Total: LKR <?=$booking['total']?>
                            </div>
                            <div class="d-flex">
                                <?php if($booking['status'] === 'Confirmed'): ?>
                                    <a href="payment.php?type=accommodation&id=<?= $booking['accomm_booking_id'] ?>" class="action-link text-success">Make Payment</a>&nbsp;&nbsp;&nbsp;
                                <?php endif; ?>
                                <?php if($booking['status'] !== 'Payment Complete' && $booking['status'] !== 'Cancelled'): ?>
                                    <form action="traveller_dashboard.php" method="post" class="d-inline">
                                        <input type="hidden" name="type" value="accommodation">
                                        <input type="hidden" name="id" value="<?= $booking['accomm_booking_id'] ?>">
                                        <button type="submit" name="cancel" class="action-link" onclick="return confirm('Do you really want to cancel this booking?');">Cancel</button>
                                    </form>
                                <?php else: ?>
                                    <span class="action-link text-muted">Cancel</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="booking-dates"><?= date('d M', strtotime($booking['check_in_date'])) ?> - <?= date('d M Y', strtotime($booking['check_out_date'])) ?></div>
                    <div class="booking-status">
                        <span class="status-badge"><?= $booking['status']?></span>
                    </div>
                </div>
            <?php endforeach;?>

            <!-- transport booking cards -->
            <?php foreach($transportBookings as $booking): ?>
                <div class="booking-card">
                    <div class="booking-header">
                        <h5>Transport Booking | ID: #<?= $booking['transport_booking_id']?></h5>
                        <div>
                            <div class="booking-price mb-3">
                                Total: LKR <?=$booking['total']?>
                            </div>
                            <div class="d-flex">
                                <?php if($booking['status'] === 'Confirmed'): ?>
                                    <a href="payment.php?type=transport&id=<?= $booking['transport_booking_id'] ?>" class="action-link text-success">Make Payment</a>&nbsp;&nbsp;&nbsp;
                                    <a href="view_booking_details.php?type=transport&id=<?= $booking['final_booking_id'] ?>" class="action-link">View Details</a>&nbsp;&nbsp;&nbsp;
                                <?php endif; ?>
                                <?php if($booking['status'] !== 'Payment Complete' && $booking['status'] !== 'Cancelled'): ?>
                                    <form action="traveller_dashboard.php" method="post" class="d-inline">
                                        <input type="hidden" name="type" value="transport">
                                        <input type="hidden" name="id" value="<?= $booking['transport_booking_id'] ?>">
                                        <button type="submit" name="cancel" class="action-link" onclick="return confirm('Do you really want to cancel this booking?');">Cancel</button>
                                    </form>
                                <?php else: ?>
                                    <a href="view_booking_details.php?type=transport&id=<?= $booking['final_booking_id'] ?>" class="action-link">View Details</a>&nbsp;&nbsp;&nbsp;
                                    <span class="action-link text-muted">Cancel</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="booking-dates"><?= date('d M', strtotime($booking['start_date'])) ?> - <?= date('d M Y', strtotime($booking['end_date'])) ?></div>
                    <div class="booking-status">
                        <span class="status-badge"><?= $booking['status']?></span>
                    </div>
                </div>
            <?php endforeach;?>

            <!-- customized tour booking cards -->
            <?php foreach($customTours as $tour): ?>
                <div class="booking-card">
                    <div class="booking-header">
                        <h5>Custom Tour Request | ID: #<?= $tour['cpkg_id'] ?></h5>
                        <div>
                            <div class="booking-price mb-3">
                                Total: LKR <?=$tour['total']?>
                            </div>
                            <div class="d-flex">
                                <?php if($tour['status'] === 'Quoted'): ?>
                                    <a href="payment.php?type=customized&id=<?= $tour['cpkg_id'] ?>" class="action-link text-success">Make Payment</a>&nbsp;&nbsp;&nbsp;
                                    <a href="view_booking_details.php?type=customized&id=<?= $tour['final_booking_id'] ?>" class="action-link">View Details</a>&nbsp;&nbsp;&nbsp;
                                <?php endif; ?>
                                <?php if($tour['status'] !== 'Payment Complete' && $tour['status'] !== 'Cancelled'): ?>
                                    <form action="traveller_dashboard.php" method="post" class="d-inline">
                                        <input type="hidden" name="type" value="custom">
                                        <input type="hidden" name="id" value="<?= $tour['cpkg_id'] ?>">
                                        <button type="submit" name="cancel" class="action-link" onclick="return confirm('Do you really want to cancel this booking?');">Cancel</button>
                                    </form>
                                <?php else: ?>
                                    <a href="view_booking_details.php?type=customized&id=<?= $tour['final_booking_id'] ?>" class="action-link">View Details</a>&nbsp;&nbsp;&nbsp;
                                    <span class="action-link text-muted">Cancel</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="booking-dates"><?= date('d M', strtotime($tour['arrival_date'])) ?> - <?= date('d M Y', strtotime($tour['departure_date'])) ?></div>
                    <div class="booking-status">
                        <span class="status-badge"><?= $tour['status'] ?></span>
                    </div>
                </div>
        <?php endforeach; ?>
        </div>
    </section>
    <?php include "footer.php";?>
</body>
</html>