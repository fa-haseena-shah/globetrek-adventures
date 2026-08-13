<?php session_start();?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Custom Tour</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="style.css">
    <script defer src="validation_script.js"></script>
</head>
<body>
    <?php include "navbar.php";?>
    <section class="booking-form">
        <?php
            include "db_helper.php";
            $errors = [];
            if(isset($_POST['book_customized_tour'])) {
                if(!isset($_SESSION['user_id'])) {
                    $error[] = "You must be logged in to book.";
                }

                if(!$conn) {
                        die("DB connection failed: " . mysqli_connect_error());
                }

                $userId = $_SESSION['user_id'] ?? null;
                $arrivalDate = $_POST['start_date'];
                $departureDate = $_POST['end_date'];
                $budget = $_POST['budget'];
                $travellerCount = $_POST['travellers'];
                $roomCount = $_POST['room_count'];
                $primaryTravellerId = $_POST['primary_identifier'];
                $accommodationPref = $_POST['accomm'];
                $destinationNotes = $_POST['destination_notes'];
                $activityNotes = $_POST['activity_notes'];
                $airportTransfer = isset($_POST['airport_transfer']) ? 1 : 0;
                $transportNeeded = isset($_POST['transport_needed']) ? 1 : 0;

                if(empty($arrivalDate) || empty($departureDate) || empty($budget) || empty($travellerCount) 
                || empty($roomCount) || empty($primaryTravellerId) || empty($accommodationPref) || 
                empty($destinationNotes) || empty($activityNotes)) {
                    $error[] = "Please fill in all fields!";
                }

                if(count($errors) === 0) {
                    $sql = "INSERT INTO customized_tours(user_id, arrival_date, departure_date, traveller_count,
                    room_count, lead_traveller_identifier, accommodation_preference, airport_transfer, 
                    transport_needed, destination_notes, activity_notes, budget) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                    $bookingId = insertRow($conn, $sql, "issiissiissi", [$userId, $arrivalDate, $departureDate,
                    $travellerCount, $roomCount, $primaryTravellerId, $accommodationPref, $airportTransfer, 
                    $transportNeeded, $destinationNotes, $activityNotes, $budget]);
                    $_SESSION['booking_type'] = "Customized Package";
                    $_SESSION['booking_id'] = $bookingId;
                    header("Location: booking_success.php");
                    exit();
                }
            }
        ?>
        <div class="booking-title">You Are Curating</div>
        <div class="booking-subtitle">A Customized Tour</div>
        <form action="customize_tour.php" method="post" onsubmit="return validateCustomizeForm()">
            <div class="section-title">Tour Details</div>
            <div class="row g-3 mb-4">
                <div class="col-md-5">
                    <label class="form-label" for="start_date">Arrival Date</label>
                    <input type="date" class="form-control" name="start_date" id="start_date">
                </div>
                <div class="col-md-5">
                    <label class="form-label" for="end_date">Departure Date</label>
                    <input type="date" class="form-control" name="end_date" id="end_date">
                </div>
                <div class="col-md-2">
                    <label class="form-label" for="budget">Budget (LKR)</label>
                    <input type="number" class="form-control" name="budget" id="budget" min="0">
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
            <hr>
            <div class="d-flex flex-wrap gap-3 align-items-center mb-4">
                <div class="section-title">Accommodation Preferences</div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="accomm" id="accomm_budget" value="Budget">
                    <label class="form-check-label" for="accomm_budget">Budget</label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="accomm" id="accomm_standard" value="Standard">
                    <label class="form-check-label" for="accomm_standard">Standard</label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="accomm" id="accomm_luxe" value="Luxury">
                    <label class="form-check-label" for="accomm_luxe">Luxury</label>
                </div>
            </div>
            <hr>
            <div class="section-title">Destinations</div>
            <div class="mb-4">
                <textarea class="form-control" name="destination_notes" id="destination_notes" rows="4" placeholder="Tell us the places you want to visit..."></textarea>
            </div>
            <div class="section-title">Activities</div>
            <div class="mb-4">
                <textarea class="form-control" name="activity_notes" id="activity_notes" rows="4" placeholder="What would you like to do on your tour?"></textarea>
            </div>
            <hr>
            <div class="section-title">We Take Care of Transport (If You'd Like!)</div>
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <div class="form-check">
                        <input class="form-check-input" name="airport_transfer" type="checkbox" value="1" id="airport_transfer">
                        <label class="form-check-label" for="airport_transfer">Do you need airport transfers (BIA)?</label>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-check">
                        <input class="form-check-input" name="transport_needed" type="checkbox" value="1" id="transport_needed">
                        <label class="form-check-label" for="transport_needed">Do you need daily transport?</label>
                    </div>
                </div>
            </div>
             <?php if (!empty($errors)): ?>
                    <?php foreach ($errors as $error): ?>
                        <div class="alert alert-danger">
                            <?= htmlspecialchars($error) ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            <div class="text-center">
                <button type="submit" name="book_customized_tour" class="btn btn-booking w-100">Submit Customized Tour Request</button>
            </div>
        </form>
    </section>
</body>
</html>