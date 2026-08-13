<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Our Accommodation</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <script defer src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="style.css">
    <script src="function_script.js" defer></script>
</head>
<body>
    
   <?php include "navbar.php";?>
    <section class="basic-wrapper">
        <div class="section-header">
            <h1>Our Accommodation</h1>
            <p>Enjoy Comfortable Stays Across Sri Lanka</p>
        </div>
        <?php include "search_bar.php";?>
        <?php include "db_helper.php";?>
        <div class="grid-container">
            <?php $accommodations = fetchAll($conn, "
            SELECT * FROM accommodation"); ?>
            <?php foreach($accommodations as $acmdt): ?>
            <div class="card">
                <div class="card-content">
                    <img src="<?= $acmdt['image'] ?>" alt="<?= $acmdt['name'] ?>">
                    <div class="pkg-header">
                        <p class="fw-bold fs-6"><?=$acmdt['name']?></p>
                        <p>LKR <?=$acmdt['price_per_night']?></p>
                        <p>Rated At <?=$acmdt['rating']?></p>
                    </div>
                    <a href="accommodation_details.php?id=<?= urlencode($acmdt['accommodation_id'])?>" class="btn btn-primary">View Details</a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </section>
    <?php include "footer.php";?>
</body>
</html>