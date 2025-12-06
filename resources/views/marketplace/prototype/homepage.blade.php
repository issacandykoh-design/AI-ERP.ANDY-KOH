@extends('prototype.layouts.app')

@section('content')
<!-- MARKETPLACE HOMEPAGE PROTOTYPE - HARDCODED DATA -->
<div class="content-wrapper">
    
    <!-- Hero Section -->
    <div class="bg-white rounded b-shadow-4 mb-4">
        <div class="row p-4">
            <div class="col-lg-6">
                <h1 class="f-28 font-weight-bold text-darkest-grey mb-3">Welcome to B2B Marketplace</h1>
                <p class="f-16 text-dark-grey mb-4">Your one-stop shop for F&B products and services</p>
                
                <!-- Search Bar -->
                <div class="input-group input-group-lg">
                    <input type="text" class="form-control" placeholder="Search products, suppliers, or categories..." value="">
                    <div class="input-group-append">
                        <button class="btn btn-primary" type="button">
                            <i class="fa fa-search"></i> Search
                        </button>
                    </div>
                </div>
                
                <!-- Quick Filters -->
                <div class="mt-3">
                    <span class="badge badge-secondary mr-2 p-2">Fresh Produce</span>
                    <span class="badge badge-secondary mr-2 p-2">Beverages</span>
                    <span class="badge badge-secondary mr-2 p-2">Packaged Foods</span>
                    <span class="badge badge-secondary mr-2 p-2">Kitchen Supplies</span>
                </div>
            </div>
            <div class="col-lg-6 text-center">
                <img src="https://via.placeholder.com/600x300?text=Marketplace+Hero+Image" alt="Marketplace" class="img-fluid rounded">
            </div>
        </div>
    </div>

    <!-- Featured Categories -->
    <div class="bg-white rounded b-shadow-4 mb-4">
        <div class="p-4 border-bottom-grey">
            <h2 class="f-21 font-weight-bold text-darkest-grey mb-0">Shop by Category</h2>
        </div>
        <div class="row p-4">
            <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                <div class="card border-0 text-center hover-shadow">
                    <div class="card-body p-4">
                        <div class="mb-3">
                            <i class="fa fa-apple-alt fa-3x text-success"></i>
                        </div>
                        <h5 class="card-title">Fresh Produce</h5>
                        <p class="text-muted mb-0">1,234 products</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                <div class="card border-0 text-center hover-shadow">
                    <div class="card-body p-4">
                        <div class="mb-3">
                            <i class="fa fa-wine-bottle fa-3x text-danger"></i>
                        </div>
                        <h5 class="card-title">Beverages</h5>
                        <p class="text-muted mb-0">856 products</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                <div class="card border-0 text-center hover-shadow">
                    <div class="card-body p-4">
                        <div class="mb-3">
                            <i class="fa fa-box fa-3x text-warning"></i>
                        </div>
                        <h5 class="card-title">Packaged Foods</h5>
                        <p class="text-muted mb-0">2,145 products</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                <div class="card border-0 text-center hover-shadow">
                    <div class="card-body p-4">
                        <div class="mb-3">
                            <i class="fa fa-utensils fa-3x text-info"></i>
                        </div>
                        <h5 class="card-title">Kitchen Supplies</h5>
                        <p class="text-muted mb-0">678 products</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Featured Products -->
    <div class="bg-white rounded b-shadow-4 mb-4">
        <div class="d-flex justify-content-between align-items-center p-4 border-bottom-grey">
            <h2 class="f-21 font-weight-bold text-darkest-grey mb-0">Featured Products</h2>
            <a href="#" class="text-primary">View All <i class="fa fa-arrow-right"></i></a>
        </div>
        <div class="row p-4">
            <!-- Product Card 1 -->
            <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                <div class="card border-0 product-card">
                    <div class="position-relative">
                        <img src="https://via.placeholder.com/300x300?text=Product+Image" class="card-img-top" alt="Product">
                        <span class="badge badge-success position-absolute top-0 right-0 m-2">Bestseller</span>
                        <span class="badge badge-warning position-absolute top-0 left-0 m-2">-15%</span>
                    </div>
                    <div class="card-body">
                        <p class="text-muted mb-1 small">Fresh Produce</p>
                        <h5 class="card-title f-16 mb-2">Premium Organic Tomatoes</h5>
                        <div class="mb-2">
                            <span class="text-dark-grey">From:</span>
                            <span class="f-18 font-weight-bold text-darkest-grey">$12.50</span>
                            <span class="text-muted text-decoration-line-through ml-2">$14.75</span>
                        </div>
                        <div class="mb-2">
                            <small class="text-success">
                                <i class="fa fa-star"></i> 4.5 (125 reviews)
                            </small>
                        </div>
                        <div class="mb-2">
                            <small class="text-dark-grey">
                                <i class="fa fa-store"></i> Fresh Farm Co.
                            </small>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <button class="btn btn-primary btn-sm">
                                <i class="fa fa-cart-plus"></i> Add to Cart
                            </button>
                            <a href="#" class="text-primary">
                                <i class="fa fa-heart"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Product Card 2 -->
            <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                <div class="card border-0 product-card">
                    <div class="position-relative">
                        <img src="https://via.placeholder.com/300x300?text=Product+Image" class="card-img-top" alt="Product">
                        <span class="badge badge-info position-absolute top-0 right-0 m-2">New</span>
                    </div>
                    <div class="card-body">
                        <p class="text-muted mb-1 small">Beverages</p>
                        <h5 class="card-title f-16 mb-2">Artisan Coffee Beans (1kg)</h5>
                        <div class="mb-2">
                            <span class="text-dark-grey">From:</span>
                            <span class="f-18 font-weight-bold text-darkest-grey">$28.90</span>
                        </div>
                        <div class="mb-2">
                            <small class="text-success">
                                <i class="fa fa-star"></i> 4.8 (89 reviews)
                            </small>
                        </div>
                        <div class="mb-2">
                            <small class="text-dark-grey">
                                <i class="fa fa-store"></i> Coffee Masters
                            </small>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <button class="btn btn-primary btn-sm">
                                <i class="fa fa-cart-plus"></i> Add to Cart
                            </button>
                            <a href="#" class="text-primary">
                                <i class="fa fa-heart"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Product Card 3 -->
            <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                <div class="card border-0 product-card">
                    <div class="position-relative">
                        <img src="https://via.placeholder.com/300x300?text=Product+Image" class="card-img-top" alt="Product">
                        <span class="badge badge-danger position-absolute top-0 right-0 m-2">Hot Deal</span>
                    </div>
                    <div class="card-body">
                        <p class="text-muted mb-1 small">Packaged Foods</p>
                        <h5 class="card-title f-16 mb-2">Gourmet Pasta Set (5 Varieties)</h5>
                        <div class="mb-2">
                            <span class="text-dark-grey">From:</span>
                            <span class="f-18 font-weight-bold text-darkest-grey">$45.00</span>
                            <span class="text-muted text-decoration-line-through ml-2">$55.00</span>
                        </div>
                        <div class="mb-2">
                            <small class="text-success">
                                <i class="fa fa-star"></i> 4.6 (203 reviews)
                            </small>
                        </div>
                        <div class="mb-2">
                            <small class="text-dark-grey">
                                <i class="fa fa-store"></i> Italian Delights
                            </small>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <button class="btn btn-primary btn-sm">
                                <i class="fa fa-cart-plus"></i> Add to Cart
                            </button>
                            <a href="#" class="text-primary">
                                <i class="fa fa-heart"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Product Card 4 -->
            <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                <div class="card border-0 product-card">
                    <div class="position-relative">
                        <img src="https://via.placeholder.com/300x300?text=Product+Image" class="card-img-top" alt="Product">
                    </div>
                    <div class="card-body">
                        <p class="text-muted mb-1 small">Kitchen Supplies</p>
                        <h5 class="card-title f-16 mb-2">Professional Chef Knife Set</h5>
                        <div class="mb-2">
                            <span class="text-dark-grey">From:</span>
                            <span class="f-18 font-weight-bold text-darkest-grey">$189.00</span>
                        </div>
                        <div class="mb-2">
                            <small class="text-success">
                                <i class="fa fa-star"></i> 4.9 (156 reviews)
                            </small>
                        </div>
                        <div class="mb-2">
                            <small class="text-dark-grey">
                                <i class="fa fa-store"></i> Kitchen Pro
                            </small>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <button class="btn btn-primary btn-sm">
                                <i class="fa fa-cart-plus"></i> Add to Cart
                            </button>
                            <a href="#" class="text-primary">
                                <i class="fa fa-heart"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Featured Merchants -->
    <div class="bg-white rounded b-shadow-4 mb-4">
        <div class="d-flex justify-content-between align-items-center p-4 border-bottom-grey">
            <h2 class="f-21 font-weight-bold text-darkest-grey mb-0">Top Suppliers</h2>
            <a href="#" class="text-primary">View All <i class="fa fa-arrow-right"></i></a>
        </div>
        <div class="row p-4">
            <!-- Merchant Card 1 -->
            <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                <div class="card border-0 text-center">
                    <div class="card-body p-4">
                        <img src="https://via.placeholder.com/100x100?text=Logo" class="rounded-circle mb-3" alt="Merchant">
                        <h5 class="card-title">Fresh Farm Co.</h5>
                        <p class="text-muted small mb-2">Fresh Produce Specialist</p>
                        <div class="mb-2">
                            <span class="badge badge-success">Verified</span>
                            <span class="badge badge-info">Premium</span>
                        </div>
                        <div class="mb-2">
                            <small class="text-dark-grey">
                                <i class="fa fa-star text-warning"></i> 4.8
                            </small>
                            <small class="text-muted ml-2">(1,234 reviews)</small>
                        </div>
                        <div class="mb-2">
                            <small class="text-dark-grey">1,234 products</small>
                        </div>
                        <a href="#" class="btn btn-outline-primary btn-sm">Visit Store</a>
                    </div>
                </div>
            </div>

            <!-- Merchant Card 2 -->
            <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                <div class="card border-0 text-center">
                    <div class="card-body p-4">
                        <img src="https://via.placeholder.com/100x100?text=Logo" class="rounded-circle mb-3" alt="Merchant">
                        <h5 class="card-title">Coffee Masters</h5>
                        <p class="text-muted small mb-2">Premium Coffee & Beverages</p>
                        <div class="mb-2">
                            <span class="badge badge-success">Verified</span>
                        </div>
                        <div class="mb-2">
                            <small class="text-dark-grey">
                                <i class="fa fa-star text-warning"></i> 4.9
                            </small>
                            <small class="text-muted ml-2">(856 reviews)</small>
                        </div>
                        <div class="mb-2">
                            <small class="text-dark-grey">856 products</small>
                        </div>
                        <a href="#" class="btn btn-outline-primary btn-sm">Visit Store</a>
                    </div>
                </div>
            </div>

            <!-- Merchant Card 3 -->
            <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                <div class="card border-0 text-center">
                    <div class="card-body p-4">
                        <img src="https://via.placeholder.com/100x100?text=Logo" class="rounded-circle mb-3" alt="Merchant">
                        <h5 class="card-title">Italian Delights</h5>
                        <p class="text-muted small mb-2">Authentic Italian Products</p>
                        <div class="mb-2">
                            <span class="badge badge-success">Verified</span>
                            <span class="badge badge-warning">Featured</span>
                        </div>
                        <div class="mb-2">
                            <small class="text-dark-grey">
                                <i class="fa fa-star text-warning"></i> 4.7
                            </small>
                            <small class="text-muted ml-2">(2,145 reviews)</small>
                        </div>
                        <div class="mb-2">
                            <small class="text-dark-grey">2,145 products</small>
                        </div>
                        <a href="#" class="btn btn-outline-primary btn-sm">Visit Store</a>
                    </div>
                </div>
            </div>

            <!-- Merchant Card 4 -->
            <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                <div class="card border-0 text-center">
                    <div class="card-body p-4">
                        <img src="https://via.placeholder.com/100x100?text=Logo" class="rounded-circle mb-3" alt="Merchant">
                        <h5 class="card-title">Kitchen Pro</h5>
                        <p class="text-muted small mb-2">Professional Kitchen Equipment</p>
                        <div class="mb-2">
                            <span class="badge badge-success">Verified</span>
                        </div>
                        <div class="mb-2">
                            <small class="text-dark-grey">
                                <i class="fa fa-star text-warning"></i> 4.6
                            </small>
                            <small class="text-muted ml-2">(678 reviews)</small>
                        </div>
                        <div class="mb-2">
                            <small class="text-dark-grey">678 products</small>
                        </div>
                        <a href="#" class="btn btn-outline-primary btn-sm">Visit Store</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Deals & Promotions -->
    <div class="bg-white rounded b-shadow-4 mb-4">
        <div class="d-flex justify-content-between align-items-center p-4 border-bottom-grey">
            <h2 class="f-21 font-weight-bold text-darkest-grey mb-0">Special Deals</h2>
            <a href="#" class="text-primary">View All <i class="fa fa-arrow-right"></i></a>
        </div>
        <div class="row p-4">
            <div class="col-lg-6 mb-4">
                <div class="card border-0 bg-gradient-primary text-white">
                    <div class="card-body p-4">
                        <h3 class="text-white mb-2">Flash Sale!</h3>
                        <p class="mb-3">Up to 30% off on all fresh produce</p>
                        <p class="mb-0"><small>Ends in: 2 days 5 hours</small></p>
                        <a href="#" class="btn btn-light btn-sm mt-3">Shop Now</a>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 mb-4">
                <div class="card border-0 bg-gradient-success text-white">
                    <div class="card-body p-4">
                        <h3 class="text-white mb-2">Bulk Order Discount</h3>
                        <p class="mb-3">Order $500+ and get 15% off</p>
                        <p class="mb-0"><small>Valid until end of month</small></p>
                        <a href="#" class="btn btn-light btn-sm mt-3">Learn More</a>
                    </div>
                </div>
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
.hover-shadow:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}
</style>
@endsection

