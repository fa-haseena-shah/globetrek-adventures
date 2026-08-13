<?php session_start();
    $errors = [];
    $success = "";
    include "db_helper.php";

    if(isset($_POST['submit_contact'])) {
            $userId = $_SESSION['user_id'] ?? null;
            $fullName = $_POST['full_name'] ?? ($_SESSION['user_name'] ?? '');
            $email = $_POST['email'] ?? ($_SESSION['user_email'] ?? '');
            $message = $_POST['message'];

            if(empty($fullName) || empty($email) || empty($message)) {
                $errors[] = "Please fill in all fields!";
            }

            if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = "Invalid email format.";
            }

            if(count($errors) === 0) {
                $sql = "INSERT INTO customer_query(user_id, name, email, message, status) VALUES (?,?,?,?,?)";
                insertRow($conn, $sql, "issss", [$userId, $fullName, $email, $message, 'Open']);
                $success = "Thank you for your query. Kindly expect a reply via " . $email . " !";
            }
        }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <script defer src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="style.css">
    <script defer src="validation_script.js"></script>
    <script defer src="https://kit.fontawesome.com/e5a3f33c9d.js" crossorigin="anonymous"></script>
</head>
<body>
    <?php include "navbar.php";?>
    <?php 
        
    ?>
    <section class="query-form">
        <h2>Have Any Queries?</h2>
       <div class="d-flex align-items-center contact-bg m-0">
            <div><p><i class="fa-solid fa-phone"></i>&nbsp;+94 77 123 4567</p></div>&nbsp;&nbsp;&nbsp;
            <div><p><i class="fa-regular fa-envelope-open"></i>&nbsp;globetrekadventures2026@gmail.com</p></div>
        </div>
        <p>Fill out this form and kindly hold a maximum of 1 day for a response. We appreciate your patience!</p>
        <form action="contact.php" method="post" class="contact-form" onsubmit="return validateContactForm()">
            <label for="full_name">Full Name</label>
            <input type="text" name="full_name" id="full_name">

            <label for="email">Email</label>
            <input type="email" name="email" id="email">

            <label for="message">Your Message</label>
            <textarea name="message" id="message" placeholder="Hi, can I know..."></textarea>
            <?php if(!empty($success)): ?>
                <div class="alert alert-success"><?= $success ?></div>
            <?php endif; ?>

            <?php if(!empty($errors)): ?>
                <?php foreach($errors as $error): ?>
                    <div class="alert alert-danger"><?= $error ?></div>
                <?php endforeach; ?>
            <?php endif; ?>
            <button type="submit" name="submit_contact" class="btn btn-booking w-100">Submit</button>
        </form>
    </section>
    <?php include "footer.php";?>
</body>
</html>