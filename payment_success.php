<?php
        session_start();
        $id = $_SESSION['payment_id'] ?? null;
        $total = $_SESSION['total_paid'] ?? null;
        unset($_SESSION['total_paid'], $_SESSION['payment_id']);
        if($_SESSION['role'] === 'Staff' || $_SESSION['role'] === 'Admin') {
            header("Location: dashboard.php");
            exit();
        }
    ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Successful!</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="style.css">
    <script defer src="https://kit.fontawesome.com/e5a3f33c9d.js" crossorigin="anonymous"></script>
</head>
<body>
    <?php include "navbar.php";?>
    <section class="booking-success container d-flex flex-column align-items-center">
        <h1 class="mt-5"><i class="fa-solid fa-thumbs-up"></i></h1>
        <h2 class="mt-3 mb-3 text-center">Thank You For Your Payment</h2>
        <?php if ($id): ?>
            <p>Your Payment ID: <strong>#<?= $id ?></strong></p>
            <p>Your Payment Total: LKR <?= $total?></p>
        <?php endif; ?>
        <a href="traveller_dashboard.php" class="btn btn-primary text-center">Track Bookings</a>
    </section>
    <?php include "footer.php";?>
</body>
</html>