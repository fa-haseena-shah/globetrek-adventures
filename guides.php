<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Travel Guides</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <script defer src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include "navbar.php"; ?>
    <section class="travel-guides">
        <h3>TRAVEL GUIDES</h3>
        <?php include "db_helper.php";?>
        <div class="inline-grid">
            <?php $guides = fetchAll($conn, "
            SELECT * FROM guides"); ?>
            <?php foreach($guides as $guide): ?>
            <div class="inline-grid-item">
                <img src="<?= $guide['photo'] ?>" alt="Guide <?= $guide['full_name'] ?>">
                <h4><?= $guide['full_name'] ?> • </h4>
                <p><?= $guide['field'] ?> Guide</p>
            </div>
            <?php endforeach; ?>
        </div>
        <p>Our experienced travel guides are passionate about showcasing the beauty, culture, wildlife, and history of Sri Lanka. With extensive local knowledge and professional service, they ensure travelers enjoy informative, safe, and memorable experiences throughout their journey.</p>
    </section>
    <?php include "footer.php"; ?>
</body>
</html>