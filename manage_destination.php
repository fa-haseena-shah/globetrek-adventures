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
    $destination = null;

    if($id) {
        $destination = fetchOne($conn, "
        SELECT * FROM destinations 
        WHERE destination_id = ?
        ", "i", [$id]);

        if(!$destination) {
            header("Location: services.php");
            exit();
        }
    }

    $name = $destination['name'] ?? '';
    $description = $destination['description'] ?? '';
    $location = $destination['location'] ?? '';
    $imagePath = $destination['image'] ?? null;

    if(isset($_POST['save_destination'])) {
        $name = $_POST['name'];
        $description = $_POST['description'];
        $location = $_POST['location'];

        // image upload
        if(isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = 'assets/destinations/';
            $extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $filename  = 'dest_' . time() . '.' . $extension;
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

        if(empty($name) || empty($description) || empty($location)) {
            $errors[] = "Fields cannot be empty.";
        }

        if(count($errors) === 0) {
            if($id) {
                updateRow($conn, "
                UPDATE destinations
                SET name = ?, description = ?, location = ?, image = ?
                WHERE destination_id = ?
                ", "ssssi", [$name, $description, $location, $imagePath, $id]);

                $success     = "Destination updated successfully!";
                $destination = fetchOne($conn, "SELECT * FROM destinations WHERE destination_id = ?", "i", [$id]);
            } else {
                insertRow($conn, "
                INSERT INTO destinations(name, description, location, image)
                VALUES (?, ?, ?, ?)
                ", "ssss", [$name, $description, $location, $imagePath]);

                $success = "Destination added successfully!";
            }
        }
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add/Update Destination</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include "staff_navbar.php"; ?>
    <section class="section-header mt-5 mb-2 text-center">
        <h1><?= $id ? "Updating Destination #$id" : "Adding New Destination" ?></h1>
    </section>

    <section class="container mb-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <form action="manage_destination.php<?= $id ? '?id='.$id : '' ?>" method="post" enctype="multipart/form-data" class="manage-form">
                    <?php if(!empty($success)): ?>
                        <div class="alert alert-success"><?= $success ?></div>
                    <?php endif; ?>
                    <?php if(!empty($errors)): ?>
                        <?php foreach($errors as $error): ?>
                            <div class="alert alert-danger"><?= $error ?></div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    <div class="mb-4">
                        <label class="form-label fw-semibold">Destination Name:</label>
                        <input type="text" name="name" class="form-control" value="<?= $name ?>">
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-semibold">Description:</label>
                        <textarea name="description" class="form-control" rows="3"><?= $description ?></textarea>
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-semibold">Location (Province):</label>
                        <input type="text" name="location" class="form-control" value="<?= $location ?>">
                    </div>
                    <?php if(!empty($destination['image'])): ?>
                        <div class="mb-3">
                            <p class="form-label fw-semibold">Current Image:</p>
                            <img src="<?= $destination['image'] ?>" alt="Current" style="height:120px; object-fit:cover; border-radius:8px;">
                        </div>
                        <input type="hidden" name="existing_image" value="<?= $destination['image'] ?>">
                    <?php endif; ?>
                    <div class="mb-4">
                        <label class="form-label fw-semibold">
                            <?= $id ? 'Replace Image (optional):' : 'Upload Image:' ?>
                        </label>
                        <input type="file" name="image" class="form-control" accept="image/jpeg,image/png,image/webp">
                        <small class="text-muted">JPG, PNG or WebP. Max 2MB.</small>
                    </div>
                    <button type="submit" name="save_destination" class="btn btn-onboarding w-100">
                        <?= $id ? 'Save Changes' : 'Add Destination' ?>
                    </button>
                </form>
            </div>
        </div>
    </section>
</body>
</html>