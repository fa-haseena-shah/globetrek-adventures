<?php
	if (session_status() === PHP_SESSION_NONE) {
			session_start();
	}
	$userName = isset($_SESSION['name']) ? $_SESSION['name'] : 'Staff';
	$userType = isset($_SESSION['role']) ? $_SESSION['role'] : null;

	if($userType === 'Customer') {
        header("Location: traveller_dashboard.php");
        exit();
    }
?>
<!doctype html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title>Staff Dashboard</title>
		<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
		<link rel="stylesheet" href="style.css">
		<script defer src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
	</head>
	<body>
        <?php include "staff_navbar.php";?>
		<main class="container">
			<section class="staff-dashboard-hero py-5">
				<div class="d-flex justify-content-between align-items-start">
					<div>
						<p class="greeting">Hi, <?=$userName ?>!</p>
						<p class="role"><?=$userType?> Account</p>
					</div>
					<div>
						<a href="logout.php" class="btn btn-primary">Logout</a>
					</div>
				</div>

				<?php if ($userType === 'Staff'): ?>
				<div class="dashboard-section">
					<h4>Manage</h4>
					<div class="row mt-4 g-3">
						<div class="col-md-6">
							<a href="manage_bookings.php" class="dashboard-btn btn btn-primary">Manage Bookings</a>
						</div>
						<div class="col-md-6">
							<a href="view_pkg.php" class="dashboard-btn btn btn-primary">Manage Packages</a>
						</div>
						<div class="col-md-6 mt-3">
							<a href="queries.php" class="dashboard-btn btn btn-primary">Respond Queries</a>
						</div>
					</div>
				</div>
				<?php endif; ?>
				<?php if ($userType === 'Admin'): ?>
					<div class="dashboard-section">
						<h4>Admin Dashboard</h4>
						<div class="row mt-4">
							<div class="col-md-6">
								<a href="manage_bookings.php" class="dashboard-btn btn btn-primary">View Bookings</a>
							</div>
							<div class="col-md-6">
								<a href="services.php" class="dashboard-btn btn btn-primary">Manage Service Information</a>
							</div>
							<div class="col-md-6 mt-3">
								<a href="accounts.php" class="dashboard-btn btn btn-primary">Create Staff/Admin Account</a>
							</div>
							<div class="col-md-6 mt-3">
								<a href="generate_report.php" class="dashboard-btn btn btn-primary">Sales & Customer Reports</a>
							</div>
						</div>
					</div>
				<?php endif; ?>
			</section>
		</main>
	</body>
</html>

