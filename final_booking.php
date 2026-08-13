<?php
    session_start();
    include "db_helper.php";
    // protect this page from customer accessing it
    if(!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['Staff', 'Admin'])) {
        header("Location: login.php");
        exit();
    }
        
    $type = $_GET['type'] ?? null;
    $id = $_GET['id'] ?? null;

    if(!$type || !$id) {
        header("Location: manage_bookings.php");
        exit();
    }

    $errors  = [];
    $success = "";
    $booking = null;

        // using $type and switch cases to locate the booking 
        switch($type) {
            case 'tour':
                $booking = fetchOne($conn, "
                SELECT tour_booking.*, tour_packages.pkg_name, tour_packages.duration_days,
                        users.full_name, users.user_id
                FROM tour_booking
                JOIN tour_packages ON tour_booking.pkg_id = tour_packages.pkg_id
                JOIN users ON tour_booking.user_id = users.user_id
                WHERE booking_id = ?
                ", "i", [$id]);
                break;

            case 'accommodation':
                $booking = fetchOne($conn, "
                SELECT accommodation_booking.*, accommodation.name AS accomm_name,
                        users.full_name, users.email, users.phone
                FROM accommodation_booking
                JOIN accommodation ON accommodation_booking.accommodation_id = accommodation.accommodation_id
                JOIN users ON accommodation_booking.user_id = users.user_id
                WHERE accomm_booking_id = ?
                ", "i", [$id]);
                break;

            case 'transport':
                $booking = fetchOne($conn, "
                SELECT transport_booking.*, users.full_name, users.email, users.phone
                FROM transport_booking
                JOIN users ON transport_booking.user_id = users.user_id
                WHERE transport_booking_id = ?
                ", "i", [$id]);
                break;

            case 'customized':
                $booking = fetchOne($conn, "
                SELECT customized_tours.*, users.full_name, users.email, users.phone
                FROM customized_tours
                JOIN users ON customized_tours.user_id = users.user_id
                WHERE cpkg_id = ?
                ", "i", [$id]);
                break;
        }

        if(!$booking) {
            header("Location: manage_bookings.php");
            exit();
        }

        // accommodation info for staff reference
        $accommodation = null;
        if($type === 'accommodation') {
            $accommodation = fetchOne($conn, "
            SELECT accommodation.*, destinations.name AS location
            FROM accommodation
            JOIN destinations ON accommodation.destination_id = destinations.destination_id
            WHERE accommodation_id = ?", "i", [$booking['accommodation_id']]);
        }

        // package info for staff reference (similar process as what we did in package_details.php)
            $pkgId = $booking['pkg_id'] ?? null;
            $package = fetchOne($conn, "
            SELECT tour_packages.*, category.name AS category_name
            FROM tour_packages
            JOIN category ON tour_packages.category_id = category.category_id
            WHERE tour_packages.pkg_id = ?
            ", "i", [$pkgId]);

            $destinations = fetchAll($conn, "
            SELECT destinations.name, destinations.location,package_destinations.day_number
            FROM destinations
            JOIN package_destinations ON destinations.destination_id = package_destinations.destination_id
            WHERE package_destinations.pkg_id = ?
            ORDER BY package_destinations.day_number ASC
            ", "i", [$pkgId]);

            $activities = fetchAll($conn, "
            SELECT activities.activity_name,activities.price_per_head,package_activities.day_number,package_activities.day_order
            FROM activities
            JOIN package_activities ON activities.activity_id = package_activities.activity_id
            WHERE package_activities.pkg_id = ?
            ORDER BY package_activities.day_number ASC, package_activities.day_order ASC
            ", "i", [$pkgId]);

            $activitiesByDay   = [];
            $destinationsByDay = [];

            foreach($activities as $activity) {
                $activitiesByDay[$activity['day_number']][] = $activity;
            }
            foreach($destinations as $destination) {
                $destinationsByDay[$destination['day_number']][] = $destination;
            }

            $allDays = array_unique(array_merge(
            array_keys($activitiesByDay),
            array_keys($destinationsByDay)
            ));
            sort($allDays);

        // accomm, transport, guide info needed when assigning
            $accommodations = fetchAll($conn, "
            SELECT * FROM accommodation
            WHERE availability = 1
            ORDER BY standard, name
            ");

            $transports = fetchAll($conn, "
            SELECT * FROM transport
            WHERE availability = 1
            ORDER BY vehicle_type
            ");

            $guides = fetchAll($conn, "
            SELECT * FROM guides
            WHERE availability = 1
            ");
        
    
        
        if(isset($_POST['confirm'])) {
        $totalAmount = $_POST['total_amount'] ?? null;
        $notes = $_POST['notes'] ?? null;
        $staffId = $_SESSION['user_id'];

            if(empty($totalAmount)) {
                $errors[] = "Please set a total amount.";
            }

            $bookingId = null;
            $cpkgId = null;

            if(count($errors) === 0) {
            // update booking status in booking tables
            switch($type) {
                case 'tour':
                    updateRow($conn, "UPDATE tour_booking SET status = 'Confirmed' WHERE booking_id = ?", "i", [$id]);
                    $bookingId = $id;
                    $cpkgId = null;
                    break;
                case 'accommodation':
                    updateRow($conn, "UPDATE accommodation_booking SET status = 'Confirmed' WHERE accomm_booking_id = ?", "i", [$id]);
                    $bookingId = null;
                    $cpkgId = null;
                    break;
                case 'transport':
                    updateRow($conn, "UPDATE transport_booking SET status = 'Confirmed' WHERE transport_booking_id = ?", "i", [$id]);
                    $bookingId = null;
                    $cpkgId = null;
                    break;
                case 'customized':
                    updateRow($conn, "UPDATE customized_tours SET status = 'Quoted' WHERE cpkg_id = ?", "i", [$id]);
                    $bookingId = null;
                    $cpkgId = $id;
                    break;
            }

            // insert final_booking
            $finalBookingId = insertRow($conn, "
                INSERT INTO final_booking(booking_id, cpkg_id, staff_id, total_amount, notes)
                VALUES (?, ?, ?, ?, ?)
            ", "iiids", [$bookingId, $cpkgId, $staffId, $totalAmount, $notes]);

            // accommodation : tour/customized (6 max)
            if($type === 'tour' || $type === 'customized') {
                $rooms = $_POST['accommodation_rooms'] ?? null;
                for($i = 1; $i <= 6; $i++) {
                    $accommId = $_POST["accommodation_id_$i"] ?? null;
                    if(!empty($accommId)) {
                        insertRow($conn, "
                            INSERT INTO booking_service(final_booking_id, service_type, accommodation_id, notes)
                            VALUES (?, ?, ?, ?)
                        ", "isis", [$finalBookingId, "Accommodation", $accommId, "Rooms: $rooms"]);
                    }
                }
            }

            // accommodation : standalone 
            if($type === 'accommodation') {
                $accommId = $_POST['accommodation_id_0'] ?? null;
                $rooms = $_POST['accommodation_rooms'] ?? null;
                if(!empty($accommId)) {
                    insertRow($conn, "
                    INSERT INTO booking_service(
                    final_booking_id,
                    service_type,
                    accommodation_id,
                    accomm_booking_id,
                    notes
                    ) VALUES (?, ?, ?, ?, ?)
                    ", "isiis", [
                    $finalBookingId, "Accommodation", $accommId, $id, "Rooms: $rooms"]);
                }
            }

            // transport for tour/customized
            if($type === 'tour' || $type === 'customized') {
                $transportId = $_POST['transport_id'] ?? null;
                if(!empty($transportId)) {
                    insertRow($conn, "
                        INSERT INTO booking_service(final_booking_id, service_type, transport_id)
                        VALUES (?, ?, ?)
                    ", "isi", [$finalBookingId,"Transport", $transportId]);
                }

                // airport transfer : tour/customized 
                if($type === 'tour' || $type === 'customized') {
                    $airportTransferId = $_POST['airport_transfer_id'] ?? null;
                    if(!empty($airportTransferId)) {
                        insertRow($conn, "
                        INSERT INTO booking_service(final_booking_id, service_type, transport_id, notes)
                        VALUES (?,?, ?,?)
                        ", "isis", [$finalBookingId,  "Transport", $airportTransferId, "This is airport transferer"]);
                    }
                }
            }

            // standlaone transport
            if($type === 'transport') {
                $transportId = $_POST['transport_id'] ?? null;
                insertRow($conn, "
                INSERT INTO booking_service(
                final_booking_id,
                service_type,
                transport_id,
                transport_booking_id)VALUES (?, ?, ?, ?)
                ", "isii", [$finalBookingId,"Transport",$transportId,$id]);
            }

            // guide : tour/customized
            if($type === 'tour' || $type === 'customized') {
                $guideId = $_POST['guide_id'] ?? null;
                if(!empty($guideId)) {
                    insertRow($conn, "
                        INSERT INTO booking_service(final_booking_id, service_type, guide_id)
                        VALUES (?, ?, ?)
                    ", "isi", [$finalBookingId, "Guide", $guideId]);

                    updateRow($conn, "
                        UPDATE guides
                        SET availability = 0
                        WHERE guide_id = ?
                    ", "i", [$guideId]);
                }
            }

            $success = "Booking confirmed. Customer must make payment.";
        }
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirm Booking - GlobeTrek Staff</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="style.css">
    <script defer src="https://kit.fontawesome.com/e5a3f33c9d.js" crossorigin="anonymous"></script>
</head>
<body>
    <?php include "staff_navbar.php";?>

    <?php if(!empty($success)): ?>
        <div class="alert alert-success"><?= $success ?></div>
    <?php endif; ?>
    <?php if(!empty($errors)): ?>
        <?php foreach($errors as $error): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endforeach; ?>
    <?php endif; ?>

    <section class="section-header mt-5 mb-2 text-center">
        <h4><i class="fa-solid fa-circle-check"></i>&nbsp;&nbsp;&nbsp;Confirming <?=ucfirst(strtolower($type))?> Booking</h4>
        <h1>ID: # <?= $id?></h1>
    </section>

    <!-- booking summary -->
    <section class="container m-5">
        <h4 class="text-decoration-underline mb-4">Reference Information</h4>
        <p class="mb-5">This will be helpful to you as you confirm the customer's booking</p>
        <!-- customer info -->
        <h5>Customer Requirements</h5>
        <div class="table-responsive">
            <table class="table table-striped table-bordered mb-5">
                <thead>
                    <tr>
                        <th scope="col">Customer</th>
                        <?php if($type === 'tour'): ?>
                            <th scope="col">Package</th>
                            <th scope="col">Pkg Duration (Days)</th>
                            <th scope="col">Travellers</th>
                            <th scope="col">Room Count</th>
                            <th scope="col">Arrival</th>
                            <th scope="col">Departure</th>
                            <th scope="col">Airport Transfer</th>
                        <?php elseif($type === 'accommodation'): ?>
                            <th scope="col">Accommodation</th>
                            <th scope="col">CheckIn</th>
                            <th scope="col">CheckOut</th>
                            <th scope="col">Guests</th>
                            <th scope="col">Room Count</th>
                        <?php elseif($type === 'transport'): ?>
                            <th scope="col">PickUp At</th>
                            <th scope="col">DropOff At</th>
                            <th scope="col">Passengers</th>
                            <th scope="col">Start Date</th>
                            <th scope="col">End Date</th>
                        <?php elseif($type === 'customized'): ?>
                            <th scope="col">Travellers</th>
                            <th scope="col">Arrival</th>
                            <th scope="col">Departure</th>
                            <th scope="col">Room Count</th>
                            <th scope="col">Budget</th>
                            <th scope="col">Destinations</th>
                            <th scope="col">Activities</th>
                            <th scope="col">Airport Transfer</th>
                            <th scope="col">Transport</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><?= $booking['full_name'] ?></td>
                        <?php if($type === 'tour'): ?>
                            <td><?= $booking['pkg_name'] ?></td>
                            <td><?= $booking['duration_days'] ?></td>
                            <td><?= $booking['traveller_count'] ?></td>
                            <td><?= $booking['room_count'] ?></td>
                            <td><?= $booking['arrival_date'] ?></td>
                            <td><?= $booking['departure_date'] ?></td>
                            <td><?= ($booking['airport_transfer'] === 1) ? "✓" : "✕"?></td>
                        <?php elseif($type === 'accommodation'): ?>
                            <td><?= $booking['accomm_name'] ?></td>
                            <td><?= $booking['check_in_date'] ?></td>
                            <td><?= $booking['check_out_date'] ?></td>
                            <td><?= $booking['guest_count'] ?></td>
                            <td><?= $booking['room_count'] ?></td>
                        <?php elseif($type === 'transport'): ?>
                            <td><?= $booking['pickup_location'] ?></td>
                            <td><?= $booking['dropoff_location'] ?></td>
                            <td><?= $booking['passenger_count'] ?></td>
                            <td><?= $booking['start_date'] ?></td>
                            <td><?= $booking['end_date'] ?></td>
                        <?php elseif($type === 'customized'): ?>
                            <td><?= $booking['traveller_count'] ?></td>
                            <td><?= $booking['arrival_date'] ?></td>
                            <td><?= $booking['departure_date'] ?></td>
                            <td><?= $booking['room_count'] ?></td>
                            <td><?= $booking['budget'] ?></td>
                            <td><?= $booking['destination_notes'] ?></td>
                            <td><?= $booking['activity_notes'] ?></td>
                            <td><?= ($booking['airport_transfer'] === 1) ? "✓" : "✕"?></td>
                            <td><?= ($booking['transport_needed'] === 1) ? "✓" : "✕"?></td>
                        <?php endif; ?>
                    </tr>
                </tbody>
            </table>
        </div>

        <h5><?=ucfirst(strtolower($type))?> References</h5>
        <?php if($type === 'customized'): ?>
            <p class="mb-5">Select accommodation, transport and guides according to customer requirements. Note down the itinerary in <strong>Extra Notes</strong></p>
        <?php endif;?>
        <!-- service info -->
        <div class="table-responsive">
            <table class="table table-striped table-bordered mb-0">
                <thead>
                    <tr>
                        <?php if($type === 'tour'): ?>
                            <th scope="col">Package</th>
                            <th scope="col">Pkg Duration (Days)</th>
                            <th scope="col">Price Per Head</th>
                            <th scope="col">Itinerary</th>
                        <?php elseif($type === 'accommodation'): ?>
                            <th scope="col">Accommodation</th>
                            <th scope="col">Destination</th>
                            <th scope="col">Price Per Night</th>
                            <th scope="col">Standard</th>
                            <th scope="col">Type</th>
                        <?php elseif($type === 'transport'): ?>
                            <th scope="col">Name</th>
                            <th scope="col">Type</th>
                            <th scope="col">Price Per Day (Base)</th>
                            <th scope="col">Capacity</th>
                        <?php endif;?>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <?php if($type === 'tour'): ?>
                            <td><?= $package['pkg_name'] ?></td>
                            <td><?= $package['duration_days'] ?></td>
                            <td><?= $package['price_per_head'] ?></td>
                            <td>
                                <?php foreach($allDays as $day): ?>
                                    <?php if(isset($destinationsByDay[$day])): ?>
                                        <?php foreach($destinationsByDay[$day] as $dest): ?>
                                            <strong><?= $dest['name']?></strong><br>
                                        <?php endforeach; ?>
                                    <?php endif;?>

                                    <?php if(isset($activitiesByDay[$day])): ?>
                                        <?php foreach($activitiesByDay[$day] as $act): ?>
                                            <?= $act['activity_name'] ?>, <small>LKR <?=$act['price_per_head']?> per person</small> <br>
                                        <?php endforeach; ?>
                                    <?php endif;?>
                                <?php endforeach;?>
                            </td>
                        <?php elseif($type === 'accommodation'): ?>
                            <td><?= $accommodation['name'] ?></td>
                            <td><?= $accommodation['location'] ?></td>
                            <td><?= $accommodation['price_per_night'] ?></td>
                            <td><?= $accommodation['standard'] ?></td>
                            <td><?= $accommodation['property_type'] ?></td>
                        <?php endif;?>
                    </tr>
                    <?php if($type === 'transport'): ?>
                        <?php foreach($transports as $transport):?>
                            <tr>
                                <td><?= $transport['name']?></td>
                                <td><?= $transport['vehicle_type']?></td>
                                <td><?= $transport['price_per_head']?></td>
                                <td><?= $transport['capacity']?></td>
                            </tr>
                        <?php endforeach;?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>

    <!-- booking form -->
    <section class="container">
        <form action="final_booking.php?type=<?=$type?>&id=<?= $id ?>" method="post">
            <!-- accommodation : tours / singlet bookings -->
            <?php if($type === 'tour' || $type === 'customized'):?>
                <h5>Assign Accommodation</h5>
                <p class="text-muted small">Assign up to 6 accommodations for different destinations of the trip.</p>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Rooms</label>
                    <input type="number" name="accommodation_rooms" class="form-control accomm-rooms" min="1" placeholder="e.g. 2">
                </div>
                <div class="row row-cols-1 row-cols-md-3 g-3 mb-3">
                    <?php for($i = 1; $i <= 6; $i++): ?>
                        <div class="col">
                            <div class="card h-100 p-3">
                                <label class="form-label">Accommodation Name #<?= $i ?></label>
                                <select name="accommodation_id_<?= $i ?>" class="form-select">
                                    <option value=""> No Selection... </option>
                                    <?php foreach($accommodations as $accomm): ?>
                                        <option value="<?= $accomm['accommodation_id'] ?>"><?= $accomm['name'] ?>, LKR <small><?= $accomm['price_per_night'] ?> per night</small></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    <?php endfor; ?>
                </div>
            <?php elseif($type === 'accommodation'): ?>
                <h5>Assign Accommodation</h5>
                <div class="row g-3 align-items-end mb-3">
                    <div class="col-md-8">
                        <label class="form-label">Accommodation Name</label>
                        <select name="accommodation_id_0" class="form-select">
                            <?php foreach($accommodations as $accomm): ?>
                                <option value="<?= $accomm['accommodation_id'] ?>"><?= $accomm['name'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Rooms</label>
                        <input type="number" name="accommodation_rooms" class="form-control accomm-rooms" min="1" value="<?=$booking['room_count']?>">
                    </div>
                </div>
            <?php endif; ?>

            <!-- main transport -->
            <?php if($type === 'tour' || $type === 'customized' || $type === 'transport'): ?>
                <h5>Assign Transport</h5>   
                <div class="mb-4 mt-4">
                    <label class="form-label fw-semibold">Transport</label>
                        <select name="transport_id" class="form-select" id="transport_select">
                            <option value="">No selection...</option>
                            <?php foreach($transports as $transport): ?>
                                <option value="<?= $transport['transport_id'] ?>" data-price="<?= $transport['price_per_head'] ?>">
                                    <?= $transport['name'] ?> — <?= $transport['vehicle_type'] ?>
                                    (Capacity: <?= $transport['capacity'] ?>,LKR <?= number_format($transport['price_per_head']) ?>/day)
                                </option>
                            <?php endforeach; ?>
                        </select>
                </div>
            
            <!-- airport transfer : show for tours only -->
                <?php if($type === 'tour' || $type === 'customized'): ?>
                        <label class="form-label fw-semibold mt-3">Airport Transfer <i>(If Required)</i></label>
                        <div class="mb-4">
                            <select name="airport_transfer_id" class="form-select" id="airport_transfer_select">
                                <option value="">No selection...</option>
                                <?php foreach($transports as $transport): ?>
                                    <option value="<?= $transport['transport_id'] ?>"
                                            data-price="<?= $transport['price_per_head'] ?>">
                                        <?= $transport['name'] ?> — <?= $transport['vehicle_type'] ?>
                                        (Capacity: <?= $transport['capacity'] ?>,
                                        LKR <?= number_format($transport['price_per_head']) ?>/day)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                <?php endif; ?>
            <?php endif; ?>

            <!-- guide assignment -->
            <?php if($type === 'tour' || $type === 'customized'):?>
                <h5>Assign Guide</h5>   
                <div class="mt-4">
                    <select name="guide_id" class="form-select">
                        <option value="">No guide selected...</option>
                        <?php foreach($guides as $guide): ?>
                            <option value="<?= $guide['guide_id'] ?>">
                                <?= $guide['full_name']?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            <?php endif;?>

            <!-- extra notes for all -->
            <div>
                <h5 class="mt-5">Extra Notes</h5>   
                <p class="text-muted">Any internal notes, customized itineraries</p>
                    <div class="mt-4">
                        <textarea name="notes" id="notes" cols="30" rows="10" class="form-control"></textarea>
                    </div>
            </div>
            <div class="total-box">
                <h3 class="m-5">Total (LKR)</h3>
                <input type="number" name="total_amount" class="form-control" step="0.01">
            </div>
            <button type="submit" name="confirm" class="btn btn-booking m-5 w-100">Confirm This Booking</button>
        </form>
    </section>
</body>
</html>