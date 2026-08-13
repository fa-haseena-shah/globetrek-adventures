<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tropical Destinations Await!</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <script defer src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="style.css">
    <script src="function_script.js" defer></script>
</head>
<body>
    <?php include "navbar.php";?>
    <section class="basic-wrapper">
        <div class="section-header">
            <h1>Destinations</h1>
        </div>
        <?php include "search_bar.php";?>
        <?php include "db_helper.php";?>
        <div class="grid-container">
            <?php $destinations = fetchAll($conn, "
            SELECT * FROM destinations"); ?>
            <?php foreach($destinations as $destination): ?>
            <div class="card" data-category="<?= strtolower($destination['location']) ?>">
                <div class="card-content">
                    <img src="<?= $destination['image'] ?>" alt="<?= $destination['name'] ?>">
                    <div class="pkg-header">
                        <p class="fw-bold fs-6"><?=$destination['name']?></p>
                    </div>
                    <a href="destination_details.php?id=<?= urlencode($destination['destination_id'])?>" class="btn btn-primary">View Details</a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </section>
    <?php include "footer.php";?>
</body>
</html>