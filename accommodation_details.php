<?php session_start();?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accommodation Details</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="style.css">
    <script defer src="https://kit.fontawesome.com/e5a3f33c9d.js" crossorigin="anonymous"></script>
</head>
<body>
    <?php include "navbar.php";?>
    <section class="container py-5">
        <?php 
            include "db_helper.php";
            $id = $_GET['id'] ?? null;
            if(!$id) {
                header("Location: accommodation.php");
                exit();
            }
            $accommodation = fetchOne($conn, "
            SELECT accommodation.*, destinations.name AS location
            FROM accommodation
            JOIN destinations ON accommodation.destination_id = destinations.destination_id
            WHERE accommodation_id = ?", "i", [$id]);

            if(!$accommodation) {
                header("Location: accommodation.php");
                exit();
            }
        ?>
        <section class="details-hero row gx-0 align-items-center overflow-hidden">
            <div class="col-lg-6 details-hero-image bg-light">
                <img src="<?= $accommodation['image'] ?>" alt="Accommodation image" class="img-fluid w-100 h-100 object-fit-cover">
            </div>
            <div class="col-lg-6 details-content d-flex align-items-center p-4 p-md-5">
                <div>
                    <p class="details-type text-uppercase mb-3"><?= $accommodation['property_type'] ?></p>
                    <h1 class="details-title mb-4"><?= $accommodation['name'] ?></h1>
                    <div class="details-chip d-flex flex-column flex-sm-row gap-3 gap-sm-4 mb-4">
                        <p class="mb-0"><i class="fa-brands fa-yelp"></i>Rated At <?= $accommodation['rating'] ?></p>
                        <p class="mb-0"><i class="fa-solid fa-location-dot"></i><?= $accommodation['location'] ?></p>
                    </div>
                    <p class="details-description mb-0"><?= $accommodation['description'] ?></p>
                </div>
            </div>
        </section>

        <section class="facilities row align-items-start mt-5 gx-5 text-start">
            <div class="col-lg-4">
                <h3 class="facilities-title mb-3 text-start">Facilities</h3>
            </div>
            <div class="col-lg-8">
                <div class="facility-summary">
                    <div class="facility-item">
                        <span class="facility-label">Ideal For</span>
                        <span class="facility-value"><?= $accommodation['ideal_for'] ?></span>
                    </div>
                    <div class="facility-item">
                        <span class="facility-label">Meals Fullboard</span>
                        <span class="facility-value">
                            <?= ($accommodation['bed_and_breakfast'] === 1) ? "Offered" : "Not Available"?>
                        </span>
                    </div>
                    <div class="facility-item">
                        <span class="facility-label">Price Per Night</span>
                        <span class="facility-value">LKR <?= $accommodation['price_per_night'] ?></span>
                    </div>
                    <div class="facility-item">
                        <span class="facility-label">Location</span>
                        <span class="facility-value"><?= $accommodation['location'] ?></span>
                    </div>
                    <div class="facility-item">
                        <span class="facility-label">Currently</span>
                        <span class="facility-value"><?= ($accommodation['availability']===1) ? 'Available' : 'Unavailable' ?></span>
                    </div>
                </div>
            </div>
            <?php if($accommodation['availability']===0): ?>
                <button class="btn btn-onboarding w-100 mt-5" disabled>Book <?=$accommodation['name']?></button>
            <?php else:?>
            <a href="book_accommodation.php?id=<?= urlencode($accommodation['accommodation_id'])?>" class="btn btn-other">Book <?= $accommodation['name'] ?></a>
            <?php endif;?>
        </section>
    </section>
    <?php include "footer.php";?>
</body>
</html>