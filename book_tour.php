<?php session_start();?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Tour</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="style.css">
    <script defer src="function_script.js"></script>
    <script defer src="validation_script.js"></script>
</head>
<body>
    <?php include "navbar.php";?>
    <section class="booking-form">
        <?php 
            include "db_helper.php";
            $id = $_GET['id'] ?? null;
            if(!$id) {
                header("Location: packages.php");
                exit();
            }
                    
            $package = fetchOne($conn, "
            SELECT * FROM tour_packages
            WHERE pkg_id = ?", "i", [$id]);

            if(!$package) {
                header("Location: packages.php");
                exit();
            }

            $errors = [];
            if(isset($_POST['book_tour'])) {
                if(!isset($_SESSION['user_id'])) {
                    $errors[] = "You must be logged in to book.";
                }

                if(!$conn) {
                    die("DB connection failed: " . mysqli_connect_error());
                }

                $userId = $_SESSION['user_id'] ?? null;
                $pkgId = $package['pkg_id'] ?? null;

                $arrivalDate = $_POST['start_date'];
                $departureDate = $_POST['end_date'];
                $travellerCount = $_POST['travellers'];
                $roomCount = $_POST['room_count'];
                $primaryTravellerId = $_POST['primary_identifier'];
                $airportTransfer = isset($_POST['airport_transfer']) ? 1 : 0;

                if(empty($arrivalDate) || empty($departureDate) || empty($travellerCount) || empty($roomCount) || empty($primaryTravellerId)) {
                    $errors[] = "Please fill in all fields!";
                }

                if(count($errors) === 0) {
                    $sql = "INSERT INTO tour_booking(user_id, pkg_id, arrival_date, departure_date, 
                    traveller_count, room_count, lead_traveller_identifier, airport_transfer) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                    $bookingId = insertRow($conn, $sql, "iissiisi", [$userId, $pkgId, $arrivalDate, $departureDate, 
                    $travellerCount, $roomCount, $primaryTravellerId, $airportTransfer]);
                    $_SESSION['booking_type'] = "Tour Package";
                    $_SESSION['booking_id'] = $bookingId;
                    header("Location: booking_success.php");
                    exit();
                }
            }
        ?>
        <div class="booking-title">You Are Booking</div>
        <div class="booking-subtitle"><?= $package['pkg_name']?></div>
        <form action="book_tour.php?id=<?= $id ?>" method="post" onsubmit="return validateTourForm()">
            <input type="hidden" id="base_price" value="<?= $package['price_per_head'] ?>">
            <div class="section-title">Tour Details</div>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Arrival Date</label>
                    <input type="date" class="form-control" name="start_date" id="start_date">
                </div>
                 <div class="col-md-6">
                    <label class="form-label">Departure Date</label>
                    <input type="date" class="form-control" name="end_date" id="end_date">
                </div>
            </div>
            <hr>
            <div class="section-title">Traveller Details</div>
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Traveller Count</label>
                    <input type="number" class="form-control" name="travellers" id="travellers">
                </div>
                <div class="col-md-3">
                    <label class="form-label">No of Rooms Needed</label>
                    <input type="number" class="form-control" name="room_count" id="room_count">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Primary Traveller NIC/Passport</label>
                    <input type="text" class="form-control" name="primary_identifier" id="primary_identifier">
                </div>
            </div>
            <div class="form-check d-flex justify-content-center mt-4">
                <input class="form-check-input" name="airport_transfer" type="checkbox" value="1" id="airport_transfer">
                <label class="form-check-label" for="flexCheckDefault">
                    &nbsp;&nbsp;&nbsp;Do you need airport transfers (BIA)?
                </label>
            </div>
            <div class="total-box">
                <p>Total</p>
                <p id="total_amount"></p>
            </div>
            <?php if (!empty($errors)): ?>
                    <?php foreach ($errors as $error): ?>
                        <div class="alert alert-danger">
                            <?= htmlspecialchars($error) ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            <button type="submit" name="book_tour" class="btn btn-booking w-100">Confirm Package Booking!</button>
        </form>
    </section>
</body>
</html>