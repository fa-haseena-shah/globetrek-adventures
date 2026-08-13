<?php session_start();

    $searchQuery = $_GET['query'] ?? '';
    $keyword = !empty($searchQuery) ? '%' . $searchQuery . '%' : '%';
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

    // tour packages
    $packages = fetchAll($conn, "
    SELECT tour_packages.*,category.name AS category_name
    FROM tour_packages
    JOIN category ON tour_packages.category_id = category.category_id
    WHERE tour_packages.pkg_name LIKE ?
    OR category.name LIKE ?
    ORDER BY tour_packages.pkg_id ASC
    ", "ss", [$keyword, $keyword]);

    // accommodations
    $accommodations = fetchAll($conn, "
    SELECT accommodation.*,destinations.name AS destination_name, destinations.location AS destination_location
    FROM accommodation
    JOIN destinations ON accommodation.destination_id = destinations.destination_id
    WHERE accommodation.name LIKE ?
    OR accommodation.standard LIKE ?
    OR accommodation.property_type LIKE ?
    OR destinations.name LIKE ?
    ORDER BY accommodation.accommodation_id ASC
    ", "ssss", [$keyword, $keyword, $keyword, $keyword]);

    // transport
    $transport = fetchAll($conn, "
    SELECT * FROM transport
    WHERE name LIKE ?
    OR vehicle_type LIKE ?
    ORDER BY transport_id ASC
    ", "ss", [$keyword, $keyword]);

    // destinnations
    $destinations = fetchAll($conn, "
    SELECT * FROM destinations
    WHERE name LIKE ?
    OR description LIKE ?
    OR location LIKE ?
    ORDER BY destination_id ASC
    ", "sss", [$keyword, $keyword, $keyword]);

    // activities
    $activities = fetchAll($conn, "
    SELECT activities.*, category.name AS category_name, destinations.name AS destination_name
    FROM activities
    JOIN category ON activities.category_id = category.category_id
    JOIN destinations ON activities.destination_id = destinations.destination_id
    WHERE activities.activity_name LIKE ?
    OR category.name LIKE ?
    OR destinations.name LIKE ?
    ORDER BY activities.activity_id ASC
    ", "sss", [$keyword, $keyword, $keyword]);

    // guides
    $guides = fetchAll($conn, "
    SELECT * FROM guides
    WHERE full_name LIKE ?
    OR field LIKE ?
    ORDER BY guide_id ASC
    ", "ss", [$keyword, $keyword]);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Viewing All Service Information</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <script defer src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include "staff_navbar.php";?>
    <section class="section-header mt-5 mb-2">
        <h2><i class="fa-regular fa-circle-check"></i></i>&nbsp;&nbsp;&nbsp;Manage Service Information</h2>
    </section>

    <!-- search -->
    <section class="search-bar bg-transparent py-4 d-flex">
        <div class="container">
            <form action="services.php" method="get" class="row g-2 align-items-center">
                
                <div class="col-md-8 col-sm-12">
                    <input type="text" name="query" class="form-control" placeholder="Search..." value="<?= $searchQuery ?>">
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-primary">SEARCH</button>
                </div>
            </form>
        </div>
        <!-- add service dropdown-->
        <div class="dropdown mt-3">
            <button class="btn btn-dropdown dropdown-toggle add-btn"
                    type="button"
                    data-bs-toggle="dropdown"
                    aria-expanded="false">
                <i class="bi bi-plus-lg"></i> Add New
            </button>

            <ul class="dropdown-menu dropdown-menu-end shadow">
                <li>
                    <a class="dropdown-item" href="manage_pkg.php">Tour Package</a>
                </li>
                <li>
                    <a class="dropdown-item" href="manage_destination.php">Destination</a>
                </li>
                <li>
                    <a class="dropdown-item" href="manage_accommodation.php">Accommodation</a>
                </li>
                <li>
                    <a class="dropdown-item" href="manage_transport.php">Transport</a>
                </li>
                <li>
                    <a class="dropdown-item" href="manage_activity.php">Activity</a>
                </li>
                <li>
                    <a class="dropdown-item" href="manage_guide.php">Guide</a>
                </li>
            </ul>
        </div>
    </section>

    <section class="container pb-5">
        <div class="accordion" id="serviceAccordion">
            <!-- tour pkgs -->
            <div class="accordion-item">
                <h2 class="accordion-header" id="headingTour">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTour" aria-expanded="false" aria-controls="collapseTour">
                        Tour Packages
                    </button>
                </h2>
                <div id="collapseTour" class="accordion-collapse collapse" aria-labelledby="headingTour" data-bs-parent="#serviceAccordion">
                    <div class="accordion-body p-0">
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered mb-0">
                                <thead class="custom-table-header">
                                    <tr>
                                        <th>Package ID</th>
                                        <th>Category</th>
                                        <th>Package Name</th>
                                        <th style="width: 10%;">Description</th>
                                        <th>Duration Days</th>
                                        <th>Price Per Head</th>
                                        <th>Availability</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($packages)): ?>
                                        <?php foreach ($packages as $package): ?>
                                            <tr>
                                                <td><?= $package['pkg_id'] ?></td>
                                                <td><?= $package['category_name'] ?></td>
                                                <td><?= $package['pkg_name'] ?></td>
                                                <td><?= $package['description'] ?></td>
                                                <td><?= $package['duration_days'] ?></td>
                                                <td><?= $package['price_per_head'] ?></td>
                                                <td><?=($package['availability'] == 1) ? 'Yes' : 'No' ?></td>
                                                <td>
                                                    <a href="manage_pkg.php?id=<?= $package['pkg_id'] ?>" class="action-link">Update</a>&nbsp;&nbsp;&nbsp;
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="15" class="text-center py-4">No packages available.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- accommodation -->
            <div class="accordion-item">
                <h2 class="accordion-header" id="headingAccommodation">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseAccomm" aria-expanded="false" aria-controls="collapseAccomm">
                        Accommodation
                    </button>
                </h2>
                <div id="collapseAccomm" class="accordion-collapse collapse" aria-labelledby="headingAccommodation" data-bs-parent="#serviceAccordion">
                    <div class="accordion-body p-0">
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered mb-0">
                                <thead class="custom-table-header">
                                    <tr>
                                        <th>Accommodation ID</th>
                                        <th>Accommodation Name</th>
                                        <th>Destination</th>
                                        <th>Rating</th>
                                        <th>Standard</th>
                                        <th>Price Per Night</th>
                                        <th style="width: 10%;">Description</th>
                                        <th>Availability</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($accommodations)): ?>
                                        <?php foreach ($accommodations as $accomodation): ?>
                                            <tr>
                                                <td><?= $accomodation['accommodation_id'] ?></td>
                                                <td><?= $accomodation['name'] ?></td>
                                                <td><?= $accomodation['destination_name'] ?></td>
                                                <td><?= $accomodation['rating'] ?></td>
                                                <td><?= $accomodation['standard'] ?></td>
                                                <td><?= $accomodation['price_per_night'] ?></td>
                                                <td><?= $accomodation['description'] ?></td>
                                                <td><?=($accomodation['availability'] == 1) ? 'Yes' : 'No' ?></td>
                                                <td>
                                                    <a href="manage_accommodation.php?id=<?= $accomodation['accommodation_id'] ?>" class="action-link">Update</a>&nbsp;&nbsp;&nbsp;
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="15" class="text-center py-4">No acccommodations available.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- transport -->
            <div class="accordion-item">
                <h2 class="accordion-header" id="headingTransport">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTransport" aria-expanded="false" aria-controls="collapseTransport">
                        Transport
                    </button>
                </h2>
                <div id="collapseTransport" class="accordion-collapse collapse" aria-labelledby="headingTransport" data-bs-parent="#serviceAccordion">
                    <div class="accordion-body p-0">
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered mb-0">
                                <thead class="custom-table-header">
                                    <tr>
                                        <th>Transport ID</th>
                                        <th>Transport Name</th>
                                        <th>Vehicle Type</th>
                                        <th>Price Per Day</th>
                                        <th>Capacity</th>
                                        <th>Availability</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($transport)): ?>
                                        <?php foreach ($transport as $trns): ?>
                                            <tr>
                                                <td><?= $trns['transport_id'] ?></td>
                                                <td><?= $trns['name'] ?></td>
                                                <td><?= $trns['vehicle_type'] ?></td>
                                                <td><?= $trns['price_per_head'] ?></td>
                                                <td><?= $trns['capacity'] ?></td>
                                                <td><?=($trns['availability'] == 1) ? 'Yes' : 'No' ?></td>
                                                <td>
                                                    <a href="manage_transport.php?id=<?= $trns['transport_id'] ?>" class="action-link">Update</a>&nbsp;&nbsp;&nbsp;
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="15" class="text-center py-4">No transport available.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div> 

            <!-- destinations -->
            <div class="accordion-item">
                <h2 class="accordion-header" id="headingDestinations">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseDestination" aria-expanded="false" aria-controls="collapseDestination">
                        Destinations
                    </button>
                </h2>
                <div id="collapseDestination" class="accordion-collapse collapse" aria-labelledby="headingDestinations" data-bs-parent="#serviceAccordion">
                    <div class="accordion-body p-0">
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered mb-0">
                                <thead class="custom-table-header">
                                    <tr>
                                        <th>Destination ID</th>
                                        <th>Destination Name</th>
                                        <th>Description</th>
                                        <th>Province</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($destinations)): ?>
                                        <?php foreach ($destinations as $destination): ?>
                                            <tr>
                                                <td><?= $destination['destination_id'] ?></td>
                                                <td><?= $destination['name'] ?></td>
                                                <td><?= $destination['description'] ?></td>
                                                <td><?= $destination['location'] ?></td>
                                                <td>
                                                    <a href="manage_destination.php?id=<?= $destination['destination_id'] ?>" class="action-link">Update</a>&nbsp;&nbsp;&nbsp;
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="15" class="text-center py-4">No destinations available.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>  

            <!-- activities -->
            <div class="accordion-item">
                <h2 class="accordion-header" id="headingActivities">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseActivity" aria-expanded="false" aria-controls="collapseActivity">
                        Activities
                    </button>
                </h2>
                <div id="collapseActivity" class="accordion-collapse collapse" aria-labelledby="headingActivities" data-bs-parent="#serviceAccordion">
                    <div class="accordion-body p-0">
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered mb-0">
                                <thead class="custom-table-header">
                                    <tr>
                                        <th>Activity ID</th>
                                        <th>Activity Name</th>
                                        <th>Category</th>
                                        <th>Destination</th>
                                        <th>Description</th>
                                        <th>Price Per Head</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($activities)): ?>
                                        <?php foreach ($activities as $activity): ?>
                                            <tr>
                                                <td><?= $activity['activity_id'] ?></td>
                                                <td><?= $activity['activity_name'] ?></td>
                                                <td><?= $activity['category_name'] ?></td>
                                                <td><?= $activity['destination_name'] ?></td>
                                                <td><?= $activity['description'] ?></td>
                                                <td><?= $activity['price_per_head'] ?></td>
                                                <td>
                                                    <a href="manage_activity.php?id=<?= $activity['activity_id'] ?>" class="action-link">Update</a>&nbsp;&nbsp;&nbsp;
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="15" class="text-center py-4">No activity available.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- guides -->
            <div class="accordion-item">
                <h2 class="accordion-header" id="headingGuides">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseGuides" aria-expanded="false" aria-controls="collapseGuides">
                        Guides
                    </button>
                </h2>
                <div id="collapseGuides" class="accordion-collapse collapse" aria-labelledby="headingGuides" data-bs-parent="#serviceAccordion">
                    <div class="accordion-body p-0">
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered mb-0">
                                <thead class="custom-table-header">
                                    <tr>
                                        <th>Guide ID</th>
                                        <th>Guide Name</th>
                                        <th>Field</th>
                                        <th>Bio</th>
                                        <th>Phone</th>
                                        <th>Email</th>
                                        <th>Availability</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($guides)): ?>
                                        <?php foreach ($guides as $guide): ?>
                                            <tr>
                                                <td><?= $guide['guide_id'] ?></td>
                                                <td><?= $guide['full_name'] ?></td>
                                                <td><?= $guide['field'] ?></td>
                                                <td><?= $guide['biography'] ?></td>
                                                <td><?= $guide['phone'] ?></td>
                                                <td><?= $guide['email'] ?></td>
                                                <td><?=($guide['availability'] == 1) ? 'Yes' : 'No' ?></td>
                                                <td>
                                                    <a href="manage_guide.php?id=<?= $guide['guide_id'] ?>" class="action-link">Update</a>&nbsp;&nbsp;&nbsp;
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="15" class="text-center py-4">No guide available.</td>
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