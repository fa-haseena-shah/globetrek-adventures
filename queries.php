<?php session_start();
    include "db_helper.php";
    // protect this page from customer accessing it
    if(!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['Staff', 'Admin'])) {
        header("Location: login.php");
        exit();
    }

    $queries = fetchAll($conn, "
    SELECT * FROM customer_query");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Queries</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="style.css">
    <script defer src="https://kit.fontawesome.com/e5a3f33c9d.js" crossorigin="anonymous"></script>
</head>
<body>
    <?php include "staff_navbar.php";?>
    <section class="section-header mt-5 mb-2">
        <h2>All Customer Queries</h2>
    </section>

    <section class="table-responsive">
        <table class="table table-striped table-bordered mb-0">
            <thead class="custom-table-header">
                <tr>
                    <th>Query ID</th>
                    <th>Customer Name</th>
                    <th>Customer Email</th>
                    <th>Query</th>
                    <th>Status</th>
                    <th>Response</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($queries as $query):?>
                    <tr>
                        <td><?=$query['query_id']?></td>
                        <td><?=$query['name']?></td>
                        <td><?=$query['email']?></td>
                        <td><?=$query['message']?></td>
                        <td><?=$query['status']?></td>
                        <td><?=$query['response']?></td>
                        <?php if($query['status'] === 'Open'): ?>
                            <td class="align-middle text-center">
                                <a href="respond_queries.php?id=<?= $query['query_id'] ?>" class="action-link">Respond</a>
                            </td>
                        <?php else: ?>
                            <td></td>
                        <?php endif;?>
                    </tr>
                <?php endforeach;?>
            </tbody>
        </table>
    </section>
</body>
</html>