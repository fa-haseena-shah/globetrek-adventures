<?php
    session_start();
    include "db_helper.php";

    if(!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['Staff', 'Admin'])) {
        header("Location: login.php");
        exit();
    }

    $id = $_GET['id'] ?? null;
    $package = null;
    $errors = [];
    $success = "";

    $categories = fetchAll($conn, "SELECT category_id, name FROM category ORDER BY name");

    if($id) {
        $package = fetchOne($conn, "
        SELECT tour_packages.*, category.name AS category_name
        FROM tour_packages
        JOIN category ON tour_packages.category_id = category.category_id
        WHERE pkg_id = ?
        ", "i", [$id]);

        if(!$package) {
            header("Location: services.php");
            exit();
        }
    }

    // adding new package is admin only
    // if $id = empty, that means it is not a pkg update
    if(!$id && $_SESSION['role'] !== 'Admin') {
        header("Location: services.php");
        exit();
    }

    $pkgName = $package['pkg_name'] ?? '';
    $categoryId = $package['category_id'] ?? '';
    $description = $package['description'] ?? '';
    $durationDays = $package['duration_days'] ?? '';
    $price = $package['price_per_head'] ?? '';
    $availability = $package['availability']  ?? 1;
    $imagePath = $package['image'] ?? null;

    if(isset($_POST['save_pkg'])) {
        $pkgName = $_POST['pkg_name'];
        $categoryId = $_POST['category_id'];
        $description = $_POST['description'];
        $durationDays = $_POST['duration_days'];
        $price = $_POST['price'];
        $availability = isset($_POST['availability']) ? 1 : 0;

        // image upload
        if(isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = 'assets/pkgs/';
            $extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $filename = 'pkg_' . time() . '.' . $extension;
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

        if(empty($pkgName) || empty($categoryId) || empty($description) || empty($durationDays) || empty($price)) {
            $errors[] = "Fields cannot be empty.";
        }

        if(count($errors) === 0) {
            if($id) {
                updateRow($conn, "
                UPDATE tour_packages
                SET pkg_name = ?, category_id = ?, description = ?,
                duration_days = ?, price_per_head = ?, availability = ?, image = ?
                WHERE pkg_id = ?
                ", "sisidisi", [$pkgName, $categoryId, $description, $durationDays, $price, $availability, $imagePath, $id]);

                $success = "Package updated successfully!";
                $package = fetchOne($conn, "
                SELECT tour_packages.*, category.name AS category_name
                FROM tour_packages
                JOIN category ON tour_packages.category_id = category.category_id
                WHERE pkg_id = ?
                ", "i", [$id]);

                $pkgName = $package['pkg_name'];
                $categoryId = $package['category_id'];
                $description = $package['description'];
                $durationDays = $package['duration_days'];
                $price = $package['price_per_head'];
                $availability = $package['availability'];
                $imagePath = $package['image'];
            } else {
                $newId = insertRow($conn, "
                INSERT INTO tour_packages(pkg_name, category_id, description, duration_days, price_per_head, availability, image)
                VALUES (?, ?, ?, ?, ?, ?, ?)
                ", "sisidis", [$pkgName, $categoryId, $description, $durationDays, $price, $availability, $imagePath]);
                // redirect to itinerary page after adding
                header("Location: manage_itinerary.php?id=$newId");
                exit();
            }
        }
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $id ? "Update Package #$id" : "Add New Package" ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include "staff_navbar.php"; ?>
    <section class="section-header mt-5 mb-2 text-center">
        <h1><?= $id ? "Updating Package | $pkgName" : "Adding New Package" ?></h1>
    </section>

    <section class="container mb-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <form action="manage_pkg.php<?= $id ? '?id='.$id : '' ?>" method="post" enctype="multipart/form-data" class="manage-form">
                    <?php if(!empty($success)): ?>
                        <div class="alert alert-success"><?= $success ?></div>
                    <?php endif; ?>
                    <?php if(!empty($errors)): ?>
                        <?php foreach($errors as $error): ?>
                            <div class="alert alert-danger"><?= $error ?></div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    <div class="mb-4">
                        <label class="form-label fw-semibold">Package Name:</label>
                        <input type="text" name="pkg_name" class="form-control" value="<?= $pkgName ?>">
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
                        <label class="form-label fw-semibold">Description:</label>
                        <textarea name="description" class="form-control" rows="3"><?= $description ?></textarea>
                    </div>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Duration (Days):</label>
                            <input type="number" name="duration_days" class="form-control" value="<?= $durationDays ?>" min="1">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Price Per Head (LKR):</label>
                            <input type="number" name="price" class="form-control" value="<?= $price ?>" step="0.01">
                        </div>
                    </div>
                    <?php if(!empty($package['image'])): ?>
                        <div class="mb-3">
                            <p class="form-label fw-semibold">Current Image:</p>
                            <img src="<?= $package['image'] ?>" alt="Package Image"
                                style="height:120px; object-fit:cover; border-radius:8px;">
                        </div>
                        <input type="hidden" name="existing_image" value="<?= $package['image'] ?>">
                    <?php endif; ?>
                    <div class="mb-4">
                        <label class="form-label fw-semibold">
                            <?= $id ? 'Replace Image (optional):' : 'Upload Image:' ?>
                        </label>
                        <input type="file" name="image" class="form-control" accept="image/jpeg,image/png,image/webp">
                        <small class="text-muted">JPG, PNG or WebP. Max 2MB.</small>
                    </div>
                    <div class="form-check d-flex justify-content-center mt-4 mb-4">
                        <input class="form-check-input" name="availability" type="checkbox" value="1" id="availability"
                            <?= $availability == 1 ? 'checked' : '' ?>>
                        <label class="form-check-label" for="availability">
                            &nbsp;&nbsp;&nbsp;Available
                        </label>
                    </div>
                    <button type="submit" name="save_pkg" class="btn btn-onboarding w-100">
                        <?= $id ? 'Save Changes' : 'Add Package' ?>
                    </button>
                    <!-- visible to admin onlyy-->
                    <?php if($id && $_SESSION['role'] === 'Admin'): ?>
                        <a href="manage_itinerary.php?id=<?= $id ?>" class="btn btn-outline w-100 mt-3">Edit Itinerary</a>
                    <?php endif; ?>
                </form>
            </div>
        </div>
    </section>
</body>
</html>