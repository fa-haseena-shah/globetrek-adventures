<?php if(session_status() === PHP_SESSION_NONE) {
    session_start();
    }
?>
<nav class="navbar navbar-expand-lg">
    <div class="nav-logo">
        <div class="nav-logo">
            <a href="index.php"><img src="assets/logo.png" alt="GlobeTrek Adventures Logo"></a>
        </div>
    </div>
    <div class="container-fluid">
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavDropdown" aria-controls="navbarNavDropdown" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNavDropdown">
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link active" aria-current="page" href="index.php">Home</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="packages.php">Tour Packages</a>
            </li>
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                Experiences</a>
            <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="destinations.php">Destinations</a></li>
                <li><a class="dropdown-item" href="activities.php">Activities</a></li>
            </ul>
            </li>
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                Stay & Services</a>
            <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="accommodation.php">Accommodation</a></li>
                <li><a class="dropdown-item" href="transport.php">Transport</a></li>
                <li><a class="dropdown-item" href="guides.php">Travel Guides</a></li>
            </ul>
            <li class="nav-item">
                <a class="nav-link" href="about.php">About Us</a>
            </li>
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                Queries & Contact</a>
            <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="faq.php">Frequently Asked Questions</a></li>
                <li><a class="dropdown-item" href="contact.php">Contact</a></li>
            </ul>
            </li>
        </ul>
    </div>
    <?php if(isset($_SESSION['user_id']) && ($_SESSION['role'] === 'Customer')): ?>
        <ul class="list-unstyled">
            <li class="nav-item mt-4 fw-bold">
                <a class="nav-link" href="traveller_dashboard.php">Travel Dashboard</a>
            </li>
        </ul>
       <a class="btn btn-secondary" href="logout.php" role="button">Log Out</a>
    <?php else: ?>
        <a class="btn btn-secondary" href="register.php" role="button">Register</a>
        <a class="btn btn-secondary" href="login.php" role="button">Login</a>
    <?php endif; ?>
  </div>
</nav>