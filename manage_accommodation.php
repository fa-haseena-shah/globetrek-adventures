<?php session_start();
    include "db_helper.php";
    $success  = "";
    $errors = [];
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

    $id = $_GET['id'] ?? null;
    $accommodation = null;

    // dropdown options
    $destinations = fetchAll($conn, "
    SELECT destination_id, name
    FROM destinations
    ORDER BY name
    ");

    if($id) {
        $accommodation = fetchOne($conn, "
        SELECT accommodation.*,
        accommodation.name AS accomm_name,
        destinations.name AS destination_name,
        destinations.destination_id
        FROM accommodation
        JOIN destinations ON accommodation.destination_id = destinations.destination_id
        WHERE accommodation.accommodation_id = ?
        ", "i", [$id]);


        if(!$accommodation) {
            header("Location: services.php");
            exit();
        }
    }
    // image functionality
    $imagePath = $accommodation['image'] ?? null;

    // since this form manage update AND add, we set null to values if they're not set to prevent array offset
    $name = $accommodation['name'] ?? '';
    $rating = $accommodation['rating'] ?? '';
    $standard = $accommodation['standard'] ?? '';
    $price = $accommodation['price_per_night'] ?? '';
    $description = $accommodation['description'] ?? '';
    $availability = $accommodation['availability'] ?? 1;
    $idealFor = $accommodation['ideal_for'] ?? '';
    $propertyType = $accommodation['property_type'] ?? '';
    $destinationId = $accommodation['destination_id'] ?? '';
    $bnb = $accommodation['bed_and_breakfast'] ?? 0;

    if(isset($_POST['save_accommodation'])) {
        $accommodationName = $_POST['name'];
        $destinationId = $_POST['destination_id'];
        $rating = $_POST['rating'];
        $standard = $_POST['standard'];
        $price = $_POST['price'];
        $desc = $_POST['desc'];
        $availability = isset($_POST['avlb']) ? 1 : 0;
        $idealFor = $_POST['ideal'];
        $property = $_POST['property'];
        $bNB = isset($_POST['bnb']) ? 1 : 0;


        // image functionality
        if(isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $uploadDir  = 'assets/accomm/';
                $extension  = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
                $filename   = 'accomm_' . time() . '.' . $extension;
                $targetPath = $uploadDir . $filename;

                $allowedTypes = ['image/jpeg','image/jpg', 'image/png', 'image/webp'];
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
            
            // use existing image if no new one uploaded
            if(empty($imagePath)) {
                $imagePath = $_POST['existing_image'] ?? null;
            }
        }
        
        if(empty($accommodationName) || empty($destinationId) || empty($rating) || 
        empty($standard) || empty($price) || empty($desc) || empty($idealFor)
        || empty($property)) {
            $errors[] = "Fields cannot be empty.";
        }

        // if there is id => update
        if(count($errors)===0) {
            if($id) {
            updateRow($conn, "
            UPDATE accommodation
            SET destination_id = ?, name = ?, rating = ?, standard = ?, 
                price_per_night = ?, description = ?, availability = ?, 
                ideal_for = ?, property_type = ?, bed_and_breakfast = ?, image = ?
            WHERE accommodation_id = ?
            ", "isdsdsissisi", [$destinationId, $accommodationName, $rating, 
                            $standard, $price, $desc, $availability, $idealFor, $property, $bNB, $imagePath, $id]);
            
            $success = "Accommodation updated successfully!";

            // replace in form with new data
            $accommodation = fetchOne($conn, "
            SELECT * FROM accommodation
            JOIN destinations
            ON accommodation.destination_id = destinations.destination_id
            WHERE accommodation.accommodation_id = ?
            ", "i", [$id]);
        } 
        
        // if there is no id, insert
            else {
                $sql = "INSERT INTO accommodation(destination_id, name, rating, standard, 
                        price_per_night, description, availability, ideal_for, property_type, bed_and_breakfast, image) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                insertRow($conn, $sql, "isdsdsissis", [$destinationId, $accommodationName, $rating, 
                                $standard, $price, $desc, $availability, $idealFor, $property, $bNB, $imagePath]);
                
                $success = "Accommodation added successfully!";

            }
        }
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add/Update Accommodation</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
	<link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include "staff_navbar.php";?>
    <section class="section-header mt-5 mb-2 text-center">
        <h1><?= $id ? "Updating Accommodation #".$id: "Adding New Accommodation" ?></h1>
    </section>

    <section class="container mb-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <form action="manage_accommodation.php<?= $id ? '?id='.$id : '' ?>" method="post" enctype="multipart/form-data" class="manage-form">
                    <div class="mb-4">
                        <?php if(!empty($success)): ?>
                            <div class="alert alert-success"><?= $success ?></div>
                        <?php endif; ?>
                        <?php if(!empty($errors)): ?>
                            <?php foreach($errors as $error): ?>
                                <div class="alert alert-danger"><?= $error ?></div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        <label class="form-label fw-semibold" for="name">Accommodation Name:</label>
                        <input type="text" id="name" name="name" class="form-control" value="<?=$name?>">
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-semibold" for="destination_id">Destination:</label>
                        <select name="destination_id" class="form-select">
                            <?php foreach($destinations as $destination): ?>
                                <option
                                    value="<?= $destination['destination_id'] ?>"
                                    <?= $destination['destination_id'] == ($accommodation['destination_id'] ?? '') ? 'selected' : '' ?>>
                                    <?= $destination['name'] ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-semibold" for="rating">Rating:</label>
                        <input type="text" id="rating" name="rating" class="form-control" value="<?=$rating?>">
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-semibold" for="standard">Standard:</label>
                        <select id="standard" name="standard" class="form-select">
                            <option value="Budget" <?= $standard === 'Budget' ? 'selected' : '' ?>>Budget</option>
                            <option value="Standard" <?= $standard === 'Standard' ? 'selected' : '' ?>>Standard</option>
                            <option value="Luxury" <?= $standard === 'Luxury' ? 'selected' : '' ?>>Luxury</option>
                        </select>
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-semibold" for="price">Price Per Night (LKR):</label>
                        <input type="text" id="price" name="price" class="form-control" value="<?=$price?>">
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-semibold" for="desc">Description:</label>
                        <input type="text" id="desc" name="desc" class="form-control" value="<?=$description?>">
                    </div>
                    <!-- image functionality -->
                    <!-- if image is already uploaded -->
                    <?php if(!empty($accommodation['image'])): ?>
                        <div class="mb-3">
                            <p class="form-label fw-semibold">Image:</p>
                            <img src="<?= $accommodation['image'] ?>" alt="Current" style="height:120px; object-fit:cover; border-radius:8px;">
                        </div>
                        <input type="hidden" name="existing_image" value="<?= $accommodation['image'] ?>">
                    <?php endif; ?>

                    <!-- if upload new image -->
                    <div class="mb-4">
                        <label class="form-label fw-semibold" for="image">
                            <?= $id ? 'Replace Image (optional):' : 'Upload Image:' ?>
                        </label>
                        <input type="file" id="image" name="image" class="form-control" accept="image/jpeg,image/png,image/webp">
                        <small class="text-muted">JPG, PNG or WebP. Max 2MB.</small>
                    </div>
                    <div class="form-check d-flex justify-content-center mt-4">
                            <input class="form-check-input" name="avlb" type="checkbox" value="1" id="avlb"
                                <?= ($accommodation['availability'] ?? 1) == 1 ? 'checked' : '' ?>>
                            <label class="form-check-label" for="avlb">
                            &nbsp;&nbsp;&nbsp;Available
                        </label>
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-semibold" for="ideal">Ideal For:</label>
                        <input type="text" id="ideal" name="ideal" class="form-control" value="<?=$idealFor?>">
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-semibold" for="property">Property Type:</label>
                        <select id="property" name="property" class="form-select">
                            <option value="Villa" <?= $propertyType === 'Villa' ? 'selected' : '' ?>>Villa</option>
                            <option value="Hostel" <?= $propertyType === 'Hostel' ? 'selected' : '' ?>>Hostel</option>
                            <option value="Guesthouse" <?= $propertyType === 'Guesthouse' ? 'selected' : '' ?>>Guesthouse</option>
                            <option value="Boutique Hotel" <?= $propertyType === 'Boutique Hotel' ? 'selected' : '' ?>>>Boutique Hotel</option>
                            <option value="Retreat" <?= $propertyType === 'Retreat' ? 'selected' : '' ?>>Retreat</option>
                        </select>                    
                    </div>
                    <div class="form-check d-flex justify-content-center mt-4">
                            <input class="form-check-input" name="bnb" type="checkbox" value="1" id="bnb"
                                <?= ($accommodation['bed_and_breakfast'] ?? 0) == 1 ? 'checked' : '' ?>>
                            <label class="form-check-label" for="bnb">
                            &nbsp;&nbsp;&nbsp;Bed & Breakfast
                        </label>
                    </div>
                    <button type="submit"class="btn btn-onboarding w-100" name="save_accommodation">
                        <?= $id ? 'Save Changes' : 'Add Accommodation' ?>
                    </button>                
            </form>
            </div>
        </div>
    </section>
</body>
</html>