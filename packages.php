<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tour Packages</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <script defer src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="style.css">
    <script src="function_script.js" defer></script>
</head>
<body>
    <?php include "navbar.php";?>
    <section class="basic-wrapper">
        <div class="section-header">
            <h1>Tour Packages</h1>
        </div>
        <?php include "search_bar.php";?>
        <?php include "db_helper.php";
        $categories = fetchAll($conn, "SELECT * FROM category");?>
        <div class="category-filter pkgs-filter">
            <button class="btn active" data-filter="all">All</button>
            <?php foreach($categories as $category): ?>
                <button class="btn" data-filter="<?= strtolower($category['name']) ?>">
                    <?= $category['name'] ?>
                </button>
            <?php endforeach; ?>
        </div>
        <div class="grid-container">
            <?php $packages = fetchAll($conn, "
            SELECT tour_packages.*, category.name AS category_name
            FROM tour_packages
            JOIN category ON tour_packages.category_id = category.category_id"); ?>
            <?php foreach($packages as $pkg): ?>
            <div class="card pkg-card" data-category="<?= strtolower($pkg['category_name']) ?>">
                <div class="card-content">
                    <img src="<?= $pkg['image'] ?>" alt="<?= $pkg['pkg_name'] ?>">
                    <div class="pkg-header">
                        <p class="fw-bold fs-6"><?=$pkg['pkg_name']?></p>
                        <p>LKR <?=$pkg['price_per_head']?></p>
                    </div>
                    <a href="package_details.php?id=<?= urlencode($pkg['pkg_id'])?>" class="btn btn-primary">View Details</a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </section>
    <?php include "footer.php";?>
</body>
</html>