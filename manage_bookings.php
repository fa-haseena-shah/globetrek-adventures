<?php session_start();
    $searchQuery = $_GET['query'] ?? '';
    $searchType = $_GET['type'] ?? 'tour';
    $keyword = !empty($searchQuery) ? '%' . $searchQuery . '%' : '%';

    include "db_helper.php";

$tourBookings = fetchAll($conn, "
    SELECT 
        tour_booking.booking_id,
        tour_booking.arrival_date,
        tour_booking.departure_date,
        tour_booking.traveller_count,
        tour_booking.room_count,
        tour_booking.lead_traveller_identifier,
        tour_booking.airport_transfer,
        tour_booking.status,

        users.user_id,
        users.full_name,
        users.email,
        users.phone,

        tour_packages.pkg_id,
        tour_packages.pkg_name,

        final_booking.total_amount,
        final_booking.final_booking_id,
        final_booking.notes
    FROM tour_booking
    JOIN users ON tour_booking.user_id = users.user_id
    JOIN tour_packages ON tour_booking.pkg_id = tour_packages.pkg_id
    LEFT JOIN final_booking ON final_booking.booking_id = tour_booking.booking_id
    WHERE
        users.full_name LIKE ?
        OR users.email LIKE ?
        OR tour_packages.pkg_name LIKE ?
        OR tour_booking.status LIKE ?
    ORDER BY tour_booking.booking_id ASC
", "ssss", [$keyword, $keyword, $keyword, $keyword]);

$accommBookings = fetchAll($conn, "
    SELECT
        accommodation_booking.accomm_booking_id,
        accommodation_booking.check_in_date,
        accommodation_booking.check_out_date,
        accommodation_booking.guest_count,
        accommodation_booking.room_count,
        accommodation_booking.lead_traveller_identifier,
        accommodation_booking.status,
        accommodation_booking.created_at,
        users.user_id,
        users.full_name,
        users.email,
        users.phone,
        accommodation.accommodation_id,
        accommodation.name AS accomm_name,
        accommodation.standard,
        final_booking.total_amount,
        final_booking.final_booking_id
    FROM accommodation_booking
    JOIN users ON accommodation_booking.user_id = users.user_id
    JOIN accommodation ON accommodation_booking.accommodation_id = accommodation.accommodation_id
    JOIN destinations ON accommodation.destination_id = destinations.destination_id
    LEFT JOIN booking_service ON booking_service.accomm_booking_id = accommodation_booking.accomm_booking_id
    LEFT JOIN final_booking ON final_booking.final_booking_id = booking_service.final_booking_id
    WHERE
        users.full_name LIKE ?
        OR users.email LIKE ?
        OR accommodation.name LIKE ?
        OR destinations.name LIKE ?
        OR accommodation_booking.status LIKE ?
    ORDER BY accommodation_booking.accomm_booking_id ASC
", "sssss", [$keyword, $keyword, $keyword, $keyword, $keyword]);

$transportBookings = fetchAll($conn, "
    SELECT
        transport_booking.transport_booking_id,
        transport_booking.start_date,
        transport_booking.end_date,
        transport_booking.pickup_location,
        transport_booking.dropoff_location,
        transport_booking.passenger_count,
        transport_booking.lead_traveller_identifier,
        transport_booking.special_requests,
        transport_booking.status,

        users.user_id,
        users.full_name,
        users.email,
        users.phone,

        final_booking.total_amount,
        final_booking.final_booking_id,

        booking_service.notes,

        transport.transport_id,
        transport.name AS transport_name,
        transport.vehicle_type AS vehicle
    FROM transport_booking
    JOIN users ON transport_booking.user_id = users.user_id
    LEFT JOIN booking_service ON booking_service.transport_booking_id = transport_booking.transport_booking_id
    LEFT JOIN final_booking ON final_booking.final_booking_id = booking_service.final_booking_id
    LEFT JOIN transport ON booking_service.transport_id = transport.transport_id
    WHERE
        users.full_name LIKE ?
        OR users.email LIKE ?
        OR transport_booking.status LIKE ?
    ORDER BY transport_booking.transport_booking_id ASC
", "sss", [$keyword, $keyword, $keyword]);

$customTours = fetchAll($conn, "
    SELECT
        customized_tours.cpkg_id,
        customized_tours.arrival_date,
        customized_tours.departure_date,
        customized_tours.traveller_count,
        customized_tours.room_count,
        customized_tours.lead_traveller_identifier,
        customized_tours.accommodation_preference,
        customized_tours.airport_transfer,
        customized_tours.transport_needed,
        customized_tours.destination_notes,
        customized_tours.activity_notes,
        customized_tours.budget,
        customized_tours.status,

        users.user_id,
        users.full_name,
        users.email,
        users.phone,

        final_booking.total_amount,
        final_booking.final_booking_id,
        final_booking.notes
    FROM customized_tours
    JOIN users ON customized_tours.user_id = users.user_id
    LEFT JOIN final_booking ON final_booking.cpkg_id = customized_tours.cpkg_id
    WHERE
        users.full_name LIKE ?
        OR users.email LIKE ?
        OR customized_tours.status LIKE ?
    ORDER BY customized_tours.cpkg_id ASC
", "sss", [$keyword, $keyword, $keyword]);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Customer Bookings</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <script defer src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="style.css">
    <script defer src="https://kit.fontawesome.com/e5a3f33c9d.js" crossorigin="anonymous"></script>
</head>
<body>
    <?php include "staff_navbar.php";?>

    <section class="section-header mt-5 mb-2">
        <h2><i class="fa-regular fa-circle-check"></i></i>&nbsp;&nbsp;&nbsp;Viewing All Bookings</h2>
    </section>

    <!-- search bar -->
    <section class="search-bar bg-transparent py-4">
        <div class="container">
            <form action="manage_bookings.php" method="get" class="row g-2 align-items-center">
                <div class="col-auto">
                    <select name="type" class="form-select">
                        <option value="tour" <?= $searchType === 'tour' ? 'selected' : '' ?>>Tour</option>
                        <option value="accommodation" <?= $searchType === 'accommodation' ? 'selected' : '' ?>>Accommodation</option>
                        <option value="transport" <?= $searchType === 'transport' ? 'selected' : '' ?>>Transport</option>
                        <option value="custom" <?= $searchType === 'custom' ? 'selected' : '' ?>>Custom Tour</option>
                    </select>
                </div>
                <div class="col-md-6 col-sm-12 m-2">
                    <input type="text" name="query" class="form-control" placeholder="Search by name, package or status..." value="<?= htmlspecialchars($searchQuery) ?>">
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-primary">SEARCH</button>
                </div>
            </form>
        </div>
    </section>

    <!-- accordion-->
    <section class="container pb-5">
        <div class="accordion" id="bookingAccordion">
            <!-- tour pkg -->
            <div class="accordion-item">
                <h2 class="accordion-header" id="headingTour">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTour" aria-expanded="false" aria-controls="collapseTour">
                        Tour Package Bookings
                    </button>
                </h2>
                <div id="collapseTour" class="accordion-collapse collapse" aria-labelledby="headingTour" data-bs-parent="#bookingAccordion">
                    <div class="accordion-body p-0">
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered mb-0">
                                <thead class="custom-table-header">
                                    <tr>
                                        <th>Booking ID</th>
                                        <th>Package</th>
                                        <th>Customer</th>
                                        <th>NIC/Passport</th>
                                        <th>Phone</th>
                                        <th>Arrival</th>
                                        <th>Departure</th>
                                        <th>Travellers</th>
                                        <th>Rooms</th>
                                        <th>Airport Transfer</th>
                                        <th>Total (LKR)</th>
                                        <th>Status</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($tourBookings)): ?>
                                        <?php foreach ($tourBookings as $booking): ?>
                                            <tr>
                                                <td><?= $booking['booking_id'] ?></td>
                                                <td><?= $booking['pkg_name'] ?> <br><small>Pkg #<?= $booking['pkg_id'] ?></small></td>
                                                <td><?= $booking['full_name'] ?></td>
                                                <td><?= $booking['lead_traveller_identifier']?></td>
                                                <td><?= $booking['phone'] ?></td>
                                                <td><?= $booking['arrival_date'] ?></td>
                                                <td><?= $booking['departure_date'] ?></td>
                                                <td><?= $booking['traveller_count'] ?></td>
                                                <td><?= $booking['room_count'] ?></td>
                                                <td><?= ($booking['airport_transfer'] == 1) ? 'Yes' : 'No' ?></td>
                                                <td><?= ($booking['total_amount'] != null) ? $booking['total_amount'] : 'Pending Finalization' ?></td>
                                                <td><?= $booking['status'] ?></td>
                                                <?php if($booking['status'] === 'Pending'): ?>
                                                    <td class="align-middle text-center">
                                                        <a href="final_booking.php?type=tour&id=<?= $booking['booking_id'] ?>" class="action-link">Confirm</a>
                                                    </td>
                                                <?php elseif($booking['status']==='Confirmed' || $booking['status']==='Payment Complete'):?>
                                                    <td class="align-middle text-center">
                                                        <a href="view_booking_details.php?type=tour&id=<?= $booking['final_booking_id'] ?>" class="action-link">View Details</a>
                                                    </td>
                                                <?php else: ?>
                                                    <td><a class="action-link"></a></td>
                                                <?php endif;?>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="15" class="text-center py-4">No tour bookings available.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- accomm -->
            <div class="accordion-item">
                <h2 class="accordion-header" id="headingAccommodation">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseAccommodation" aria-expanded="false" aria-controls="collapseAccommodation">
                        Accommodation Bookings
                    </button>
                </h2>
                <div id="collapseAccommodation" class="accordion-collapse collapse" aria-labelledby="headingAccommodation" data-bs-parent="#bookingAccordion">
                    <div class="accordion-body p-0">
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered mb-0">
                                <thead class="custom-table-header">
                                    <tr>
                                        <th>Booking ID</th>
                                        <th>Accommodation</th>
                                        <th>Customer</th>
                                        <th>NIC/Passport</th>
                                        <th>Phone</th>
                                        <th>Check-in</th>
                                        <th>Check-out</th>
                                        <th>Guests</th>
                                        <th>Rooms</th>
                                        <th>Total (LKR)</th>
                                        <th>Status</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($accommBookings)): ?>
                                        <?php foreach ($accommBookings as $booking): ?>
                                            <tr>
                                                <td><?= $booking['accomm_booking_id'] ?></td>
                                                <td><?= $booking['accomm_name'] ?> <br><small><?= $booking['standard'] ?></small></td>
                                                <td><?= $booking['full_name'] ?></td>
                                                <td><?= $booking['lead_traveller_identifier']?></td>
                                                <td><?= $booking['phone'] ?></td>
                                                <td><?= $booking['check_in_date'] ?></td>
                                                <td><?= $booking['check_out_date'] ?></td>
                                                <td><?= $booking['guest_count'] ?></td>
                                                <td><?= $booking['room_count'] ?></td>
                                                <td><?= ($booking['total_amount'] != null) ? $booking['total_amount'] : 'Pending Finalization' ?></td>
                                                <td><?= $booking['status'] ?></td>
                                                <?php if($booking['status'] === 'Pending'): ?>
                                                    <td class="align-middle text-center">
                                                        <a href="final_booking.php?type=accommodation&id=<?= $booking['accomm_booking_id'] ?>" class="action-link">Confirm</a>
                                                    </td>
                                                <?php else: ?>
                                                    <a class="action-link"></a>
                                                <?php endif;?>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="15" class="text-center py-4">No accommodation bookings available.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="accordion-item">
                <h2 class="accordion-header" id="headingTransport">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTransport" aria-expanded="false" aria-controls="collapseTransport">
                        Transport Bookings
                    </button>
                </h2>
                <div id="collapseTransport" class="accordion-collapse collapse" aria-labelledby="headingTransport" data-bs-parent="#bookingAccordion">
                    <div class="accordion-body p-0">
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered mb-0 data-resizable=true">
                                <thead class="custom-table-header">
                                    <tr>
                                        <th>Booking ID</th>
                                        <th>Customer</th>
                                        <th>NIC/Passport</th>
                                        <th>Phone</th>
                                        <th>Start</th>
                                        <th>End</th>
                                        <th>Pickup</th>
                                        <th>Dropoff</th>
                                        <th>Passengers</th>
                                        <th style="width: 15%;">Special Requests</th>
                                        <th>Total (LKR)</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($transportBookings)): ?>
                                        <?php foreach ($transportBookings as $booking): ?>
                                            <tr>
                                                <td><?= $booking['transport_booking_id'] ?></td>
                                                <td><?= $booking['full_name'] ?></td>
                                                <td><?= $booking['lead_traveller_identifier']?></td>
                                                <td><?= $booking['phone'] ?></td>
                                                <td><?= $booking['start_date']?></td>
                                                <td><?= $booking['end_date'] ?></td>
                                                <td><?= $booking['pickup_location'] ?></td>
                                                <td><?= $booking['dropoff_location'] ?></td>
                                                <td><?= $booking['passenger_count'] ?></td>
                                                <td><?= $booking['special_requests'] ?></td>
                                                <td><?= ($booking['total_amount'] != null) ? $booking['total_amount'] : 'Pending Finalization' ?></td>
                                                <td><?= $booking['status'] ?></td>
                                                <?php if($booking['status'] === 'Pending'): ?>
                                                    <td class="align-middle text-center">
                                                        <a href="final_booking.php?type=transport&id=<?= $booking['transport_booking_id'] ?>" class="action-link">Confirm</a>
                                                    </td>
                                                <?php elseif($booking['status']==='Confirmed' || $booking['status']==='Payment Complete'):?>
                                                    <td class="align-middle text-center">
                                                        <a href="view_booking_details.php?type=transport&id=<?= $booking['final_booking_id'] ?>" class="action-link">View Details</a>
                                                    </td>
                                                <?php else: ?>
                                                    <a class="action-link"></a>
                                                <?php endif;?>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="15" class="text-center py-4">No transport bookings available.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="accordion-item">
                <h2 class="accordion-header" id="headingCustomTour">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseCustomTour" aria-expanded="false" aria-controls="collapseCustomTour">
                        Custom Tour
                    </button>
                </h2>
                <div id="collapseCustomTour" class="accordion-collapse collapse" aria-labelledby="headingCustomTour" data-bs-parent="#bookingAccordion">
                    <div class="accordion-body p-0">
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered mb-0">
                                <thead class="custom-table-header">
                                    <tr>
                                        <th>Request ID</th>
                                        <th>Customer</th>
                                        <th>NIC/Passport</th>
                                        <th>Phone</th>
                                        <th>Arrival</th>
                                        <th>Departure</th>
                                        <th>Travellers</th>
                                        <th>Rooms</th>
                                        <th>Accommodation Pref.</th>
                                        <th>Airport Transfer</th>
                                        <th>Transport Needed</th>
                                        <th>Budget</th>
                                        <th>Total (LKR)</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($customTours)): ?>
                                        <?php foreach ($customTours as $booking): ?>
                                            <tr>
                                                <td><?= $booking['cpkg_id'] ?></td>
                                                <td><?= $booking['full_name'] ?></td>
                                                <td><?= $booking['lead_traveller_identifier']?></td>
                                                <td><?= $booking['phone'] ?></td>
                                                <td><?= $booking['arrival_date']?></td>
                                                <td><?= $booking['departure_date'] ?></td>
                                                <td><?= $booking['traveller_count'] ?></td>
                                                <td><?= $booking['room_count'] ?></td>
                                                <td><?= $booking['accommodation_preference'] ?></td>
                                                <td><?= ($booking['airport_transfer'] == 1) ? 'Yes' : 'No' ?></td>
                                                <td><?= ($booking['transport_needed'] == 1) ? 'Yes' : 'No' ?></td>
                                                <td><?= $booking['budget'] ?></td>
                                                <td><?= ($booking['total_amount'] != null) ? $booking['total_amount'] : 'Pending Finalization' ?></td>
                                                <td><?= $booking['status'] ?></td>
                                                <?php if($booking['status'] === 'Pending Review'): ?>
                                                    <td class="align-middle text-center">
                                                        <a href="final_booking.php?type=customized&id=<?= $booking['cpkg_id'] ?>" class="action-link">Confirm</a>
                                                    </td>
                                                <?php elseif($booking['status']==='Quoted' || $booking['status']==='Payment Complete'):?>
                                                    <td class="align-middle text-center">
                                                        <a href="view_booking_details.php?type=customized&id=<?= $booking['final_booking_id'] ?>" class="action-link">View Details</a>
                                                    </td>
                                                <?php else: ?>
                                                    <a class="action-link"></a>
                                                <?php endif;?>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="15" class="text-center py-4">No custom tour requests available.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </section>
</body>
</html>


