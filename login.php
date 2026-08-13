<?php 
    ini_set('display_errors', 0);
    error_reporting(E_ALL);
    // if someone is logged in, redirect to index
    session_start();
    if(isset($_SESSION['user_id'])) {
        $role = $_SESSION['role'] ?? '';
        if($role === 'Admin' || $role === 'Staff') {
            header("Location: dashboard.php");
        } else {
            header("Location: index.php");
        }
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Traveller Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <script defer src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="style.css">
    <script defer src="validation_script.js"></script>
</head>
<body>
    <?php
        $errors = array();
        if(isset($_POST['login'])) {
            include "db_helper.php";
            $email = trim($_POST['email']);
            $password = trim($_POST['password']);

            if(empty($email) || empty($password)) {
                $errors[] = "Please fill in all fields!";
            }
            if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = "Invalid email format.";
            }

            if(empty($errors)) {
                $sql = "SELECT * FROM users WHERE email = ?";
                $user = fetchOne($conn, $sql, "s", [$email]);

                if($user && password_verify($password, $user['password'])) {

                    $_SESSION["user_id"] = $user["user_id"];
                    $_SESSION["name"] = $user["full_name"];
                    $_SESSION["role"] = $user["role"];

                    if($user["role"] === "Staff" || $user["role"] === "Admin") {
                        header("Location: dashboard.php");
                        exit();
                    }
                    header("Location: traveller_dashboard.php");
                    exit();
                }
                else {
                    $errors[] = "Invalid credentials!";
                } 
            }
        }
    ?>
    <section class="login-wrapper">
        <div class="container-fluid min-vh-100 d-flex align-items-center justify-content-center register-card">
            <div class="card border-0 login-card">
                <div class="row g-0">
                    <div class="col-sm-6 p-5">
                        <?php include "navlogo.php";?>
                        <div class="mb-4">
                            <p class="text-muted mb-1">Welcome Back!</p>
                            <h1 class="display-6 fw-bold">Login</h1>
                        </div>

                        <form action="login.php" method="post" onsubmit="return validateLoginForm()">
                            <div class="mb-3">
                                <input type="text" class="form-control" name="email" id="email" placeholder="Enter your email...">
                            </div>
                            <div class="mb-3">
                                <input type="password" class="form-control" name="password" id="password" placeholder="Enter your password...">
                            </div>
                            <?php if (!empty($errors)): ?>
                                <?php foreach ($errors as $error): ?>
                                    <div class="alert alert-danger">
                                        <?= htmlspecialchars($error) ?>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                            <button type="submit" name="login" class="btn btn-onboarding w-100">Login</button>
                        </form>
                        <p class="text-center mt-4 mb-0">Don't have an account?<a href="register.php">Sign Up</a></p>
                    </div>
                    <div class="col-sm-6 d-none d-sm-block login-image">
                        <div class="image-overlay">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</body>
</html>