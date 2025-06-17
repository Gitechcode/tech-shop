<?php
require_once '../config/config.php';
require_once 'includes/header.php';
?>

<main class="about-page container py-5">
    <div class="row mb-5">
        <div class="col-12 text-center">
            <h1 class="display-4 fw-bold">About TechShop</h1>
            <p class="lead text-muted">Your trusted destination for quality tech products since 2010</p>
        </div>
    </div>

    <!-- Company Story Section -->
    <section class="row mb-5 align-items-center">
        <div class="col-md-6">
            <h2>Our Story</h2>
            <p>TechShop was founded in 2010 with a simple mission: to provide high-quality tech products at affordable prices with exceptional customer service.</p>
            <p>What started as a small online store has grown into one of the most trusted e-commerce platforms for technology enthusiasts, professionals, and everyday consumers alike.</p>
            <p>Over the years, we've expanded our product range, improved our services, and built a community of loyal customers who share our passion for technology.</p>
        </div>
        <div class="col-md-6">
            <img src="assets/images/about/company-history.jpg" alt="TechShop History" class="img-fluid rounded shadow">
        </div>
    </section>

    <!-- Mission & Vision Section -->
    <section class="row mb-5 bg-light p-4 rounded">
        <div class="col-md-6 mb-4 mb-md-0">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body">
                    <h3 class="card-title">Our Mission</h3>
                    <p class="card-text">To provide our customers with the latest technology products, exceptional service, and expert advice, making technology accessible to everyone.</p>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body">
                    <h3 class="card-title">Our Vision</h3>
                    <p class="card-text">To become the world's most customer-centric technology retailer, where customers can find and discover anything they might want to buy online.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Team Section -->
    <section class="mb-5">
        <h2 class="text-center mb-4">Meet Our Team</h2>
        <div class="row">
            <div class="col-md-3 col-sm-6 mb-4">
                <div class="card team-card border-0 shadow-sm">
                    <img src="assets/images/about/team-1.jpg" class="card-img-top" alt="CEO">
                    <div class="card-body text-center">
                        <h5 class="card-title mb-1">John Smith</h5>
                        <p class="card-text text-muted">CEO & Founder</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-4">
                <div class="card team-card border-0 shadow-sm">
                    <img src="assets/images/about/team-2.jpg" class="card-img-top" alt="CTO">
                    <div class="card-body text-center">
                        <h5 class="card-title mb-1">Sarah Johnson</h5>
                        <p class="card-text text-muted">CTO</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-4">
                <div class="card team-card border-0 shadow-sm">
                    <img src="assets/images/about/team-3.jpg" class="card-img-top" alt="Marketing Director">
                    <div class="card-body text-center">
                        <h5 class="card-title mb-1">Michael Chen</h5>
                        <p class="card-text text-muted">Marketing Director</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-4">
                <div class="card team-card border-0 shadow-sm">
                    <img src="assets/images/about/team-4.jpg" class="card-img-top" alt="Customer Service Manager">
                    <div class="card-body text-center">
                        <h5 class="card-title mb-1">Emily Rodriguez</h5>
                        <p class="card-text text-muted">Customer Service Manager</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Values Section -->
    <section class="mb-5">
        <h2 class="text-center mb-4">Our Core Values</h2>
        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body text-center">
                        <div class="value-icon mb-3">
                            <i class="fas fa-award fa-3x text-primary"></i>
                        </div>
                        <h4 class="card-title">Quality</h4>
                        <p class="card-text">We are committed to offering only the highest quality products that meet our strict standards.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body text-center">
                        <div class="value-icon mb-3">
                            <i class="fas fa-users fa-3x text-primary"></i>
                        </div>
                        <h4 class="card-title">Customer Focus</h4>
                        <p class="card-text">Our customers are at the heart of everything we do. We strive to exceed expectations.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body text-center">
                        <div class="value-icon mb-3">
                            <i class="fas fa-lightbulb fa-3x text-primary"></i>
                        </div>
                        <h4 class="card-title">Innovation</h4>
                        <p class="card-text">We embrace new technologies and continuously improve our products and services.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section class="mb-5">
        <h2 class="text-center mb-4">What Our Customers Say</h2>
        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body">
                        <div class="mb-3">
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                        </div>
                        <p class="card-text">"TechShop has been my go-to for all my tech needs. Their customer service is exceptional, and the products are always as described."</p>
                        <p class="font-weight-bold mb-0">- Robert K.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body">
                        <div class="mb-3">
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                        </div>
                        <p class="card-text">"I've been shopping at TechShop for years. Their prices are competitive, and shipping is always fast and reliable."</p>
                        <p class="font-weight-bold mb-0">- Amanda T.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body">
                        <div class="mb-3">
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star-half-alt text-warning"></i>
                        </div>
                        <p class="card-text">"The technical support team at TechShop is knowledgeable and patient. They helped me choose the perfect laptop for my needs."</p>
                        <p class="font-weight-bold mb-0">- David M.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact CTA -->
    <section class="text-center py-5 bg-light rounded">
        <h2>Have Questions?</h2>
        <p class="lead mb-4">We'd love to hear from you. Our team is always ready to help.</p>
        <a href="contact.php" class="btn btn-primary btn-lg">Contact Us</a>
    </section>
</main>

<?php require_once 'includes/footer.php'; ?>
