<!-- Footer -->
    <footer class="bg-dark text-light py-5 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 mb-4">
                    <div class="d-flex align-items-center mb-3">
                        <div class="logo-icon me-2">
                            <i class="fas fa-laptop text-primary"></i>
                        </div>
                        <span class="fw-bold fs-4"><?php echo SITE_NAME; ?></span>
                    </div>
                    <p class="text-muted">
                        Your trusted partner for the latest technology and electronics. 
                        We provide quality products with excellent customer service.
                    </p>
                    <div class="social-links">
                        <a href="#" class="text-light me-3"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="text-light me-3"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="text-light me-3"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="text-light me-3"><i class="fab fa-linkedin-in"></i></a>
                        <a href="#" class="text-light"><i class="fab fa-youtube"></i></a>
                    </div>
                </div>

                <div class="col-lg-2 col-md-6 mb-4">
                    <h6 class="fw-bold mb-3">Quick Links</h6>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="<?php echo FRONTEND_URL; ?>" class="text-muted text-decoration-none">Home</a></li>
                        <li class="mb-2"><a href="<?php echo FRONTEND_URL; ?>/shop.php" class="text-muted text-decoration-none">Shop</a></li>
                        <li class="mb-2"><a href="<?php echo FRONTEND_URL; ?>/about.php" class="text-muted text-decoration-none">About Us</a></li>
                        <li class="mb-2"><a href="<?php echo FRONTEND_URL; ?>/contact.php" class="text-muted text-decoration-none">Contact</a></li>
                        <li class="mb-2"><a href="<?php echo FRONTEND_URL; ?>/blog.php" class="text-muted text-decoration-none">Blog</a></li>
                    </ul>
                </div>

                <div class="col-lg-2 col-md-6 mb-4">
                    <h6 class="fw-bold mb-3">Categories</h6>
                    <ul class="list-unstyled">
                        <?php
                        $stmt = $pdo->query("SELECT * FROM categories WHERE status = 'active' ORDER BY name LIMIT 5");
                        while ($category = $stmt->fetch()) {
                            echo "<li class='mb-2'><a href='" . FRONTEND_URL . "/shop.php?category=" . $category['id'] . "' class='text-muted text-decoration-none'>" . htmlspecialchars($category['name']) . "</a></li>";
                        }
                        ?>
                    </ul>
                </div>

                <div class="col-lg-2 col-md-6 mb-4">
                    <h6 class="fw-bold mb-3">Customer Service</h6>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="#" class="text-muted text-decoration-none">Help Center</a></li>
                        <li class="mb-2"><a href="#" class="text-muted text-decoration-none">Shipping Info</a></li>
                        <li class="mb-2"><a href="#" class="text-muted text-decoration-none">Returns</a></li>
                        <li class="mb-2"><a href="#" class="text-muted text-decoration-none">Track Order</a></li>
                        <li class="mb-2"><a href="#" class="text-muted text-decoration-none">Size Guide</a></li>
                    </ul>
                </div>

                <div class="col-lg-2 col-md-6 mb-4">
                    <h6 class="fw-bold mb-3">Legal</h6>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="#" class="text-muted text-decoration-none">Privacy Policy</a></li>
                        <li class="mb-2"><a href="#" class="text-muted text-decoration-none">Terms of Service</a></li>
                        <li class="mb-2"><a href="#" class="text-muted text-decoration-none">Cookie Policy</a></li>
                        <li class="mb-2"><a href="#" class="text-muted text-decoration-none">Refund Policy</a></li>
                    </ul>
                </div>
            </div>

            <hr class="my-4">

            <div class="row align-items-center">
                <div class="col-md-6">
                    <p class="mb-0 text-muted">
                        &copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. All rights reserved.
                    </p>
                </div>
                <div class="col-md-6 text-md-end">
                    <div class="payment-methods">
                        <i class="fab fa-cc-visa text-muted me-2"></i>
                        <i class="fab fa-cc-mastercard text-muted me-2"></i>
                        <i class="fab fa-cc-amex text-muted me-2"></i>
                        <i class="fab fa-cc-paypal text-muted me-2"></i>
                        <i class="fab fa-cc-stripe text-muted"></i>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <!-- Back to Top Button -->
    <button class="btn btn-primary btn-floating" id="backToTop" style="display: none;">
        <i class="fas fa-arrow-up"></i>
    </button>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Swiper JS -->
    <script src="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.js"></script>
    <!-- Custom JS -->
    <script src="<?php echo FRONTEND_URL; ?>/assets/js/main.js"></script>

    <script>
    // Back to top button
    window.addEventListener('scroll', function() {
        const backToTop = document.getElementById('backToTop');
        if (window.pageYOffset > 300) {
            backToTop.style.display = 'block';
        } else {
            backToTop.style.display = 'none';
        }
    });

    document.getElementById('backToTop').addEventListener('click', function() {
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });
    </script>

    <style>
    .btn-floating {
        position: fixed;
        bottom: 20px;
        right: 20px;
        width: 50px;
        height: 50px;
        border-radius: 50%;
        z-index: 1000;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }

    .social-links a:hover {
        color: var(--bs-primary) !important;
        transform: translateY(-2px);
        transition: all 0.3s ease;
    }

    .payment-methods i {
        font-size: 1.5rem;
        transition: color 0.3s ease;
    }

    .payment-methods i:hover {
        color: var(--bs-primary) !important;
    }
    </style>
</body>
</html>
