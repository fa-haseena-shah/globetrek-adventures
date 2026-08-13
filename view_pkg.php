<?php session_start();
    include "db_helper.php";
    $searchQuery  = $_GET['query'] ?? '';
    $searchType   = $_GET['type'] ?? 'tour';
    $keyword      = !empty($searchQuery) ? '%' . $searchQuery . '%' : '%';

    if($_SESSION['role'] === 'Customer') {
        header("Location: traveller_dashboard.php");
        exit();
    }

    $packages = fetchAll($conn, "
    SELECT tour_packages.*, category.category_id, category.name AS category_name

    FROM tour_packages
    JOIN category ON tour_packages.category_id = category.category_id
    WHERE tour_packages.pkg_name LIKE ?
    OR category.name LIKE ?
    ORDER BY tour_packages.pkg_id ASC
    ", "ss", [$keyword, $keyword]);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View All Packages</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include "staff_navbar.php";?>
    <section class="section-header mt-5 mb-2">
        <h2>GlobeTrek Tour Packages</h2>
    </section>

    <section class="search-bar bg-transparent py-4">
        <div class="container">
            <form action="view_pkg.php" method="get" class="align-items-center">
                <div class="col-md-6 col-sm-12 m-2">
                    <input type="text" name="query" class="form-control" placeholder="Search by package or category..." value="<?= htmlspecialchars($searchQuery) ?>">
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-primary">SEARCH</button>
                </div>
            </form>
        </div>
    </section>

    <!-- pkg info -->
    <div class="table-responsive">
        <table class="table">
            <thead class="custom-table-header">
                <tr>
                    <th scope="col">Package Name</th>
                    <th scope="col">Category</th>
                    <th scope="col">Description</th>
                    <th scope="col">Duration (Days)</th>
                    <th scope="col">Price Per Head (LKR)</th>
                    <th scope="col">Availability</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($packages as $package):?>
                <tr>
                    <td><?=$package['pkg_name']?></td>
                    <td><?=$package['category_name']?></td>
                    <td><?=$package['description']?></td>
                    <td><?=$package['duration_days']?></td>
                    <td><?=$package['price_per_head']?></td>
                    <td><?=($package['availability'] == 1) ? 'Yes' : 'No' ?></td>
                    <td>
                        <a href="manage_pkg.php?id=<?= $package['pkg_id'] ?>" class="action-link">Update</a>
                    </td>
                </tr>
                <?php endforeach;?>                    
            </tbody>
        </table>
    </div>
</body>
</html>