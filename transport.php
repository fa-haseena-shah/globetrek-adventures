<?php session_start(); 
        $errors = [];
        if(isset($_POST['book_transport'])) {
            if(!isset($_SESSION['user_id'])) {
                $errors[] =  "You must be logged in to book.";
            }

            include "db_helper.php";
            
            if(!$conn) {
                die("DB connection failed: " . mysqli_connect_error());
            }

            $userId = $_SESSION['user_id'] ?? null;
            $startDate = $_POST['start_date'];
            $endDate = $_POST['end_date'];
            $pickUp = $_POST['pick_location'];
            $dropOff = $_POST['drop_location'];
            $travellerCount = $_POST['travellers'];
            $primaryTravellerId = $_POST['primary_identifier'];
            $specialReqs = $_POST['special_requests'];

            if(empty($startDate) || empty($endDate) || empty($pickUp) || empty($dropOff) || empty($travellerCount) || empty($primaryTravellerId)) {
                $errors[] =  "Please fill in all fields!";
            }

            if(count($errors) === 0) {
                $sql = "INSERT INTO transport_booking(user_id, start_date, end_date, pickup_location, dropoff_location, passenger_count, lead_traveller_identifier, special_requests) VALUES (?,?,?,?,?,?,?,?)";
                $bookingId = insertRow($conn, $sql, "issssiss", [$userId, $startDate, $endDate, $pickUp, $dropOff, $travellerCount, $primaryTravellerId, $specialReqs]);
                $_SESSION['booking_type'] = "Transport";
                $_SESSION['booking_id'] = $bookingId;
                header("Location: booking_success.php");
                exit();
            }
        }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transport Services</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <script defer src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="style.css">
    <script defer src="https://kit.fontawesome.com/e5a3f33c9d.js" crossorigin="anonymous"></script>
    <script defer src="validation_script.js"></script>
</head>
<body>
    <?php include "navbar.php";?>
    <section class="transport-wrapper">
        <h3>TRANSPORT SERVICES</h3>
        <div class="list">
            <div class="list-item">
                <h3 class="m-0"><i class="fa-solid fa-plane-arrival"></i></h3>
                <div class="list-content">
                    <h4 class="fw-bold fs-5">Airport Transfers</h4>
                    <p>Pickup and drop-off services between BIA and your hotel</p>
                </div>
            </div>
            <div class="list-item">
                <h3 class="m-0"><i class="fa-solid fa-car-burst"></i></h3>
                <div class="list-content">
                    <h4 class="fw-bold fs-5">Daily Travels</h4>
                    <p>Transport across the island to wherever your heart desires!</p>
                </div>
            </div>
            <div class="list-item">
                <h3 class="m-0"><i class="fa-solid fa-ticket"></i></h3>
                <div class="list-content">
                    <div>
                        <h4 class="fw-bold fs-5">Sit Back & Relax</h4>
                        <p>Booking a tour with us guarantees transport!</p>
                    </div>
                </div>
            </div>
        </div>
        <br><br>
        <a href="#book_transport_form" class="btn btn-primary mx-auto d-block">Book Transport</a>
    </section>
    <section class="booking-form" id="book_transport_form">
        <div class="booking-title">You Are Booking</div>
        <div class="booking-subtitle">TRANSPORT SERVICES</div>
        <form action="transport.php" method="post" onsubmit="return validateTransportForm()">
            <div class="section-title">Travel Details</div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Start Travel Date</label>
                        <input type="date" class="form-control" name="start_date" id="start_date">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">End Travel Date</label>
                        <input type="date" class="form-control" name="end_date" id="end_date">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Pick-Up</label>
                        <input type="text" class="form-control" name="pick_location" id="pick_location">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Drop-Off</label>
                        <input type="text" class="form-control" name="drop_location" id="drop_location">
                    </div>
                </div>
                <hr>

                <div class="section-title">Traveller Details</div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Traveller Count</label>
                        <input type="number" class="form-control" name="travellers" id="travellers">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Primary Traveller NIC/Passport</label>
                        <input type="text" class="form-control" name="primary_identifier" id="primary_identifier">
                    </div>
                </div>
                <div class="section-title">Special Requests</div>
                <div class="row g-3">
                    <div class="col-md-12">
                        <textarea class="form-control" name="special_requests"></textarea>
                    </div>
                </div>
                <?php if (!empty($errors)): ?>
                    <?php foreach ($errors as $error): ?>
                        <div class="alert alert-danger">
                            <?= htmlspecialchars($error) ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
                <button type="submit" name="book_transport" class="btn btn-booking w-100">Confirm Transport Booking!</button>
            </div>
        </form>
    </section>
    <?php include "footer.php";?>
</body>
</html>