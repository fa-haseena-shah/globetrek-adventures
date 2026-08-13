<?php session_start();
    include "db_helper.php";
    $searchQuery  = $_GET['query'] ?? '';
    $searchId = is_numeric($searchQuery) ? (int)$searchQuery : 0;
    $keyword = !empty($searchQuery) ? '%' . $searchQuery . '%' : '%';
    $errors = [];
    $success = [];

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

    $users = fetchAll($conn, "
    SELECT * FROM users
    WHERE (full_name LIKE ? OR user_id = ?)
    AND role IN ('Staff', 'Admin')
    ORDER BY role DESC, full_name ASC
    ", "si", [$keyword, $searchId]);

    // delete account
     if(isset($_POST['delete'])) {
        $id = $_POST['id'] ?? '';
        if(!$id) {
            $errors[] = "Invalid request.";
        } 
        
        if($id == $_SESSION['user_id']) {
            $errors[] = "You cannot delete your own account.";
        }

        if(count($errors) === 0) {
            runQuery($conn, "
            DELETE FROM users 
            WHERE user_id = ?
            ","i", [$id]);
            $success[] = "Account deleted!"; 
            
            header("Location: accounts.php");
            exit();
        }
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Account</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
	<link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include "staff_navbar.php";?>
    <section class="section-header mt-5 mb-2 text-center">
        <h1>User Accounts</h1>
    </section>

    <?php if(!empty($success)): ?>
        <?php foreach($success as $scs): ?>
            <div class="alert alert-danger"><?= $scs ?></div>
        <?php endforeach; ?>
    <?php endif; ?>
    <?php if(!empty($errors)): ?>
        <?php foreach($errors as $error): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endforeach; ?>
    <?php endif; ?>

    <!-- search bar -->
    <section class="search-bar bg-transparent py-4">
        <div class="d-flex justify-content-between align-items-start">
            <div class="container">
                <form action="accounts.php" method="get" class="align-items-center">
                    <div class="col-md-6 col-sm-12 m-2">
                        <input type="text" name="query" class="form-control" placeholder="Search by name or role..." value="<?= htmlspecialchars($searchQuery) ?>">
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn btn-primary">SEARCH</button>
                    </div>
                </form>
            </div>
            <div>
                <a href="add_account.php" class="btn btn-primary w-100">Add Account</a>
            </div>
        </div>
    </section>

    <!-- user details table-->
    <section class="table-responsive">
        <table class="table table-striped table-bordered">
            <thead class="custom-table-header">
                <tr>
                    <th scope="col">User ID</th>
                    <th scope="col">User Name</th>
                    <th scope="col">Email</th>
                    <th scope="col">Phone</th>
                    <th scope="col">Role</th>
                    <th scope="col">Manage Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($users)): ?>
                <?php foreach($users as $user):?>
                <tr>
                    <td>#<?=$user['user_id']?></td>
                    <td><?=$user['full_name']?></td>
                    <td><?=$user['email']?></td>
                    <td><?=$user['phone']?></td>
                    <td><?=$user['role']?></td>
                    <td>
                        <a href="update_account.php?id=<?= $user['user_id'] ?>" class="action-link">Update</a>&nbsp;&nbsp;&nbsp;
                        <form action="accounts.php" method="post" class="d-inline">
                            <input type="hidden" name="id" value="<?= $user['user_id'] ?>">
                            <button type="submit" name="delete" class="action-link" onclick="return confirm('Do you really want to delete this account?');">Delete</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach;?>      
                <?php else: ?>
                <tr>
                    <td colspan="15" class="text-center py-4">No accounts...</td>
                </tr>
                <?php endif; ?>              
            </tbody>
        </table>
    </section>
</body>
</html>