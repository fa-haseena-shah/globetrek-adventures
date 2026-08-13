<?php
    session_start();
    include "db_helper.php";

    if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
        header("Location: view_pkg.php");
        exit();
    }

    $id = $_GET['id'] ?? null;
    if(!$id) {
        header("Location: view_pkg.php");
        exit();
    }

    $errors = [];
    $success = "";

    // for dropdowns
    $activities = fetchAll($conn, "SELECT activity_id, activity_name AS activity_name FROM activities ORDER BY activity_name ASC");
    $destinations = fetchAll($conn, "SELECT destination_id, name AS destination_name FROM destinations ORDER BY name ASC");
    // for display
    $package = fetchOne($conn, "SELECT pkg_name, duration_days FROM tour_packages WHERE pkg_id = ?", "i", [$id]);

    if(!$package) {
        header("Location: view_pkg.php");
        exit();
    }

    // store as separate variable to easily use in loop
    $duration = $package['duration_days'];

    // fetch existing itinerary for pre-selection
    $pkgActivities = fetchAll($conn, "
    SELECT * FROM package_activities WHERE pkg_id = ?
    ORDER BY day_number, day_order
    ", "i", [$id]);

    $pkgDestinations = fetchAll($conn, "
    SELECT * FROM package_destinations WHERE pkg_id = ?
    ORDER BY day_number, day_order
    ", "i", [$id]);

    $existingActivities = [];
    $existingDestinations = [];

    // populating above arrays using fetched data to display as dropdowns
    foreach($pkgActivities as $pa) {
        $existingActivities[$pa['day_number']][$pa['day_order']] = $pa['activity_id'];
    }
    foreach($pkgDestinations as $pd) {
        $existingDestinations[$pd['day_number']][$pd['day_order']] = $pd['destination_id'];
    }

    if(isset($_POST['save'])) {
        $activityAssoc = $_POST['activity'] ?? [];
        $destinationAssoc = $_POST['destination'] ?? [];

        if(empty($activityAssoc) && empty($destinationAssoc)) {
            $errors[] = "You need to make at least one activity or destination selection.";
        }

        if(count($errors) === 0) {
            // delete existing then reinsert
            runQuery($conn, "DELETE FROM package_activities WHERE pkg_id = ?", "i", [$id]);
            runQuery($conn, "DELETE FROM package_destinations WHERE pkg_id = ?", "i", [$id]);
            // insert activities
            foreach($activityAssoc as $dayNumber => $actItems) {
                foreach($actItems as $dayOrder => $activityId) {
                    if(empty($activityId)) continue;
                    insertRow($conn, "
                    INSERT INTO package_activities(pkg_id, activity_id, day_number, day_order)
                    VALUES (?, ?, ?, ?)
                    ", "iiii", [$id, $activityId, $dayNumber, $dayOrder]);
                }
            }

            // insert destinations
            foreach($destinationAssoc as $dayNumber => $destItems) {
                foreach($destItems as $dayOrder => $destinationId) {
                    if(empty($destinationId)) continue;
                    insertRow($conn, "
                    INSERT INTO package_destinations(pkg_id, destination_id, day_number, day_order)
                    VALUES (?, ?, ?, ?)
                    ", "iiii", [$id, $destinationId, $dayNumber, $dayOrder]);
                }
            }

            // refresh existing for display
            $pkgActivities = fetchAll($conn, "SELECT * FROM package_activities WHERE pkg_id = ? ORDER BY day_number, day_order", "i", [$id]);
            $pkgDestinations = fetchAll($conn, "SELECT * FROM package_destinations WHERE pkg_id = ? ORDER BY day_number, day_order", "i", [$id]);

            $existingActivities = [];
            $existingDestinations = [];
            foreach($pkgActivities as $pa) $existingActivities[$pa['day_number']][$pa['day_order']] = $pa['activity_id'];
            foreach($pkgDestinations as $pd) $existingDestinations[$pd['day_number']][$pd['day_order']] = $pd['destination_id'];
            $success = "Itinerary saved successfully!";
        }
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Itinerary</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include "staff_navbar.php";?>
    <section class="section-header mt-5 mb-2 text-center">
        <h4><small>Managing Package</small> <?=$package['pkg_name']?>'s <small>Itinerary</small></h4>
        <p class="text-muted small">Insert activities and destinations in order of occurence</p>
    </section>
    <?php if(!empty($success)): ?>
        <div class="alert alert-success"><?= $success ?></div>
    <?php endif; ?>
    <?php if(!empty($errors)): ?>
        <?php foreach($errors as $error): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endforeach; ?>
    <?php endif; ?>
    
    <section class="container">
        <form action="manage_itinerary.php?id=<?= $id ?>" method="post">
            <?php for($day = 1; $day <= $duration; $day++): ?>
                <h5 class="text-decoration-underline m-4">Day <?=$day?></h5>
                <div class="row row-cols-1 row-cols-md-3 g-3 mb-3">
                    <!-- activities selection (max 3 per day)-->
                    <?php for($j = 1; $j <= 3; $j++): ?>
                            <div class="col">
                                <div class="card h-80 p-3">
                                    <label class="form-label">Activity #<?= $j ?></label>
                                    <select name="activity[<?= $day ?>][<?= $j ?>]" class="form-select">
                                        <option value="">No Selection...</option>
                                        <?php foreach($activities as $activity): ?>
                                            <option value="<?= $activity['activity_id'] ?>"
                                                <?= ($existingActivities[$day][$j] ?? '') == $activity['activity_id'] ? 'selected' : '' ?>>
                                                <?= $activity['activity_name'] ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                    <?php endfor; ?>

                    <!-- destinations selection (max 2 per day)-->
                    <?php for($k = 1; $k <= 2; $k++): ?>
                            <div class="col">
                                <div class="card h-80 p-3">
                                    <label class="form-label">Destination #<?= $k ?></label>
                                    <select name="destination[<?= $day ?>][<?= $k ?>]" class="form-select">
                                        <option value="">No Selection...</option>
                                        <?php foreach($destinations as $dest): ?>
                                            <option value="<?= $dest['destination_id'] ?>"
                                                <?= ($existingDestinations[$day][$k] ?? '') == $dest['destination_id'] ? 'selected' : '' ?>>
                                                <?= $dest['destination_name'] ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                    <?php endfor; ?>
                </div>
                <hr>
            <?php endfor;?>
            <button type="submit" name="save" class="btn btn-onboarding w-100 mt-5 mb-5">Save</button>
        </form>
    </section>
</body>
</html>