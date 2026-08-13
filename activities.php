<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Activities</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <script defer src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="style.css">
    <script src="function_script.js" defer></script>
</head>
<body>
    <?php include "navbar.php";?>
    <section class="basic-wrapper">
        <div class="section-header">
            <h1>Activities</h1>
        </div>
        <?php include "db_helper.php";
        $categories = fetchAll($conn, "SELECT * FROM category");?>
        <div class="category-filter activities-filter">
            <button class="btn active" data-filter="all">All</button>
            <?php foreach($categories as $category): ?>
                <button class="btn" data-filter="<?= strtolower($category['name']) ?>">
                    <?= $category['name'] ?>
                </button>
            <?php endforeach; ?>
        </div>
        <?php include "search_bar.php";?>
        <div class="activities-section container py-5">
            <?php $activities = fetchAll($conn, "
            SELECT activities.*, 
            category.name AS category_name,
            destinations.name AS destination_name
            FROM activities
            JOIN category ON activities.category_id = category.category_id
            JOIN destinations ON activities.destination_id = destinations.destination_id"); ?>
            <div class="row g-5">
                <?php foreach($activities as $activity): ?>
                 <div class="col-lg-6" data-category="<?= strtolower($activity['category_name']) ?>">
                    <div class="activity-card d-flex">
                        <img src="<?= $activity['image'] ?>" alt="<?= $activity['activity_name'] ?>" class="activity-img">
                        <div class="activity-info">
                            <h3><?=$activity['activity_name']?></h3>
                            <p class="activity-price">
                                LKR <?=$activity['price_per_head']?>
                            </p>
                            <p class="activity-location">
                                Location: <?=$activity['destination_name']?>
                            </p>
                            <p class="activity-description">
                                <?=$activity['description']?>
                            </p>
                        </div>
                    </div>
                </div>
                 <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php include "footer.php";?>
</body>
</html>