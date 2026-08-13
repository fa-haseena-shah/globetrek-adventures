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
    $guide = null;

    if($id) {
        $guide = fetchOne($conn, "SELECT * FROM guides WHERE guide_id = ?", "i", [$id]);

        if(!$guide) {
            header("Location: services.php");
            exit();
        }
    }

    $fullName = $guide['full_name'] ?? '';
    $field = $guide['field'] ?? '';
    $biography = $guide['biography'] ?? '';
    $phone = $guide['phone'] ?? '';
    $email = $guide['email'] ?? '';
    $availability = $guide['availability'] ?? 1;
    $imagePath = $guide['photo'] ?? null;

    if(isset($_POST['save_guide'])) {
        $fullName = $_POST['full_name'];
        $field = $_POST['field'];
        $biography = $_POST['biography'];
        $phone = $_POST['phone'];
        $email = $_POST['email'];
        $availability = isset($_POST['availability']) ? 1 : 0;

        // image upload
        if(isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = 'assets/guides/';
            $extension = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
            $filename = 'guide_' . time() . '.' . $extension;
            $targetPath = $uploadDir . $filename;
            $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];

            if(!in_array($_FILES['photo']['type'], $allowedTypes)) {
                $errors[] = "Only JPG, PNG and WebP images are allowed.";
            } elseif($_FILES['photo']['size'] > 2 * 1024 * 1024) {
                $errors[] = "Image must be under 2MB.";
            } else {
                if(move_uploaded_file($_FILES['photo']['tmp_name'], $targetPath)) {
                    $imagePath = $targetPath;
                } else {
                    $errors[] = "Image upload failed.";
                }
            }
        }

        if(empty($imagePath)) {
            $imagePath = $_POST['existing_photo'] ?? null;
        }

        if(empty($fullName) || empty($field) || empty($biography) || empty($phone) || empty($email)) {
            $errors[] = "Fields cannot be empty.";
        }

        if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Invalid email format.";
        }

        if(count($errors) === 0) {
            if($id) {
                updateRow($conn, "
                    UPDATE guides
                    SET full_name = ?, field = ?, biography = ?,
                        phone = ?, email = ?, availability = ?, photo = ?
                    WHERE guide_id = ?
                ", "sssssisi", [$fullName, $field, $biography, $phone, $email, $availability, $imagePath, $id]);

                $success = "Guide updated successfully!";
                $guide   = fetchOne($conn, "SELECT * FROM guides WHERE guide_id = ?", "i", [$id]);

                // refresh display vars
                $fullName = $guide['full_name'];
                $field = $guide['field'];
                $biography = $guide['biography'];
                $phone = $guide['phone'];
                $email = $guide['email'];
                $availability = $guide['availability'];
                $imagePath = $guide['photo'];
            } else {
                insertRow($conn, "
                    INSERT INTO guides(full_name, field, biography, phone, email, availability, photo)
                    VALUES (?, ?, ?, ?, ?, ?, ?)
                ", "sssssis", [$fullName, $field, $biography, $phone, $email, $availability, $imagePath]);

                $success = "Guide added successfully!";
            }
        }
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add/Update Guide</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
<?php include "staff_navbar.php"; ?>

<section class="section-header mt-5 mb-2 text-center">
    <h1><?= $id ? "Updating Guide #$id" : "Adding New Guide" ?></h1>
</section>

<section class="container mb-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <form action="manage_guide.php<?= $id ? '?id='.$id : '' ?>" method="post" enctype="multipart/form-data" class="manage-form">
                <?php if(!empty($success)): ?>
                    <div class="alert alert-success"><?= $success ?></div>
                <?php endif; ?>
                <?php if(!empty($errors)): ?>
                    <?php foreach($errors as $error): ?>
                        <div class="alert alert-danger"><?= $error ?></div>
                    <?php endforeach; ?>
                <?php endif; ?>
                <div class="mb-4">
                    <label class="form-label fw-semibold">Full Name:</label>
                    <input type="text" name="full_name" class="form-control" value="<?= $fullName ?>">
                </div>
                <div class="mb-4">
                    <label class="form-label fw-semibold">Field of Expertise:</label>
                    <input type="text" name="field" class="form-control" value="<?= $field ?>" placeholder="e.g. Adventure Tourism, Wildlife, Cultural Heritage">
                </div>
                <div class="mb-4">
                    <label class="form-label fw-semibold">Biography:</label>
                    <textarea name="biography" class="form-control" rows="4"><?= $biography ?></textarea>
                </div>
                <div class="mb-4">
                    <label class="form-label fw-semibold">Phone:</label>
                    <input type="tel" name="phone" class="form-control" value="<?= $phone ?>">
                </div>
                <div class="mb-4">
                    <label class="form-label fw-semibold">Email:</label>
                    <input type="email" name="email" class="form-control" value="<?= $email ?>">
                </div>
                <!-- photo -->
                <?php if(!empty($guide['photo'])): ?>
                    <div class="mb-3">
                        <p class="form-label fw-semibold">Current Photo:</p>
                        <img src="<?= $guide['photo'] ?>" alt="Guide Photo"
                             style="height:120px; width:120px; object-fit:cover;">
                    </div>
                    <input type="hidden" name="existing_photo" value="<?= $guide['photo'] ?>">
                <?php endif; ?>
                <div class="mb-4">
                    <label class="form-label fw-semibold">
                        <?= $id ? 'Replace Photo (optional):' : 'Upload Photo:' ?>
                    </label>
                    <input type="file" name="photo" class="form-control" accept="image/jpeg,image/png,image/webp">
                    <small class="text-muted">JPG, PNG or WebP. Max 2MB.</small>
                </div>
                <div class="form-check d-flex justify-content-center mt-4 mb-4">
                    <input class="form-check-input" name="availability" type="checkbox" value="1" id="availability"
                           <?= $availability == 1 ? 'checked' : '' ?>>
                    <label class="form-check-label" for="availability">
                        &nbsp;&nbsp;&nbsp;Available
                    </label>
                </div>
                <?php if($id): ?>
                    <input type="hidden" name="guide_id" value="<?= $id ?>">
                <?php endif; ?>
                <button type="submit" name="save_guide" class="btn btn-onboarding w-100">
                    <?= $id ? 'Save Changes' : 'Add Guide' ?>
                </button>
            </form>
        </div>
    </div>
</section>
</body>
</html>