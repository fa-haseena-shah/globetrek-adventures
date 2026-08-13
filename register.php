<?php 
    ini_set('display_errors', 0);
    error_reporting(E_ALL);
    // if someone is logged in, redirect to index
    session_start();
    if (isset($_SESSION["user_id"])) {
        header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register as Traveller</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <script defer src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="style.css">
    <script defer src="validation_script.js"></script>
</head>
<body>
    <?php
    $errors = array();
    if(isset($_POST['register'])) {
        include "db_helper.php";   

        $fullName = $_POST['full_name'];
        $email = $_POST['email'];
        $phone = $_POST['phone'];
        $password = $_POST['password'];
        $confirmPw = $_POST['confirmPw'];

        // validation
        if(empty($fullName) || empty($email) || empty($phone) || empty($password) || empty($confirmPw)) {
            $errors[] = "Please fill in all fields!";
        }
        if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] =  "Invalid email format.";
        }
        if(!preg_match('/^[0-9]{10}$/', $phone)) {
            $errors[] = "Phone number must contain 10 digits.";
        }
        if(strlen($password) < 8) {
            $errors[] = "Password must be at least 8 characters long.";
        }
        if($password !== $confirmPw) {
            $errors[] =  "Passwords do not match.";
        }

        // if there are no errors so far, check for duplicate email registration atempt
        $count = rowCount($conn, "SELECT * FROM users WHERE email = ?", "s", [$email]);
        if($count > 0) {
           $errors[] =  "This email is already registered.";
        }

        // insert if no errors
        if(count($errors) === 0) {
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            $role = "Customer";
            $sql  = "INSERT INTO users(full_name, email, phone, password, role) VALUES (?,?,?,?,?)";
            if(insertRow($conn, $sql, "sssss", [$fullName, $email, $phone, $passwordHash, $role])) {
                header("Location: login.php");
                exit();
            } 
            else {
                $errors[] =  "Registration failed. Please try again.";
            }
        }
    }
    ?>

    <section class="register-wrapper">
        <div class="container-fluid min-vh-100 d-flex align-items-center justify-content-center register-card">
            <div class="card border-0 register-card">
                <div class="row g-0">
                    <div class="col-sm-6 p-5">
                        <?php include "navlogo.php";?>
                        <div class="mb-4">
                            <p class="text-muted mb-1">Before You Book,</p>
                            <h1 class="display-6 fw-bold">Register</h1>
                        </div>

                        <form action="register.php" method="post" onsubmit="return validateRegisterForm()">
                            <div class="mb-3">
                                <input type="text" class="form-control" name="full_name" id="full_name" placeholder="Full Name...">
                            </div>
                            <div class="mb-3">
                                <input type="text" class="form-control" name="email" id="email" placeholder="hello@example.com">
                            </div>
                            <div class="mb-3">
                                <input type="text" class="form-control" name="phone" id="phone" placeholder="+XX...">
                            </div>
                            <div class="mb-3">
                                <input type="password" class="form-control" name="password" id="password" placeholder="Password (minium 8 characters)...">
                            </div>
                            <div class="mb-3">
                                <input type="password" class="form-control" name="confirmPw" id="confirmPw" placeholder="Confirm Password...">
                            </div>
                            <?php if (!empty($errors)): ?>
                                <?php foreach ($errors as $error): ?>
                                    <div class="alert alert-danger">
                                        <?= htmlspecialchars($error) ?>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                            <button type="submit" name="register" class="btn btn-onboarding w-100">Create Account</button>
                        </form>
                        <p class="text-center mt-4 mb-0">Already have an account?<a href="login.php">Login</a></p>
                    </div>
                    <div class="col-sm-6 d-none d-sm-block register-image">
                        <div class="image-overlay">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</body>
</html>