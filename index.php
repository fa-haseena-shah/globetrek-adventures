<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GlobeTrek Adventures • Sri Lanka Travels</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <script defer src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include "navbar.php";?>
    <section class="hero">
        <div class="hero-content">
            <p class="hero-subtitle">Your Tropical Dream Awaits,</p>
            <h1 class="hero-title">SRI LANKA</h1>
            <div class="hero-buttons">
                <a class="btn btn-primary" href="packages.php" role="button">Browse Packages</a>
                <a class="btn btn-primary" href="login.php" role="button">Get Started!</a>
            </div>
        </div>
    </section>

   <section class="basic-wrapper">
        <div class="section-header">
            <p>Most travellers choose these packages!</p>
            <h2>POPULAR EXPERIENCES</h2>
        </div>
        <div class="grid-container">
            <div class="card">
                <div class="card-content">
                    <img src="assets/pkgs/hill_country_adventure.jpg" alt="">
                    <div class="pkg-header">
                        <p>Hill Country Adventure</p>
                        <p>LKR 86,000</p>
                        <p>5 DAYS</p>
                    </div>
                    <div class="pkg-info"></div>
                    <a href="package_details.php?id=1" class="btn btn-primary">View Details</a>
                </div>
            </div>

            <div class="card">
                <div class="card-content">
                    <img src="assets/pkgs/southern_coast.jpg" alt="">
                    <div class="pkg-header">
                        <p>Southern Coast Escape</p>
                        <p>LKR 89,000</p>
                        <p>5 DAYS</p>
                    </div>
                    <div class="pkg-info"></div>
                    <a href="package_details.php?id=4" class="btn btn-primary">View Details</a>
                </div>
            </div>

            <div class="card">
                <div class="card-content">
                    <img src="assets/pkgs/ceylon_luxury.jpg" alt="">
                    <div class="pkg-header">
                        <p>Ceylon Luxury Retreat</p>
                        <p>LKR 185,000</p>
                        <p>7 DAYS</p>
                    </div>
                    <div class="pkg-info"></div>
                    <a href="package_details.php?id=6" class="btn btn-primary">View Details</a>
                </div>
            </div>
        </div>
    </section>

    <section class="customize-wrapper mt-0">
        <div class="section-header">
            <p>We can also bring your dream itinerary to life!</p>
            <h2>CUSTOMIZED PACKAGES</h2>
        </div>
        <a href="customize_tour.php" class="btn btn-primary mt-0">Curate Your Trip</a>
    </section>

    <section class="gallery-container">
        <div class="gallery-box">
            <div class="gallery-col">
                <img src="assets/cards/kandy.jpg" alt="Gallery Img">
                <img src="assets/cards/udawalawe.jpg" alt="Gallery Img">
                <img src="assets/cards/surfing.jpg.png" alt="Gallery Img">
            </div>
            <div class="gallery-col">
                <img src="assets/cards/mirissa.png" alt="Gallery Img">
                <img src="assets/cards/arugambay.png" alt="Gallery Img">
                <a href="destinations.php">VIEW DESTINATIONS →</a>
            </div>
            <div class="gallery-col">
                <img src="assets/cards/galle.png" alt="Gallery Img">
                <img src="assets/cards/sigiriya.png" alt="Gallery Img">
                <img src="assets/cards/train_ride.png" alt="Gallery Img">
            </div>
        </div>
    </section>

    <section class="offerings">
        <div class="offerings-content">
            <div class="section-header">
                <p>Beyond Travel Packages</p>
                <h2>OUR OFFERINGS</h2>
            </div>
            <div class="list">
                <div class="list-item">
                    <a href="packages.php"><img src="assets/pkgs.png" alt=""></a>
                    <div class="list-content">
                        <h4><a href="packages.php">Travel Packages</a></h4>
                        <p>Leave the hassle of planning to us by booking one of our packages!</p>
                    </div>
                </div>

                <div class="list-item">
                    <a href="accommodation.php"><img src="assets/resort.png" alt=""></a>
                    <div class="list-content">
                        <h4><a href="accommodation.php">Accommodation</a></h4>
                        <p>Booking with us guarantees comfortable, warm and welcoming stays.</p>
                    </div>
                </div>

                <div class="list-item">
                    <a href="transport.php"><img src="assets/transport.png" alt=""></a>
                    <div class="list-content">
                        <h4><a href="transport.php">Transport</a></h4>
                        <p>Safe, comfortable and fun transport to and from anywhere when you book with us!</p>
                    </div>
                </div>

                <div class="list-item">
                    <a href="guides.php"><img src="assets/guides.png" alt=""></a>
                    <div class="list-content">
                        <h4><a href="guides.php">Travel Guides</a></h4>
                        <p>Guaranteeing a once-in-a-lifetime experience, wherever you choose to go</p>
                    </div>
                </div>

                <div class="list-item">
                    <a href="activities.php"><img src="assets/activities.png" alt=""></a>
                    <div class="list-content">
                        <h4><a href="activities.php">Activities</a></h4>
                        <p>Adventures, beach, culture or relaxation. Activities for everyone!</p>
                    </div>
                </div>

                <div class="list-item">
                    <a href="customize_form.php"><img src="assets/customize-pkg.png" alt=""></a>
                    <div class="list-content">
                        <h4><a href="customize_tour.php">Do It Your Way</a></h4>
                        <p>You might want to see places on your own accord which you can through our customizable tours</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <?php include "footer.php";?>
</body>
</html>