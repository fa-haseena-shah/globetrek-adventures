<?php 
    session_start();
    include "db_helper.php";
    $id = $_GET['id'] ?? null;

    if(!$id) {
        header("Location: packages.php");
        exit();
    }
    
    // fetch packages
    $package = fetchOne($conn, "
    SELECT * FROM tour_packages
    WHERE pkg_id = ?", "i", [$id]);

    if(!$package) {
        header("Location: packages.php");
        exit();
    }

    // fetch pkg_destinations joined with destinations
    $destinations = fetchAll($conn, "
    SELECT destinations.name, package_destinations.day_number, package_destinations.day_order
    FROM destinations 
    JOIN package_destinations ON destinations.destination_id = package_destinations.destination_id 
    WHERE package_destinations.pkg_id = ?
    GROUP BY destinations.name
    ORDER BY package_destinations.day_number ASC, package_destinations.day_order ASC
    ", "i", [$id]);

    // fetch package activities joined with activities
    $activities = fetchAll($conn, "
    SELECT activities.*, package_activities.day_number, package_activities.day_order
    FROM activities
    JOIN package_activities ON activities.activity_id = package_activities.activity_id
    WHERE package_activities.pkg_id = ?
    ORDER BY package_activities.day_number ASC, package_activities.day_order ASC
    ", "i", [$id]);

    // pulling itinerary
    $activitiesByDay = [];
    // loop through each activity &
    // add it to activitiesByDay array according using day number as index
    foreach($activities as $activity) {
        $activitiesByDay[$activity['day_number']][] = $activity;
    }

    // do same for destinations
    $destinationsByDay = [];
    foreach($destinations as $destination) {
        $destinationsByDay[$destination['day_number']][] = $destination;
    }

    $allDays = array_unique( // remove any duplicates
        array_merge( // merge the two lists of keys into one array
        // grab the day numbers (key of assoc array) of activities and destination separately
        array_keys($activitiesByDay),
        array_keys($destinationsByDay)
    ));
    sort($allDays); // sort the days from 1-n 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Package Details</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="style.css">
    <script defer src="https://kit.fontawesome.com/e5a3f33c9d.js" crossorigin="anonymous"></script>
</head>
<body>
    <?php include "navbar.php";?>
    <section class="package-hero overflow-hidden">
        <div class="container-fluid p-0">
            <div class="row gx-0 align-items-center">
                <div class="col-lg-6 package-hero-image">
                    <img src="<?= $package['image']?>" alt="<?= $package['pkg_name']?>" class="img-fluid w-100 h-100 object-fit-cover">
                </div>

                <div class="col-lg-6 package-hero-copy d-flex align-items-center p-4 p-md-5">
                    <div class="w-100">
                        <p class="package-type text-uppercase mb-3"><?= htmlspecialchars($package['category_name'] ?? 'Package') ?></p>
                        <h1 class="package-title"><?= $package['pkg_name']?></h1>

                        <div class="package-meta d-flex flex-column flex-sm-row flex-wrap gap-3 mb-4">
                            <span class="package-duration text-uppercase">Duration: <?= $package['duration_days']?> Days</span>
                            <span class="package-price fw-semibold">LKR <?= number_format($package['price_per_head'] ?? 0) ?></span>
                        </div>

                        <a href="book_tour.php?id=<?= urlencode($package['pkg_id']) ?>" class="btn btn-booking btn-booking-full">Book</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- DESTINATIONS -->
    <section class="package-highlights py-5">
        <div class="container">
            <div class="row gx-5 align-items-start">
                <div class="col-md-4">
                    <h4 class="mb-4 text-decoration-underline"><i class="fa-solid fa-map-pin"></i>&nbsp;&nbsp;Destinations</h4>
                </div>
                <div class="col-md-8">
                    <div class="row">
                        <?php foreach($destinations as $desti): ?>
                        <div class="col-sm-6">
                            <ul>
                                <li><?= $desti['name']?></li>
                            </ul>
                        </div>
                        <?php endforeach;?>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ACTIVITIES -->
    <section class="dest-activities container py-5">
        <h4 class="text-start text-decoration-underline"><i class="fa-brands fa-angellist"></i>&nbsp;&nbsp;Activities</h4>
        <div class="activities-section container py-5">
                <div class="row g-5">
                    <?php foreach($activities as $acti):?>
                    <div class="col-lg-6">
                        <div class="activity-card d-flex">
                            <img src="<?= $acti['image'] ?>" alt="<?= $acti['activity_name'] ?>" class="activity-img">
                            <div class="activity-info">
                                <h3><?= $acti['activity_name'] ?></h3>
                                <p class="activity-price">
                                    LKR <?= $acti['price_per_head'] ?>
                                </p>
                                <p class="activity-description"><?= $acti['description'] ?></p>
                            </div>
                        </div>
                    </div>
                    <?php endforeach;?>
                </div>
            </div>
    </section>

    <!-- ITINERARY -->
    <section class="package-itinerary">
        <div class="container">
            <h4 class="text-decoration-underline mb-5">Itinerary</h4>
            <div class="row gx-5">
                <div class="col-md-5">
                    <?php foreach($allDays as $day): ?>
                    <div class="itinerary-day mb-5">
                        <p class="fw-bold fs-5 mb-3">DAY <?= $day?></p>
                        <ul>
                            <?php if(isset($destinationsByDay[$day])): ?>
                                <?php foreach($destinationsByDay[$day] as $dest): ?>
                                    <li class="list-unstyled"><i class="fa-solid fa-map-pin"></i>&nbsp;&nbsp;<?= $dest['name'] ?></li>
                                <?php endforeach; ?>
                            <?php endif; ?>

                            <?php if(isset($activitiesByDay[$day])): ?>
                                <?php foreach($activitiesByDay[$day] as $act): ?>
                                    <li class="list-unstyled"><i class="fa-brands fa-angellist"></i>&nbsp;&nbsp;<?= $act['activity_name'] ?></li>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </ul>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div class="col-md-7">
                    <div><img src="" alt=""></div>
                </div>
            </div>
            <a href="book_tour.php?id=<?= urlencode($package['pkg_id']) ?>" class="btn btn-primary w-100">Book</a>
        </div>
    </section>

    <?php include "footer.php"; ?>
</body>
</html>