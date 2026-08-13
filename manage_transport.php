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
    $transport = null;

    if($id) {
        $transport = fetchOne($conn, "
        SELECT * FROM transport 
        WHERE transport_id = ?
        ", "i", [$id]);

        if(!$transport) {
            header("Location: services.php");
            exit();
        }
    }

    $name = $transport['name'] ?? '';
    $vehicleType = $transport['vehicle_type'] ?? '';
    $price = $transport['price_per_head'] ?? '';
    $capacity = $transport['capacity'] ?? '';
    $availability = $transport['availability'] ?? 1;

    if(isset($_POST['save_transport'])) {
        $name = $_POST['name'];
        $vehicleType = $_POST['vehicle_type'];
        $price = $_POST['price'];
        $capacity = $_POST['capacity'];
        $availability = isset($_POST['availability']) ? 1 : 0;

        if(empty($name) || empty($vehicleType) || empty($price) || empty($capacity)) {
            $errors[] = "Fields cannot be empty.";
        }

        if(count($errors) === 0) {
            if($id) {
                updateRow($conn, "
                UPDATE transport
                SET name = ?, vehicle_type = ?, price_per_head = ?, capacity = ?, availability = ?
                WHERE transport_id = ?
                ", "ssdiii", [$name, $vehicleType, $price, $capacity, $availability, $id]);

                $success   = "Transport updated successfully!";
                // refresh autofill data
                $transport = fetchOne($conn, "SELECT * FROM transport WHERE transport_id = ?", "i", [$id]);

                $name = $transport['name'];
                $vehicleType = $transport['vehicle_type'];
                $price = $transport['price_per_head'];
                $capacity = $transport['capacity'];
                $availability = $transport['availability'];
            } else {
                insertRow($conn, "
                INSERT INTO transport(name, vehicle_type, price_per_head, capacity, availability)
                VALUES (?, ?, ?, ?, ?)
                ", "ssdii", [$name, $vehicleType, $price, $capacity, $availability]);

                $success = "Transport added successfully!";
            }
        }
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add/Update Transport</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include "staff_navbar.php"; ?>
    <section class="section-header mt-5 mb-2 text-center">
        <h1><?= $id ? "Updating Transport #$id" : "Adding New Transport" ?></h1>
    </section>

    <section class="container mb-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <form action="manage_transport.php<?= $id ? '?id='.$id : '' ?>" method="post" class="manage-form">
                    <?php if(!empty($success)): ?>
                        <div class="alert alert-success"><?= $success ?></div>
                    <?php endif; ?>
                    <?php if(!empty($errors)): ?>
                        <?php foreach($errors as $error): ?>
                            <div class="alert alert-danger"><?= $error ?></div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    <div class="mb-4">
                        <label class="form-label fw-semibold">Name:</label>
                        <input type="text" name="name" class="form-control" value="<?= $name ?>">
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-semibold">Vehicle Type:</label>
                        <select name="vehicle_type" class="form-select">
                            <option value="Luxury Van" <?= $vehicleType === 'Luxury Van' ? 'selected' : '' ?>>Luxury Van</option>
                            <option value="Mini Van"<?= $vehicleType === 'Mini Van' ? 'selected' : '' ?>>Mini Van</option>
                            <option value="Luxury Sedan"<?= $vehicleType === 'Luxury Sedan' ? 'selected' : '' ?>>Luxury Sedan</option>
                            <option value="Jeep" <?= $vehicleType === 'Jeep' ? 'selected' : '' ?>>Jeep</option>
                            <option value="Bus" <?= $vehicleType === 'Bus' ? 'selected' : '' ?>>Bus</option>
                            <option value="TukTuk" <?= $vehicleType === 'TukTuk' ? 'selected' : '' ?>>TukTuk</option>
                        </select>
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-semibold">Price Per Day (LKR):</label>
                        <input type="number" name="price" class="form-control" value="<?= $price ?>" step="0.01">
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-semibold">Capacity (Passengers):</label>
                        <input type="number" name="capacity" class="form-control" value="<?= $capacity ?>" min="1">
                    </div>
                    <div class="form-check d-flex justify-content-center mt-4 mb-4">
                        <input class="form-check-input" name="availability" type="checkbox" value="1" id="availability"
                            <?= $availability == 1 ? 'checked' : '' ?>>
                        <label class="form-check-label" for="availability">
                            &nbsp;&nbsp;&nbsp;Available
                        </label>
                    </div>
                    <button type="submit" name="save_transport" class="btn btn-onboarding w-100">
                        <?= $id ? 'Save Changes' : 'Add Transport' ?>
                    </button>
                </form>
            </div>
        </div>
    </section>
</body>
</html>