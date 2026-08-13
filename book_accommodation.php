<?php session_start();?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Accommodation</title>
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
                header("Location: accommodation.php");
                exit();
            }
            
            $accommodation = fetchOne($conn, "
            SELECT * FROM accommodation
            WHERE accommodation_id = ?", "i", [$id]);

            if(!$accommodation) {
                header("Location: accommodation.php");
                exit();
            }
        ?>
        <?php 
            $errors = [];
            if(isset($_POST['book_accommodation'])) {
                if(!isset($_SESSION['user_id'])) {
                    $errors[] = "You must be logged in to book.";
                }

                $userId = $_SESSION['user_id'] ?? null;
                $accommId = $accommodation['accommodation_id'] ?? null;

                $checkIn = $_POST['checkin_date'];
                $checkOut = $_POST['checkout_date'];
                $travellerCount = $_POST['travellers'];
                $roomCount = $_POST['room_count'];
                $primaryTravellerId = $_POST['primary_identifier'];

                if(empty($checkIn) || empty($checkOut) || empty($travellerCount) || empty($roomCount) ||empty($primaryTravellerId)) {
                    $errors[] = "Please fill in all fields!";
                }

                if(count($errors) === 0) {
                    $sql = "INSERT INTO accommodation_booking (user_id, accommodation_id, check_in_date, check_out_date, guest_count, room_count, lead_traveller_identifier) VALUES (?,?,?,?,?,?,?)";
                    $bookingId = insertRow($conn, $sql,"iissiis",[$userId, $accommId, $checkIn, $checkOut, $travellerCount,$roomCount,$primaryTravellerId]);
                    $_SESSION['booking_type'] = "Accommodation";
                    $_SESSION['booking_id'] = $bookingId;
                    header("Location: booking_success.php");
                    exit();
                }
            }
        ?>
        <div class="booking-title">You Are Booking</div>
        <div class="booking-subtitle"><?= $accommodation['name'];?></div>
        <form action="book_accommodation.php?id=<?= $id ?>" method="post" onsubmit="return validateAccommForm()">
            <input type="hidden" id="price_per_night" value="<?= $accommodation['price_per_night'] ?>">
            <div class="section-title">Stay Details</div>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Check-In Date</label>
                    <input type="date" class="form-control" name="checkin_date" id="checkin_date">
                </div>
                 <div class="col-md-6">
                    <label class="form-label">Check-Out Date</label>
                    <input type="date" class="form-control" name="checkout_date" id="checkout_date">
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
                    <input type="text" class="form-control" name="primary_identifier" id="primary_id">
                </div>
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
            <button type="submit" name="book_accommodation" class="btn btn-booking w-100">Confirm Accommodation Booking!</button>
        </form>            
    </section>
</body>
</html>