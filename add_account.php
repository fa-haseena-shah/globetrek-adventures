<?php
    include "db_helper.php";
    $errors = [];
    $success = [];

    session_start();
    // admin-only guard
    if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
        if(!isset($_SESSION['user_id'])) {
            header("Location: login.php");
        } elseif($_SESSION['role'] === 'Customer') {
            header("Location: traveller_dashboard.php");
        } elseif($_SESSION['role'] === 'Staff') {
            header("Location: dashboard.php");
        } else {
            header("Location: login.php");
        }
        exit();
    }

    // same process as registration
    if(isset($_POST['add_acc'])) {
        $fullName = $_POST['full_name'];
        $email = trim($_POST['email']);
        $phone = trim($_POST['phone']);
        $role = $_POST['role'];
        $password = trim($_POST['password']);
        $confirmPw = $_POST['confirmPw'];

        if(empty($fullName) || empty($email) || empty($phone) || empty($password) || empty($confirmPw)) {
            $errors[] = "Please fill in all fields!";
        }
        if(strlen($password) < 8) {
            $errors[] = "Password must be at least 8 characters long.";
        }
        if($password !== $confirmPw) {
            $errors[] =  "Passwords do not match.";
        }

        // check for duplicate email registration atempt
        $count = rowCount($conn, "SELECT * FROM users WHERE email = ?", "s", [$email]);
        if($count > 0) {
           $errors[] =  "This email is already registered.";
        }

        if(count($errors) === 0) {
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            $sql  = "INSERT INTO users(full_name, email, phone, password, role) VALUES (?,?,?,?,?)";
            if(insertRow($conn, $sql, "sssss", [$fullName, $email, $phone, $passwordHash, $role])) {
                $success[] = "Account created successfully.";
            } 
            else {
                $errors[] =  "Account creation failed...!";
            }
        }
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adding User Account</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
	<link rel="stylesheet" href="style.css">
    <script defer src="validation_script.js"></script>
</head>
<body>
    <?php include "staff_navbar.php";?>
    <section class="section-header mt-5 mb-2 text-center">
        <h1>Creating New User Account</h1>
    </section>

    <section class="container mb-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <form action="add_account.php" method="post" class="manage-form" onsubmit="return validateRegisterForm()">
                    <div class="mb-4">
                        <?php if(!empty($success)): ?>
                            <?php foreach($success as $scs): ?>
                                <div class="alert alert-success"><?= $scs ?></div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        <?php if(!empty($errors)): ?>
                            <?php foreach($errors as $error): ?>
                                <div class="alert alert-danger"><?= $error ?></div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        <label class="form-label fw-semibold" for="full_name">User Name:</label>
                        <input type="text" id="full_name" name="full_name" class="form-control">
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-semibold" for="email">Email:</label>
                        <input type="text" id="email" name="email" class="form-control">
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-semibold" for="phone">Phone Number:</label>
                        <input type="text" id="phone" name="phone" class="form-control">
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-semibold" for="role">Role:</label>
                        <select id="role" name="role" class="form-select">
                            <option value="Admin">Admin</option>
                            <option value="Staff">Staff</option>
                        </select>
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-semibold" for="password">Password (min. 8 characters):</label>
                        <input type="password" id="password" name="password" class="form-control">
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-semibold" for="confirmPw">Confirm Password:</label>
                        <input type="password" id="confirmPw" name="confirmPw" class="form-control">
                    </div>
                    <button type="submit" class="btn btn-onboarding w-100" name="add_acc">Create Account</button>
                </form>
            </div>
        </div>
    </section>
</body>
</html>