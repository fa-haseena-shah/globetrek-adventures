<?php session_start();?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Destination Details</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="style.css">
    <script defer src="https://kit.fontawesome.com/e5a3f33c9d.js" crossorigin="anonymous"></script>
</head>
<body>
    <?php include "navbar.php";?>
    <?php 
        include "db_helper.php";
        $id = $_GET['id'] ?? null;
        if(!$id) {
            header("Location: destinations.php");
            exit();
        }

        $destination = fetchOne($conn, "
            SELECT *
            FROM destinations
            WHERE destination_id = ?", "i", [$id]);
        
        if(!$destination) {
            header("Location: destinations.php");
            exit();
        }

        $activities = fetchAll($conn, "
        SELECT * FROM activities
        WHERE destination_id = ?", "i", [$id]);
    ?>
    <section class="details-hero row gx-0 align-items-center overflow-hidden">
        <div class="col-lg-6 details-hero-image bg-light">
            <img src="<?= $destination['image'] ?>" alt="<?= $destination['name'] ?>" class="img-fluid w-100 h-100 object-fit-cover">
        </div>
        <div class="col-lg-6 details-content d-flex align-items-center p-4 p-md-5">
            <div>
                <h1 class="details-title mb-4"><?= $destination['name'] ?></h1>
                <p class="details-description mb-0"><?= $destination['description'] ?></p>
            </div>
        </div>
    </section>

    <section class="dest-activities container py-5">
        <h4 class="text-start text-decoration-underline">Best Known For</h4>
        <div class="activities-section container py-5">
            <div class="row g-5">
                <?php if(empty($activities)): ?>
                    <p>No activities listed for this destination yet.</p>
                    <?php else: ?>
                    <?php foreach($activities as $activity): ?>
                    <div class="col-lg-6">
                        <div class="activity-card d-flex">
                            <img src="<?= $activity['image'] ?>" alt="<?= $activity['activity_name'] ?>" class="activity-img">
                            <div class="activity-info">
                                <h3><?= $activity['activity_name'] ?></h3>
                                <p class="activity-price">
                                    LKR <?= $activity['price_per_head'] ?>
                                </p>
                                <p class="activity-description">
                                    <?= $activity['description'] ?>
                                </p>
                            </div>
                        </div>
                    </div>
                    <?php endforeach;?>
                <?php endif;?>
            </div>
        </div>
    </section>

    <section class="destination-cta container align-items-center justify-content-between py-4">
        <h3 class="destination-cta-title mb-3">Want to Visit?</h3>
        <div class="destination-cta-actions">
            <a href="customize_tour.php" class="btn btn-primary">Customize Tour</a>
            <a href="packages.php" class="btn btn-primary">View Packages</a>
        </div>
    </section>  
    <?php include "footer.php";?> 
</body>
</html>