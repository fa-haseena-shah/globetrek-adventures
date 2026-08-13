<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$userType = isset($userType) ? $userType : (isset($_SESSION['role']) ? $_SESSION['role'] : null);
?>
<nav class="navbar navbar-expand-lg staff-nav">
    <div class="nav-logo">
        <div class="nav-logo">
            <a href="dashboard.php"><img src="assets/logo.png" alt="GlobeTrek Adventures Logo"></a>
        </div>
    </div>
    <div class="container-fluid">
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavDropdown" aria-controls="navbarNavDropdown" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNavDropdown">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link active" aria-current="page" href="dashboard.php">Home</a>
                </li>
                <?php if ($userType === 'Admin'): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="manageDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Manage
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="manageDropdown">
                            <li><a class="dropdown-item" href="manage_bookings.php">Bookings</a></li>
                            <li><a class="dropdown-item" href="services.php">Service Information</a></li>
                            <li><a class="dropdown-item" href="accounts.php">Staff/Admin Accounts</a></li>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" aria-current="page" href="generate_report.php">Generate Report</a>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link active" aria-current="page" href="manage_bookings.php">Manage Bookings</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" aria-current="page" href="view_pkg.php">Manage Tour Packages</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" aria-current="page" href="queries.php">Respond Queries</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>