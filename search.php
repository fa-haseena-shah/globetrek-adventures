<?php
    session_start();
    include "db_helper.php";

    // get keyword appended to url and turn it into query using wildcards
    $query = isset($_GET['query']) && !empty($_GET['query']) ? '%' . $_GET['query'] . '%' : null;
    $packages = $activities = $accommodation = [];

    // get matching results and store into arrays
    if($query) {
        $packages = fetchAll($conn, "
            SELECT DISTINCT tour_packages.*
            FROM tour_packages
            JOIN package_destinations ON tour_packages.pkg_id = package_destinations.pkg_id
            JOIN destinations ON package_destinations.destination_id = destinations.destination_id
            WHERE destinations.name LIKE ?
        ", "s", [$query]);

        $activities = fetchAll($conn, "
            SELECT DISTINCT activities.*
            FROM activities
            JOIN destinations ON activities.destination_id = destinations.destination_id
            WHERE destinations.name LIKE ?
        ", "s", [$query]);

        $accommodation = fetchAll($conn, "
            SELECT DISTINCT accommodation.*
            FROM accommodation
            JOIN destinations ON accommodation.destination_id = destinations.destination_id
            WHERE destinations.name LIKE ?
        ", "s", [$query]);
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Search Results</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <script defer src="https://kit.fontawesome.com/e5a3f33c9d.js" crossorigin="anonymous"></script>
</head>
<body>
    <?php include "navbar.php"; ?>
    <section class="basic-wrapper">
        <h2>Results for "<?= htmlspecialchars($_GET['query'] ?? '') ?>"</h2>
        <!-- no result handling -->
        <?php if(empty($packages) && empty($activities) && empty($accommodation)): ?>
            <p><i class="fa-regular fa-face-frown"></i>Sorry, we couldn't find anything.</p>
        <!-- display for each -->
        <?php else: ?>
            <?php if(!empty($packages)): ?>
                <h4>Packages</h4>
                <div class="grid-container">
                    <?php foreach($packages as $p): ?>
                        <div class="card">
                            <div class="card-content">
                                <img src="<?= $p['image'] ?>" alt="<?= $p['pkg_name'] ?>">
                                <div class="pkg-header">
                                    <p class="fw-bold"><?= $p['pkg_name'] ?></p>
                                    <p>LKR <?= $p['price_per_head'] ?></p>
                                </div>
                                <a href="package_details.php?id=<?= $p['pkg_id'] ?>" class="btn btn-primary">View Details</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if(!empty($activities)): ?>
                <h4>Activities</h4>
                <div class="row g-5">
                <?php foreach($activities as $activity): ?>
                 <div class="col-lg-6">
                    <div class="activity-card d-flex">
                        <img src="<?= $activity['image'] ?>" alt="<?= $activity['activity_name'] ?>" class="activity-img">
                        <div class="activity-info">
                            <h3><?=$activity['activity_name']?></h3>
                            <p class="activity-price">
                                LKR <?=$activity['price_per_head']?>
                            </p>
                            <p class="activity-description">
                                <?=$activity['description']?>
                            </p>
                        </div>
                    </div>
                </div>
                 <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <?php if(!empty($accommodation)): ?>
                <h4>Accommodation</h4>
                <div class="grid-container">
                    <?php foreach($accommodation as $ac): ?>
                        <div class="card">
                            <div class="card-content">
                                <img src="<?= $ac['image'] ?>" alt="<?= $ac['name'] ?>">
                                <div class="pkg-header">
                                    <p class="fw-bold"><?= $ac['name'] ?></p>
                                    <p>LKR <?= $ac['price_per_night'] ?></p>
                                    <p>Rated At <?= $ac['rating'] ?></p>
                                </div>
                                <a href="accommodation_details.php?id=<?= $ac['accommodation_id'] ?>" class="btn btn-primary">View Details</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </section>
    <?php include "footer.php"; ?>
</body>
</html>