<?php
    session_start();
    include "db_helper.php";

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
    
    $success = "";
    $errors = [];
    $id = $_GET['id'] ?? null;
    $activity = null;

    // dropdown
    $categories   = fetchAll($conn, "SELECT category_id, name FROM category ORDER BY name");
    $destinations = fetchAll($conn, "SELECT destination_id, name FROM destinations ORDER BY name");

    if($id) {
        $activity = fetchOne($conn, "
        SELECT activities.*,
        category.name AS category_name,
        destinations.name AS destination_name
        FROM activities
        JOIN category ON activities.category_id = category.category_id
        JOIN destinations ON activities.destination_id = destinations.destination_id
        WHERE activity_id = ?
        ", "i", [$id]);

        if(!$activity) {
            header("Location: services.php");
            exit();
        }
    }

    $activityName = $activity['activity_name'] ?? '';
    $categoryId = $activity['category_id'] ?? '';
    $destinationId = $activity['destination_id'] ?? '';
    $description = $activity['description'] ?? '';
    $price = $activity['price_per_head'] ?? '';
    $imagePath = $activity['image'] ?? null;

    if(isset($_POST['save_activity'])) {
        $activityName = $_POST['activity_name'];
        $categoryId = $_POST['category_id'];
        $destinationId = $_POST['destination_id'];
        $description = $_POST['description'];
        $price = $_POST['price'];

        // image upload
        if(isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = 'assets/cards/';
            $extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $filename = 'activity_' . time() . '.' . $extension;
            $targetPath = $uploadDir . $filename;
            $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];

            if(!in_array($_FILES['image']['type'], $allowedTypes)) {
                $errors[] = "Only JPG, PNG and WebP images are allowed.";
            } elseif($_FILES['image']['size'] > 2 * 1024 * 1024) {
                $errors[] = "Image must be under 2MB.";
            } else {
                if(move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
                    $imagePath = $targetPath;
                } else {
                    $errors[] = "Image upload failed.";
                }
            }
        }

        if(empty($imagePath)) {
            $imagePath = $_POST['existing_image'] ?? null;
        }

        if(empty($activityName) || empty($categoryId) || empty($destinationId) || empty($description) || empty($price)) {
            $errors[] = "Fields cannot be empty.";
        }

        if(count($errors) === 0) {
            if($id) {
                updateRow($conn, "
                UPDATE activities
                SET activity_name = ?, category_id = ?, destination_id = ?,
                description = ?, price_per_head = ?, image = ?
                WHERE activity_id = ?
                ", "siisdsi", [$activityName, $categoryId, $destinationId, $description, $price, $imagePath, $id]);

                $success  = "Activity updated successfully!";
                // replace autofill data with new data
                $activity = fetchOne($conn, "SELECT * FROM activities WHERE activity_id = ?", "i", [$id]);

            } else {
                insertRow($conn, "
                INSERT INTO activities(activity_name, category_id, destination_id, description, price_per_head, image)
                VALUES (?, ?, ?, ?, ?, ?)
                ", "siisds", [$activityName, $categoryId, $destinationId, $description, $price, $imagePath]);

                $success = "Activity added successfully!";
            }
        }
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add/Update Activity</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include "staff_navbar.php"; ?>

    <section class="section-header mt-5 mb-2 text-center">
        <h1><?= $id ? "Updating Activity #$id" : "Adding New Activity" ?></h1>
    </section>

    <section class="container mb-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <form action="manage_activity.php<?= $id ? '?id='.$id : '' ?>" method="post" enctype="multipart/form-data" class="manage-form">
                    <?php if(!empty($success)): ?>
                        <div class="alert alert-success"><?= $success ?></div>
                    <?php endif; ?>
                    <?php if(!empty($errors)): ?>
                        <?php foreach($errors as $error): ?>
                            <div class="alert alert-danger"><?= $error ?></div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    <div class="mb-4">
                        <label class="form-label fw-semibold">Activity Name:</label>
                        <input type="text" name="activity_name" class="form-control" value="<?= $activityName ?>">
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-semibold">Category:</label>
                        <select name="category_id" class="form-select">
                            <?php foreach($categories as $cat): ?>
                                <option value="<?= $cat['category_id'] ?>"
                                        <?= $cat['category_id'] == $categoryId ? 'selected' : '' ?>>
                                    <?= $cat['name'] ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-semibold">Destination:</label>
                        <select name="destination_id" class="form-select">
                            <?php foreach($destinations as $dest): ?>
                                <option value="<?= $dest['destination_id'] ?>"
                                        <?= $dest['destination_id'] == $destinationId ? 'selected' : '' ?>>
                                    <?= $dest['name'] ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-semibold">Description:</label>
                        <textarea name="description" class="form-control" rows="3"><?= $description ?></textarea>
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-semibold">Price Per Head (LKR):</label>
                        <input type="number" name="price" class="form-control" value="<?= $price ?>">
                    </div>
                    <?php if(!empty($activity['image'])): ?>
                        <div class="mb-3">
                            <p class="form-label fw-semibold">Current Image:</p>
                            <img src="<?= $activity['image'] ?>" alt="Current" style="height:120px; object-fit:cover; border-radius:8px;">
                        </div>
                        <input type="hidden" name="existing_image" value="<?= $activity['image'] ?>">
                    <?php endif; ?>
                    <div class="mb-4">
                        <label class="form-label fw-semibold">
                            <?= $id ? 'Replace Image (optional):' : 'Upload Image:' ?>
                        </label>
                        <input type="file" name="image" class="form-control" accept="image/jpeg,image/png,image/webp">
                        <small class="text-muted">JPG, PNG or WebP. Max 2MB.</small>
                    </div>
                    <button type="submit" name="save_activity" class="btn btn-onboarding w-100">
                        <?= $id ? 'Save Changes' : 'Add Activity' ?>
                    </button>
                </form>
            </div>
        </div>
    </section>
</body>
</html>