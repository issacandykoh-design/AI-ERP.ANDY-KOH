<!-- MERCHANT STOREFRONT PROTOTYPE - HARDCODED DATA -->
<div class="content-wrapper">
    
    <!-- Merchant Header -->
    <div class="bg-white rounded b-shadow-4 mb-4">
        <div class="p-4">
            <div class="row align-items-center">
                <div class="col-lg-2 text-center">
                    <img src="https://via.placeholder.com/150x150?text=Logo" 
                         class="rounded-circle img-thumbnail" 
                         style="width: 120px; height: 120px;"
                         alt="Merchant Logo">
                </div>
                <div class="col-lg-7">
                    <h1 class="f-28 font-weight-bold text-darkest-grey mb-2">
                        Fresh Farm Co.
                        <span class="badge badge-success">Verified</span>
                        <span class="badge badge-info">Premium</span>
                    </h1>
                    <p class="text-dark-grey mb-2">
                        <i class="fa fa-map-marker-alt"></i> Singapore | 
                        <i class="fa fa-calendar"></i> Member since 2020
                    </p>
                    <div class="mb-2">
                        <div class="text-warning d-inline-block mr-3">
                            <i class="fa fa-star"></i>
                            <i class="fa fa-star"></i>
                            <i class="fa fa-star"></i>
                            <i class="fa fa-star"></i>
                            <i class="fa fa-star-half-alt"></i>
                            <span class="text-dark-grey ml-1">4.8</span>
                        </div>
                        <span class="text-dark-grey mr-3">1,234 reviews</span>
                        <span class="text-dark-grey mr-3">1,234 products</span>
                        <span class="text-dark-grey">5,678 orders</span>
                    </div>
                    <div class="mb-2">
                        <span class="badge badge-success mr-2">Organic Certified</span>
                        <span class="badge badge-primary mr-2">Halal</span>
                        <span class="badge badge-info mr-2">Premium Supplier</span>
                    </div>
                </div>
                <div class="col-lg-3 text-right">
                    <button class="btn btn-primary btn-lg mb-2">
                        <i class="fa fa-store"></i> Follow Store
                    </button>
                    <br>
                    <button class="btn btn-outline-primary btn-sm">
                        <i class="fa fa-envelope"></i> Contact Supplier
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Merchant Info Tabs -->
    <div class="bg-white rounded b-shadow-4 mb-4">
        <ul class="nav nav-tabs" id="merchantTabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" id="products-tab" data-toggle="tab" href="#products" role="tab">
                    Products (1,234)
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="about-tab" data-toggle="tab" href="#about" role="tab">
                    About
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="reviews-tab" data-toggle="tab" href="#reviews" role="tab">
                    Reviews (1,234)
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="policies-tab" data-toggle="tab" href="#policies" role="tab">
                    Policies
                </a>
            </li>
        </ul>

        <div class="tab-content p-4" id="merchantTabsContent">
            <!-- Products Tab -->
            <div class="tab-pane fade show active" id="products" role="tabpanel">
                <!-- Filters -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <select class="form-control">
                            <option>All Categories</option>
                            <option>Fresh Produce</option>
                            <option>Vegetables</option>
                            <option>Fruits</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <select class="form-control">
                            <option>Sort by: Price Low to High</option>
                            <option>Price High to Low</option>
                            <option>Rating</option>
                            <option>Newest</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <input type="text" class="form-control" placeholder="Search products...">
                    </div>
                </div>

                <!-- Product Grid -->
                <div class="row">
                    <!-- Product cards (similar to listing page) -->
                    <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                        <div class="card border-0 product-card">
                            <img src="https://via.placeholder.com/300x300?text=Product" class="card-img-top" alt="Product">
                            <div class="card-body">
                                <h5 class="card-title f-16">Premium Organic Tomatoes</h5>
                                <p class="text-darkest-grey font-weight-bold mb-0">$12.50</p>
                                <button class="btn btn-primary btn-sm mt-2 w-100">Add to Cart</button>
                            </div>
                        </div>
                    </div>
                    <!-- More products... -->
                </div>
            </div>

            <!-- About Tab -->
            <div class="tab-pane fade" id="about" role="tabpanel">
                <h4 class="mb-3">About Fresh Farm Co.</h4>
                <p class="text-dark-grey">
                    Fresh Farm Co. is a leading supplier of organic fresh produce in Singapore. We source directly from 
                    certified organic farms and deliver the freshest products to restaurants, cafes, and food businesses.
                </p>
                
                <h5 class="mt-4 mb-2">Our Story</h5>
                <p class="text-dark-grey">
                    Founded in 2020, Fresh Farm Co. started with a mission to provide high-quality organic produce 
                    to the F&B industry. Today, we serve over 500 businesses across Singapore.
                </p>

                <h5 class="mt-4 mb-2">Certifications</h5>
                <div class="mb-3">
                    <span class="badge badge-success p-2 mr-2">Organic Certified</span>
                    <span class="badge badge-primary p-2 mr-2">Halal</span>
                    <span class="badge badge-info p-2">ISO 22000</span>
                </div>

                <h5 class="mt-4 mb-2">Contact Information</h5>
                <p class="text-dark-grey mb-1">
                    <i class="fa fa-phone"></i> +65 1234 5678<br>
                    <i class="fa fa-envelope"></i> contact@freshfarmco.com<br>
                    <i class="fa fa-map-marker-alt"></i> 123 Farm Road, Singapore 123456
                </p>
            </div>

            <!-- Reviews Tab -->
            <div class="tab-pane fade" id="reviews" role="tabpanel">
                <h4 class="mb-3">Merchant Reviews</h4>
                <!-- Review list (similar to product reviews) -->
            </div>

            <!-- Policies Tab -->
            <div class="tab-pane fade" id="policies" role="tabpanel">
                <h4 class="mb-3">Store Policies</h4>
                
                <h5 class="mt-4 mb-2">Return Policy</h5>
                <p class="text-dark-grey">
                    Returns accepted within 7 days of delivery. Products must be in original condition.
                </p>

                <h5 class="mt-4 mb-2">Shipping Policy</h5>
                <p class="text-dark-grey">
                    Standard delivery: 3-5 business days ($5.00)<br>
                    Express delivery: 1-2 business days ($12.00)<br>
                    Free delivery on orders over $50
                </p>

                <h5 class="mt-4 mb-2">Payment Terms</h5>
                <p class="text-dark-grey">
                    Credit Card, PayNow, DBS PayLah!, GrabPay<br>
                    Net 30 available for approved accounts
                </p>
            </div>
        </div>
    </div>
</div>

