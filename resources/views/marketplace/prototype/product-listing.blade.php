<!-- PRODUCT LISTING PAGE PROTOTYPE - HARDCODED DATA -->
<div class="content-wrapper">
    
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="f-28 font-weight-bold text-darkest-grey mb-2">Fresh Produce</h1>
            <p class="text-dark-grey mb-0">Showing 1,234 products</p>
        </div>
        <div>
            <span class="badge badge-primary p-2 mr-2">
                <i class="fa fa-shopping-cart"></i> Cart (<span id="cart-count">3</span>)
            </span>
        </div>
    </div>

    <div class="row">
        <!-- Sidebar Filters -->
        <div class="col-lg-3">
            <div class="bg-white rounded b-shadow-4 p-4 mb-4">
                <h5 class="f-16 font-weight-bold mb-3">Filters</h5>
                
                <!-- Price Range -->
                <div class="mb-4">
                    <label class="f-14 font-weight-bold mb-2">Price Range</label>
                    <div class="d-flex justify-content-between mb-2">
                        <input type="number" class="form-control form-control-sm" placeholder="Min" value="0">
                        <span class="px-2">-</span>
                        <input type="number" class="form-control form-control-sm" placeholder="Max" value="1000">
                    </div>
                </div>

                <!-- Category -->
                <div class="mb-4">
                    <label class="f-14 font-weight-bold mb-2">Category</label>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="cat1" checked>
                        <label class="form-check-label" for="cat1">Vegetables (456)</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="cat2">
                        <label class="form-check-label" for="cat2">Fruits (389)</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="cat3">
                        <label class="form-check-label" for="cat3">Herbs (189)</label>
                    </div>
                </div>

                <!-- Merchant -->
                <div class="mb-4">
                    <label class="f-14 font-weight-bold mb-2">Merchant</label>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="merchant1">
                        <label class="form-check-label" for="merchant1">
                            Fresh Farm Co. <span class="badge badge-success">Verified</span>
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="merchant2">
                        <label class="form-check-label" for="merchant2">Green Valley</label>
                    </div>
                </div>

                <!-- Certifications -->
                <div class="mb-4">
                    <label class="f-14 font-weight-bold mb-2">Certifications</label>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="cert1">
                        <label class="form-check-label" for="cert1">Organic</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="cert2">
                        <label class="form-check-label" for="cert2">Halal</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="cert3">
                        <label class="form-check-label" for="cert3">Non-GMO</label>
                    </div>
                </div>

                <!-- Stock Status -->
                <div class="mb-4">
                    <label class="f-14 font-weight-bold mb-2">Stock Status</label>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="stock1" checked>
                        <label class="form-check-label" for="stock1">In Stock</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="stock2">
                        <label class="form-check-label" for="stock2">Low Stock</label>
                    </div>
                </div>

                <button class="btn btn-primary btn-block">Apply Filters</button>
                <button class="btn btn-secondary btn-block mt-2">Reset</button>
            </div>
        </div>

        <!-- Product Grid -->
        <div class="col-lg-9">
            <!-- Sort & View Options -->
            <div class="bg-white rounded b-shadow-4 p-3 mb-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <span class="text-dark-grey mr-3">Sort by:</span>
                        <select class="form-control form-control-sm d-inline-block" style="width: auto;">
                            <option>Price: Low to High</option>
                            <option>Price: High to Low</option>
                            <option>Rating: Highest</option>
                            <option>Newest</option>
                            <option>Most Popular</option>
                        </select>
                    </div>
                    <div>
                        <button class="btn btn-sm btn-outline-secondary active">
                            <i class="fa fa-th"></i> Grid
                        </button>
                        <button class="btn btn-sm btn-outline-secondary">
                            <i class="fa fa-list"></i> List
                        </button>
                    </div>
                </div>
            </div>

            <!-- Product Grid -->
            <div class="row">
                <!-- Product 1 -->
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="card border-0 product-card h-100">
                        <div class="position-relative">
                            <img src="https://via.placeholder.com/300x300?text=Product" class="card-img-top" alt="Product">
                            <span class="badge badge-success position-absolute top-0 right-0 m-2">Bestseller</span>
                            <span class="badge badge-warning position-absolute top-0 left-0 m-2">-15%</span>
                            <button class="btn btn-sm btn-light position-absolute bottom-0 right-0 m-2">
                                <i class="fa fa-heart"></i>
                            </button>
                        </div>
                        <div class="card-body">
                            <p class="text-muted mb-1 small">Fresh Farm Co.</p>
                            <h5 class="card-title f-16 mb-2">
                                <a href="#" class="text-darkest-grey">Premium Organic Tomatoes (1kg)</a>
                            </h5>
                            
                            <!-- Pricing -->
                            <div class="mb-2">
                                <div class="d-flex align-items-baseline">
                                    <span class="text-dark-grey f-12 mr-2">Your Price:</span>
                                    <span class="f-20 font-weight-bold text-darkest-grey">$12.50</span>
                                </div>
                                <div class="d-flex align-items-baseline">
                                    <span class="text-muted f-12 mr-2">Original:</span>
                                    <span class="text-muted text-decoration-line-through f-14">$14.75</span>
                                    <span class="badge badge-success ml-2">Save $2.25</span>
                                </div>
                                <div class="mt-1">
                                    <small class="text-success">
                                        <i class="fa fa-tag"></i> Volume Discount Available
                                    </small>
                                </div>
                            </div>

                            <!-- Rating & Reviews -->
                            <div class="mb-2">
                                <div class="d-flex align-items-center">
                                    <div class="text-warning mr-2">
                                        <i class="fa fa-star"></i>
                                        <i class="fa fa-star"></i>
                                        <i class="fa fa-star"></i>
                                        <i class="fa fa-star"></i>
                                        <i class="fa fa-star-half-alt"></i>
                                    </div>
                                    <small class="text-dark-grey">4.5 (125 reviews)</small>
                                </div>
                            </div>

                            <!-- Stock & Delivery -->
                            <div class="mb-2">
                                <small class="text-success">
                                    <i class="fa fa-check-circle"></i> In Stock (234 units)
                                </small>
                                <br>
                                <small class="text-dark-grey">
                                    <i class="fa fa-truck"></i> Free delivery over $50
                                </small>
                            </div>

                            <!-- Actions -->
                            <div class="d-flex justify-content-between align-items-center mt-3">
                                <button class="btn btn-primary btn-sm flex-grow-1">
                                    <i class="fa fa-cart-plus"></i> Add to Cart
                                </button>
                                <a href="#" class="btn btn-outline-secondary btn-sm ml-2">
                                    <i class="fa fa-eye"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Product 2 -->
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="card border-0 product-card h-100">
                        <div class="position-relative">
                            <img src="https://via.placeholder.com/300x300?text=Product" class="card-img-top" alt="Product">
                            <span class="badge badge-info position-absolute top-0 right-0 m-2">New</span>
                            <button class="btn btn-sm btn-light position-absolute bottom-0 right-0 m-2">
                                <i class="fa fa-heart"></i>
                            </button>
                        </div>
                        <div class="card-body">
                            <p class="text-muted mb-1 small">Green Valley</p>
                            <h5 class="card-title f-16 mb-2">
                                <a href="#" class="text-darkest-grey">Fresh Organic Carrots (500g)</a>
                            </h5>
                            
                            <!-- Pricing -->
                            <div class="mb-2">
                                <div class="d-flex align-items-baseline">
                                    <span class="text-dark-grey f-12 mr-2">Your Price:</span>
                                    <span class="f-20 font-weight-bold text-darkest-grey">$8.90</span>
                                </div>
                                <div class="mt-1">
                                    <small class="text-info">
                                        <i class="fa fa-users"></i> B2B Pricing Applied
                                    </small>
                                </div>
                            </div>

                            <!-- Rating & Reviews -->
                            <div class="mb-2">
                                <div class="d-flex align-items-center">
                                    <div class="text-warning mr-2">
                                        <i class="fa fa-star"></i>
                                        <i class="fa fa-star"></i>
                                        <i class="fa fa-star"></i>
                                        <i class="fa fa-star"></i>
                                        <i class="fa fa-star"></i>
                                    </div>
                                    <small class="text-dark-grey">4.8 (89 reviews)</small>
                                </div>
                            </div>

                            <!-- Stock & Delivery -->
                            <div class="mb-2">
                                <small class="text-success">
                                    <i class="fa fa-check-circle"></i> In Stock (567 units)
                                </small>
                                <br>
                                <small class="text-dark-grey">
                                    <i class="fa fa-truck"></i> Same-day delivery available
                                </small>
                            </div>

                            <!-- Actions -->
                            <div class="d-flex justify-content-between align-items-center mt-3">
                                <button class="btn btn-primary btn-sm flex-grow-1">
                                    <i class="fa fa-cart-plus"></i> Add to Cart
                                </button>
                                <a href="#" class="btn btn-outline-secondary btn-sm ml-2">
                                    <i class="fa fa-eye"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Product 3 - Combo Product -->
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="card border-0 product-card h-100 border-primary">
                        <div class="position-relative">
                            <img src="https://via.placeholder.com/300x300?text=Combo" class="card-img-top" alt="Product">
                            <span class="badge badge-danger position-absolute top-0 right-0 m-2">Combo Deal</span>
                            <span class="badge badge-warning position-absolute top-0 left-0 m-2">Save $5</span>
                            <button class="btn btn-sm btn-light position-absolute bottom-0 right-0 m-2">
                                <i class="fa fa-heart"></i>
                            </button>
                        </div>
                        <div class="card-body">
                            <p class="text-muted mb-1 small">Fresh Farm Co.</p>
                            <h5 class="card-title f-16 mb-2">
                                <a href="#" class="text-darkest-grey">Vegetable Starter Pack</a>
                            </h5>
                            <p class="text-muted small mb-2">
                                Includes: Tomatoes + Carrots + Onions + Potatoes
                            </p>
                            
                            <!-- Pricing -->
                            <div class="mb-2">
                                <div class="d-flex align-items-baseline">
                                    <span class="text-dark-grey f-12 mr-2">Combo Price:</span>
                                    <span class="f-20 font-weight-bold text-darkest-grey">$35.00</span>
                                </div>
                                <div class="d-flex align-items-baseline">
                                    <span class="text-muted f-12 mr-2">Individual:</span>
                                    <span class="text-muted text-decoration-line-through f-14">$40.00</span>
                                    <span class="badge badge-success ml-2">Save $5.00</span>
                                </div>
                            </div>

                            <!-- Rating & Reviews -->
                            <div class="mb-2">
                                <div class="d-flex align-items-center">
                                    <div class="text-warning mr-2">
                                        <i class="fa fa-star"></i>
                                        <i class="fa fa-star"></i>
                                        <i class="fa fa-star"></i>
                                        <i class="fa fa-star"></i>
                                        <i class="fa fa-star"></i>
                                    </div>
                                    <small class="text-dark-grey">4.9 (203 reviews)</small>
                                </div>
                            </div>

                            <!-- Stock & Delivery -->
                            <div class="mb-2">
                                <small class="text-success">
                                    <i class="fa fa-check-circle"></i> In Stock
                                </small>
                                <br>
                                <small class="text-dark-grey">
                                    <i class="fa fa-truck"></i> Free delivery
                                </small>
                            </div>

                            <!-- Actions -->
                            <div class="d-flex justify-content-between align-items-center mt-3">
                                <button class="btn btn-primary btn-sm flex-grow-1">
                                    <i class="fa fa-cart-plus"></i> Add Combo to Cart
                                </button>
                                <a href="#" class="btn btn-outline-secondary btn-sm ml-2">
                                    <i class="fa fa-eye"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- More products... (repeat pattern) -->
                <!-- Product 4 -->
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="card border-0 product-card h-100">
                        <div class="position-relative">
                            <img src="https://via.placeholder.com/300x300?text=Product" class="card-img-top" alt="Product">
                            <button class="btn btn-sm btn-light position-absolute bottom-0 right-0 m-2">
                                <i class="fa fa-heart"></i>
                            </button>
                        </div>
                        <div class="card-body">
                            <p class="text-muted mb-1 small">Fresh Farm Co.</p>
                            <h5 class="card-title f-16 mb-2">
                                <a href="#" class="text-darkest-grey">Organic Bell Peppers (500g)</a>
                            </h5>
                            
                            <div class="mb-2">
                                <div class="d-flex align-items-baseline">
                                    <span class="text-dark-grey f-12 mr-2">Your Price:</span>
                                    <span class="f-20 font-weight-bold text-darkest-grey">$15.75</span>
                                </div>
                            </div>

                            <div class="mb-2">
                                <div class="d-flex align-items-center">
                                    <div class="text-warning mr-2">
                                        <i class="fa fa-star"></i>
                                        <i class="fa fa-star"></i>
                                        <i class="fa fa-star"></i>
                                        <i class="fa fa-star"></i>
                                        <i class="far fa-star"></i>
                                    </div>
                                    <small class="text-dark-grey">4.2 (67 reviews)</small>
                                </div>
                            </div>

                            <div class="mb-2">
                                <small class="text-warning">
                                    <i class="fa fa-exclamation-triangle"></i> Low Stock (12 units)
                                </small>
                            </div>

                            <div class="d-flex justify-content-between align-items-center mt-3">
                                <button class="btn btn-primary btn-sm flex-grow-1">
                                    <i class="fa fa-cart-plus"></i> Add to Cart
                                </button>
                                <a href="#" class="btn btn-outline-secondary btn-sm ml-2">
                                    <i class="fa fa-eye"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Product 5 -->
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="card border-0 product-card h-100">
                        <div class="position-relative">
                            <img src="https://via.placeholder.com/300x300?text=Product" class="card-img-top" alt="Product">
                            <span class="badge badge-secondary position-absolute top-0 right-0 m-2">Out of Stock</span>
                            <button class="btn btn-sm btn-light position-absolute bottom-0 right-0 m-2">
                                <i class="fa fa-heart"></i>
                            </button>
                        </div>
                        <div class="card-body">
                            <p class="text-muted mb-1 small">Green Valley</p>
                            <h5 class="card-title f-16 mb-2">
                                <a href="#" class="text-darkest-grey">Fresh Organic Spinach (250g)</a>
                            </h5>
                            
                            <div class="mb-2">
                                <div class="d-flex align-items-baseline">
                                    <span class="text-dark-grey f-12 mr-2">Your Price:</span>
                                    <span class="f-20 font-weight-bold text-darkest-grey">$6.50</span>
                                </div>
                            </div>

                            <div class="mb-2">
                                <div class="d-flex align-items-center">
                                    <div class="text-warning mr-2">
                                        <i class="fa fa-star"></i>
                                        <i class="fa fa-star"></i>
                                        <i class="fa fa-star"></i>
                                        <i class="fa fa-star"></i>
                                        <i class="fa fa-star-half-alt"></i>
                                    </div>
                                    <small class="text-dark-grey">4.6 (134 reviews)</small>
                                </div>
                            </div>

                            <div class="mb-2">
                                <small class="text-danger">
                                    <i class="fa fa-times-circle"></i> Out of Stock
                                </small>
                                <br>
                                <button class="btn btn-link btn-sm p-0 mt-1">
                                    <small>Notify me when available</small>
                                </button>
                            </div>

                            <div class="d-flex justify-content-between align-items-center mt-3">
                                <button class="btn btn-secondary btn-sm flex-grow-1" disabled>
                                    <i class="fa fa-cart-plus"></i> Out of Stock
                                </button>
                                <a href="#" class="btn btn-outline-secondary btn-sm ml-2">
                                    <i class="fa fa-eye"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Product 6 -->
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="card border-0 product-card h-100">
                        <div class="position-relative">
                            <img src="https://via.placeholder.com/300x300?text=Product" class="card-img-top" alt="Product">
                            <button class="btn btn-sm btn-light position-absolute bottom-0 right-0 m-2">
                                <i class="fa fa-heart"></i>
                            </button>
                        </div>
                        <div class="card-body">
                            <p class="text-muted mb-1 small">Fresh Farm Co.</p>
                            <h5 class="card-title f-16 mb-2">
                                <a href="#" class="text-darkest-grey">Organic Cucumbers (1kg)</a>
                            </h5>
                            
                            <div class="mb-2">
                                <div class="d-flex align-items-baseline">
                                    <span class="text-dark-grey f-12 mr-2">Your Price:</span>
                                    <span class="f-20 font-weight-bold text-darkest-grey">$9.90</span>
                                </div>
                                <div class="mt-1">
                                    <small class="text-success">
                                        <i class="fa fa-tag"></i> Buy 10+ for $8.90 each
                                    </small>
                                </div>
                            </div>

                            <div class="mb-2">
                                <div class="d-flex align-items-center">
                                    <div class="text-warning mr-2">
                                        <i class="fa fa-star"></i>
                                        <i class="fa fa-star"></i>
                                        <i class="fa fa-star"></i>
                                        <i class="fa fa-star"></i>
                                        <i class="far fa-star"></i>
                                    </div>
                                    <small class="text-dark-grey">4.3 (98 reviews)</small>
                                </div>
                            </div>

                            <div class="mb-2">
                                <small class="text-success">
                                    <i class="fa fa-check-circle"></i> In Stock (345 units)
                                </small>
                            </div>

                            <div class="d-flex justify-content-between align-items-center mt-3">
                                <button class="btn btn-primary btn-sm flex-grow-1">
                                    <i class="fa fa-cart-plus"></i> Add to Cart
                                </button>
                                <a href="#" class="btn btn-outline-secondary btn-sm ml-2">
                                    <i class="fa fa-eye"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pagination -->
            <div class="d-flex justify-content-center mt-4">
                <nav>
                    <ul class="pagination">
                        <li class="page-item disabled">
                            <a class="page-link" href="#" tabindex="-1">Previous</a>
                        </li>
                        <li class="page-item active"><a class="page-link" href="#">1</a></li>
                        <li class="page-item"><a class="page-link" href="#">2</a></li>
                        <li class="page-item"><a class="page-link" href="#">3</a></li>
                        <li class="page-item"><a class="page-link" href="#">4</a></li>
                        <li class="page-item"><a class="page-link" href="#">5</a></li>
                        <li class="page-item">
                            <a class="page-link" href="#">Next</a>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>
    </div>
</div>

<style>
.product-card {
    transition: transform 0.2s;
}
.product-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}
</style>

