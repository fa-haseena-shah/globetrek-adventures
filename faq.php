<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Frequently Asked Questions</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <script defer src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include "navbar.php";?>
    <section class="overflow-hidden">
        <section class="overflow-hidden faq-header">
            <div class="section-header m-5">
                <h2>Frequently Asked Questions</h2>
            </div>
        </section>
        <div class="container-fluid p-0">
            <div class="row gx-0 align-items-center">
                <div class="col-lg-6 package-hero-image m-5" style="width: 500px; height:auto;">
                    <img src="assets/cards/galle.png" alt="">
                </div>

                <div class="col-lg-6 d-flex align-items-center p-4 p-md-3">
                    <div class="accordion" id="accordionExample">
                        <div class="accordion-item faq-accordion">
                            <h2 class="accordion-header">
                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                                1. Do I need a visa to visit Sri Lanka?
                            </button>
                            </h2>
                            <div id="collapseOne" class="accordion-collapse collapse show">
                            <div class="accordion-body">
                                Yes, all visitors except Maldivian or Singaporean passport holders need a visa. The easiest way is to apply online in advance at the official site <a href="www.eta.gov.lk">www.eta.gov.lk</a>
                            </div>
                            </div>
                        </div>
                        <div class="accordion-item faq-accordion">
                            <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                                2. What is the best time to visit Sri Lanka?
                            </button>
                            </h2>
                            <div id="collapseTwo" class="accordion-collapse collapse">
                            <div class="accordion-body">
                                Sri Lanka is a year-round destination. The best time depends on the region you plan to visit, as weather patterns vary across the island.
                            </div>
                            </div>
                        </div>
                        <div class="accordion-item faq-accordion">
                            <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                                3. Can I customize my tour itinerary?
                            </button>
                            </h2>
                            <div id="collapseThree" class="accordion-collapse collapse">
                            <div class="accordion-body">
                                We offer customized tour packages tailored to your budget, interests, preferred destinations, and accommodation preferences. Simply fill out <a href="customize_tour.php">this</a> form.
                            </div>
                            </div>
                        </div>
                        <div class="accordion-item faq-accordion">
                            <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFour" aria-expanded="false" aria-controls="collapseFour">
                                4. Is transportation included in tour packages?
                            </button>
                            </h2>
                            <div id="collapseFour" class="accordion-collapse collapse">
                            <div class="accordion-body">
                                Transportation is included in our tour packages. Standalone transport services are also available for booking.                            
                            </div>
                            </div>
                        </div>
                        <div class="accordion-item faq-accordion">
                            <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFive" aria-expanded="false" aria-controls="collapseFive">
                                5. What payment methods do you accept?
                            </button>
                            </h2>
                            <div id="collapseFive" class="accordion-collapse collapse">
                            <div class="accordion-body">
                                We accept online card payments through our secure payment portal.                            
                            </div>
                        </div>
                        <div class="accordion-item faq-accordion">
                            <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFive" aria-expanded="false" aria-controls="collapseFive">
                                6. What should I pack?
                            </button>
                            </h2>
                            <div id="collapseFive" class="accordion-collapse collapse">
                            <div class="accordion-body">
                                Sri Lanka’s tropical climate makes cotton clothes ideal, along with swimwear for beaches. Casual, modest attire is suitable for most places. 
                            </div>
                        </div>
                        </div>
                </div>
            </div>
        </div>
    </section>

    <section class="customize-wrapper mt-0">
        <div class="section-header">
            <p>Have any unanswered questions?</p>
            <h2>REACH OUT TO US</h2>
        </div>
        <a href="contact.php" class="btn btn-primary mt-0">Contact Us</a>
    </section>
    <?php include "footer.php";?>
</body>
</html>