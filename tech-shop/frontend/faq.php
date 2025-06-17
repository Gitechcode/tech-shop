<?php
$pageTitle = "Frequently Asked Questions - TechShop";
include 'includes/header.php';
?>

<div class="faq-container">
    <div class="container py-5">
        <h1 class="text-center mb-5">Frequently Asked Questions</h1>
        
        <!-- Search Bar -->
        <div class="row justify-content-center mb-5">
            <div class="col-md-8">
                <div class="input-group">
                    <input type="text" id="faq-search" class="form-control" placeholder="Search for questions...">
                    <div class="input-group-append">
                        <button class="btn btn-primary" type="button">
                            <i class="fas fa-search"></i> Search
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- FAQ Categories -->
        <div class="faq-categories mb-4">
            <ul class="nav nav-pills justify-content-center">
                <li class="nav-item">
                    <a class="nav-link active" href="#all" data-category="all">All</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#orders" data-category="orders">Orders</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#shipping" data-category="shipping">Shipping</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#returns" data-category="returns">Returns</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#products" data-category="products">Products</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#account" data-category="account">Account</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#payment" data-category="payment">Payment</a>
                </li>
            </ul>
        </div>
        
        <!-- FAQ Accordion -->
        <div class="accordion" id="faqAccordion">
            <!-- Orders Section -->
            <div class="faq-section" data-category="orders">
                <h3 class="section-title">Orders</h3>
                
                <div class="card">
                    <div class="card-header" id="headingOne">
                        <h2 class="mb-0">
                            <button class="btn btn-link btn-block text-left" type="button" data-toggle="collapse" data-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                                How do I track my order?
                            </button>
                        </h2>
                    </div>
                    <div id="collapseOne" class="collapse" aria-labelledby="headingOne" data-parent="#faqAccordion">
                        <div class="card-body">
                            You can track your order by logging into your account and visiting the "Order History" section. 
                            There you'll find all your orders with their current status and tracking information. 
                            Alternatively, you can use the tracking number provided in your shipping confirmation email.
                        </div>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-header" id="headingTwo">
                        <h2 class="mb-0">
                            <button class="btn btn-link btn-block text-left collapsed" type="button" data-toggle="collapse" data-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                                Can I modify or cancel my order?
                            </button>
                        </h2>
                    </div>
                    <div id="collapseTwo" class="collapse" aria-labelledby="headingTwo" data-parent="#faqAccordion">
                        <div class="card-body">
                            Orders can be modified or canceled within 1 hour of placing them. To do this, log into your account, 
                            go to "Order History," find the relevant order, and click on "Modify" or "Cancel." If more than 1 hour 
                            has passed, please contact our customer service team as soon as possible, and we'll do our best to accommodate your request.
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Shipping Section -->
            <div class="faq-section" data-category="shipping">
                <h3 class="section-title">Shipping</h3>
                
                <div class="card">
                    <div class="card-header" id="headingThree">
                        <h2 class="mb-0">
                            <button class="btn btn-link btn-block text-left collapsed" type="button" data-toggle="collapse" data-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                                How long will it take to receive my order?
                            </button>
                        </h2>
                    </div>
                    <div id="collapseThree" class="collapse" aria-labelledby="headingThree" data-parent="#faqAccordion">
                        <div class="card-body">
                            Standard shipping typically takes 3-5 business days. Express shipping is 1-2 business days. 
                            International shipping can take 7-14 business days depending on the destination country. 
                            Please note that these are estimates and actual delivery times may vary based on location and customs processing.
                        </div>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-header" id="headingFour">
                        <h2 class="mb-0">
                            <button class="btn btn-link btn-block text-left collapsed" type="button" data-toggle="collapse" data-target="#collapseFour" aria-expanded="false" aria-controls="collapseFour">
                                Do you ship internationally?
                            </button>
                        </h2>
                    </div>
                    <div id="collapseFour" class="collapse" aria-labelledby="headingFour" data-parent="#faqAccordion">
                        <div class="card-body">
                            Yes, we ship to over 100 countries worldwide. International shipping rates and delivery times 
                            vary by location. You can see the shipping options available to your country during checkout. 
                            Please note that international orders may be subject to import duties and taxes, which are the 
                            responsibility of the customer.
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Returns Section -->
            <div class="faq-section" data-category="returns">
                <h3 class="section-title">Returns</h3>
                
                <div class="card">
                    <div class="card-header" id="headingFive">
                        <h2 class="mb-0">
                            <button class="btn btn-link btn-block text-left collapsed" type="button" data-toggle="collapse" data-target="#collapseFive" aria-expanded="false" aria-controls="collapseFive">
                                What is your return policy?
                            </button>
                        </h2>
                    </div>
                    <div id="collapseFive" class="collapse" aria-labelledby="headingFive" data-parent="#faqAccordion">
                        <div class="card-body">
                            We offer a 30-day return policy for most items. Products must be in their original condition 
                            with all packaging and accessories. Some items like software, customized products, and certain 
                            electronics may have different return policies. Please check the product page for specific details.
                        </div>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-header" id="headingSix">
                        <h2 class="mb-0">
                            <button class="btn btn-link btn-block text-left collapsed" type="button" data-toggle="collapse" data-target="#collapseSix" aria-expanded="false" aria-controls="collapseSix">
                                How do I initiate a return?
                            </button>
                        </h2>
                    </div>
                    <div id="collapseSix" class="collapse" aria-labelledby="headingSix" data-parent="#faqAccordion">
                        <div class="card-body">
                            To initiate a return, log into your account, go to "Order History," find the order containing 
                            the item you wish to return, and click "Return Items." Follow the instructions to complete the 
                            return process. You'll receive a return shipping label and instructions on how to package and 
                            send your return.
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Products Section -->
            <div class="faq-section" data-category="products">
                <h3 class="section-title">Products</h3>
                
                <div class="card">
                    <div class="card-header" id="headingSeven">
                        <h2 class="mb-0">
                            <button class="btn btn-link btn-block text-left collapsed" type="button" data-toggle="collapse" data-target="#collapseSeven" aria-expanded="false" aria-controls="collapseSeven">
                                Are your products covered by warranty?
                            </button>
                        </h2>
                    </div>
                    <div id="collapseSeven" class="collapse" aria-labelledby="headingSeven" data-parent="#faqAccordion">
                        <div class="card-body">
                            Most of our products come with a manufacturer's warranty. The warranty period varies by product 
                            and brand. You can find specific warranty information on each product page. Additionally, we offer 
                            extended warranty options on select items at checkout.
                        </div>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-header" id="headingEight">
                        <h2 class="mb-0">
                            <button class="btn btn-link btn-block text-left collapsed" type="button" data-toggle="collapse" data-target="#collapseEight" aria-expanded="false" aria-controls="collapseEight">
                                How do I know if a product is in stock?
                            </button>
                        </h2>
                    </div>
                    <div id="collapseEight" class="collapse" aria-labelledby="headingEight" data-parent="#faqAccordion">
                        <div class="card-body">
                            Product availability is displayed on each product page. If an item is in stock, you'll see "In Stock" 
                            with the quantity available. For out-of-stock items, you can sign up for notifications to be alerted 
                            when the product becomes available again.
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Account Section -->
            <div class="faq-section" data-category="account">
                <h3 class="section-title">Account</h3>
                
                <div class="card">
                    <div class="card-header" id="headingNine">
                        <h2 class="mb-0">
                            <button class="btn btn-link btn-block text-left collapsed" type="button" data-toggle="collapse" data-target="#collapseNine" aria-expanded="false" aria-controls="collapseNine">
                                How do I reset my password?
                            </button>
                        </h2>
                    </div>
                    <div id="collapseNine" class="collapse" aria-labelledby="headingNine" data-parent="#faqAccordion">
                        <div class="card-body">
                            To reset your password, click on "Login" at the top of the page, then select "Forgot Password." 
                            Enter the email address associated with your account, and we'll send you instructions to reset 
                            your password. If you don't receive the email within a few minutes, please check your spam folder.
                        </div>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-header" id="headingTen">
                        <h2 class="mb-0">
                            <button class="btn btn-link btn-block text-left collapsed" type="button" data-toggle="collapse" data-target="#collapseTen" aria-expanded="false" aria-controls="collapseTen">
                                Can I have multiple shipping addresses?
                            </button>
                        </h2>
                    </div>
                    <div id="collapseTen" class="collapse" aria-labelledby="headingTen" data-parent="#faqAccordion">
                        <div class="card-body">
                            Yes, you can save multiple shipping addresses in your account. To add or manage addresses, 
                            log into your account, go to "My Account," and select "Address Book." From there, you can add, 
                            edit, or delete shipping addresses. During checkout, you'll be able to select from your saved addresses.
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Payment Section -->
            <div class="faq-section" data-category="payment">
                <h3 class="section-title">Payment</h3>
                
                <div class="card">
                    <div class="card-header" id="headingEleven">
                        <h2 class="mb-0">
                            <button class="btn btn-link btn-block text-left collapsed" type="button" data-toggle="collapse" data-target="#collapseEleven" aria-expanded="false" aria-controls="collapseEleven">
                                What payment methods do you accept?
                            </button>
                        </h2>
                    </div>
                    <div id="collapseEleven" class="collapse" aria-labelledby="headingEleven" data-parent="#faqAccordion">
                        <div class="card-body">
                            We accept all major credit cards (Visa, MasterCard, American Express, Discover), PayPal, Apple Pay, 
                            Google Pay, and bank transfers. For certain regions, we also offer additional local payment methods. 
                            All payments are processed securely through our encrypted payment gateway.
                        </div>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-header" id="headingTwelve">
                        <h2 class="mb-0">
                            <button class="btn btn-link btn-block text-left collapsed" type="button" data-toggle="collapse" data-target="#collapseTwelve" aria-expanded="false" aria-controls="collapseTwelve">
                                Is it safe to use my credit card on your website?
                            </button>
                        </h2>
                    </div>
                    <div id="collapseTwelve" class="collapse" aria-labelledby="headingTwelve" data-parent="#faqAccordion">
                        <div class="card-body">
                            Yes, our website uses industry-standard SSL encryption to protect your personal and payment information. 
                            We are PCI DSS compliant and never store your full credit card details on our servers. Additionally, 
                            we implement multiple security measures to ensure your data remains safe and secure.
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Still Have Questions -->
        <div class="still-have-questions text-center mt-5 p-4 bg-light rounded">
            <h3>Still Have Questions?</h3>
            <p>Our customer support team is here to help you.</p>
            <a href="contact.php" class="btn btn-primary">Contact Us</a>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
