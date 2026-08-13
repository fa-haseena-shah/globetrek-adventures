<?php
    session_start();
    include "db_helper.php";

    $id = $_GET['id'] ?? null;

    if(!$id) {
        header("Location: accounts.php");
        exit();
    }

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

    $success  = "";
    $errors   = [];

    $user = fetchOne($conn, "
    SELECT * FROM users 
    WHERE user_id = ? 
    AND (role = ? OR role = ?)
    ", "iss", [$id, 'Staff', 'Admin']);

    if(isset($_POST['update_acc'])) {
        $userName = $_POST['full_name'];
        $email = $_POST['email'];
        $phone = $_POST['phone_num'];
        $role = $_POST['role'];

        if(empty($userName) || empty($email) || empty($phone) || empty($role)) {
            $errors[] = "Fields cannot be empty.";
        }

        if(count($errors) === 0) {
            updateRow($conn, "
            UPDATE users
            SET full_name = ?, email = ?, phone = ?, role = ?
            WHERE user_id = ?
            ", "ssssi", [$userName, $email, $phone, $role, $id]);
            
            $success = "User Account updated successfully!";

            // replace in form with new data
            $user = fetchOne($conn, "
            SELECT * FROM users 
            WHERE user_id = ? 
            AND (role = ? OR role = ?)
            ", "iss", [$id, 'Staff', 'Admin']);
        }
    }

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Updating Account</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
	<link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include "staff_navbar.php";?>
    <section class="section-header mt-5 mb-2 text-center">
        <h1>Updating User # <?=$id?></h1>
    </section>

    <section class="container mb-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <form action="update_account.php?id=<?= $id?>" method="post" class="manage-form">
                    <div class="mb-4">
                        <?php if(!empty($success)): ?>
                            <div class="alert alert-success"><?= $success ?></div>
                        <?php endif; ?>
                        <?php if(!empty($errors)): ?>
                            <?php foreach($errors as $error): ?>
                                <div class="alert alert-danger"><?= $error ?></div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        <label class="form-label fw-semibold" for="full_name">User Name:</label>
                        <input type="text" id="full_name" name="full_name" class="form-control" value="<?=$user['full_name']?>">
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-semibold" for="email">Email:</label>
                        <input type="text" id="email" name="email" class="form-control" value="<?=$user['email']?>">
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-semibold" for="phone_num">Phone Number:</label>
                        <input type="text" id="phone_num" name="phone_num" class="form-control" value="<?=$user['phone']?>">
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-semibold" for="role">Role:</label>
                        <select id="role" name="role" class="form-select">
                            <option value="Admin" <?= $user['role'] === 'Admin' ? 'selected' : '' ?>>Admin</option>
                            <option value="Staff" <?= $user['role'] === 'Staff' ? 'selected' : '' ?>>Staff</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-onboarding w-100" name="update_acc">Save Changes</button>
                </form>
            </div>
        </div>
    </section>
</body>
</html>