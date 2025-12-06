@extends('prototype.layouts.app')

@section('title', 'B2B Marketplace - Home')

@push('styles')
<style>
    .hero-section {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 80px 0;
        margin-bottom: 60px;
    }
    .category-card {
        transition: transform 0.3s;
        border: none;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    .category-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }
    .product-card {
        border: none;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        transition: transform 0.3s;
        height: 100%;
    }
    .product-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }
    .product-image {
        height: 200px;
        object-fit: cover;
        width: 100%;
    }
    .merchant-card {
        border: none;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        transition: transform 0.3s;
    }
    .merchant-card:hover {
        transform: translateY(-5px);
    }
    .badge-discount {
        background: #ef4444;
        color: white;
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 12px;
        font-weight: 600;
    }
    .price-comparison {
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .price-original {
        text-decoration: line-through;
        color: #9ca3af;
        font-size: 14px;
    }
    .price-current {
        color: #2563eb;
        font-size: 18px;
        font-weight: 600;
    }
    .savings-badge {
        background: #10b981;
        color: white;
        padding: 2px 6px;
        border-radius: 4px;
        font-size: 11px;
    }
</style>
@endpush

@section('content')
<!-- Hero Section -->
<section class="hero-section">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <h1 class="display-4 fw-bold mb-4">Singapore's #1 B2B F&B Marketplace</h1>
                <p class="lead mb-4">Connect with verified suppliers. Get wholesale prices. Fast delivery.</p>
                <div class="d-flex gap-3">
                    <a href="/prototype/marketplace/products" class="btn btn-light btn-lg">
                        <i class="fas fa-search"></i> Browse Products
                    </a>
                    <a href="/prototype/marketplace/merchants" class="btn btn-outline-light btn-lg">
                        <i class="fas fa-store"></i> Find Suppliers
                    </a>
                </div>
            </div>
            <div class="col-lg-6 text-center">
                <div class="hero-image-placeholder bg-white bg-opacity-20 rounded p-5">
                    <i class="fas fa-shopping-basket fa-5x"></i>
                </div>
            </div>
        </div>
    </div>
</section>

<div class="container">
    <!-- Featured Categories -->
    <section class="mb-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="h3 fw-bold">Shop by Category</h2>
            <a href="/prototype/marketplace/products" class="text-decoration-none">View All <i class="fas fa-arrow-right"></i></a>
        </div>
        <div class="row g-4">
            <div class="col-md-3 col-sm-6">
                <div class="card category-card text-center p-4">
                    <div class="mb-3">
                        <i class="fas fa-carrot fa-3x text-success"></i>
                    </div>
                    <h5 class="fw-bold">Fresh Produce</h5>
                    <p class="text-muted small mb-0">1,234 products</p>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="card category-card text-center p-4">
                    <div class="mb-3">
                        <i class="fas fa-drumstick-bite fa-3x text-danger"></i>
                    </div>
                    <h5 class="fw-bold">Meat & Seafood</h5>
                    <p class="text-muted small mb-0">856 products</p>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="card category-card text-center p-4">
                    <div class="mb-3">
                        <i class="fas fa-wine-bottle fa-3x text-primary"></i>
                    </div>
                    <h5 class="fw-bold">Beverages</h5>
                    <p class="text-muted small mb-0">642 products</p>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="card category-card text-center p-4">
                    <div class="mb-3">
                        <i class="fas fa-bread-slice fa-3x text-warning"></i>
                    </div>
                    <h5 class="fw-bold">Bakery & Pastry</h5>
                    <p class="text-muted small mb-0">423 products</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Featured Merchants -->
    <section class="mb-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="h3 fw-bold">Featured Merchants</h2>
            <a href="/prototype/marketplace/merchants" class="text-decoration-none">View All <i class="fas fa-arrow-right"></i></a>
        </div>
        <div class="row g-4">
            <div class="col-md-3 col-sm-6">
                <div class="card merchant-card p-3 text-center">
                    <div class="merchant-logo mb-3">
                        <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                            <i class="fas fa-store fa-2x text-primary"></i>
                        </div>
                    </div>
                    <h5 class="fw-bold mb-1">Fresh Farm Co.</h5>
                    <div class="mb-2">
                        <span class="badge bg-success">Verified</span>
                    </div>
                    <div class="mb-2">
                        <i class="fas fa-star text-warning"></i>
                        <i class="fas fa-star text-warning"></i>
                        <i class="fas fa-star text-warning"></i>
                        <i class="fas fa-star text-warning"></i>
                        <i class="fas fa-star text-warning"></i>
                        <span class="text-muted small">4.8</span>
                    </div>
                    <p class="text-muted small mb-3">Fresh produce supplier</p>
                    <a href="/prototype/marketplace/merchants/1" class="btn btn-outline-primary btn-sm">View Store</a>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="card merchant-card p-3 text-center">
                    <div class="merchant-logo mb-3">
                        <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                            <i class="fas fa-fish fa-2x text-info"></i>
                        </div>
                    </div>
                    <h5 class="fw-bold mb-1">Ocean Fresh Seafood</h5>
                    <div class="mb-2">
                        <span class="badge bg-success">Verified</span>
                    </div>
                    <div class="mb-2">
                        <i class="fas fa-star text-warning"></i>
                        <i class="fas fa-star text-warning"></i>
                        <i class="fas fa-star text-warning"></i>
                        <i class="fas fa-star text-warning"></i>
                        <i class="fas fa-star-half-alt text-warning"></i>
                        <span class="text-muted small">4.7</span>
                    </div>
                    <p class="text-muted small mb-3">Premium seafood supplier</p>
                    <a href="/prototype/marketplace/merchants/2" class="btn btn-outline-primary btn-sm">View Store</a>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="card merchant-card p-3 text-center">
                    <div class="merchant-logo mb-3">
                        <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                            <i class="fas fa-wine-glass fa-2x text-danger"></i>
                        </div>
                    </div>
                    <h5 class="fw-bold mb-1">Beverage Hub SG</h5>
                    <div class="mb-2">
                        <span class="badge bg-success">Verified</span>
                    </div>
                    <div class="mb-2">
                        <i class="fas fa-star text-warning"></i>
                        <i class="fas fa-star text-warning"></i>
                        <i class="fas fa-star text-warning"></i>
                        <i class="fas fa-star text-warning"></i>
                        <i class="fas fa-star text-warning"></i>
                        <span class="text-muted small">4.9</span>
                    </div>
                    <p class="text-muted small mb-3">Beverages & drinks supplier</p>
                    <a href="/prototype/marketplace/merchants/3" class="btn btn-outline-primary btn-sm">View Store</a>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="card merchant-card p-3 text-center">
                    <div class="merchant-logo mb-3">
                        <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                            <i class="fas fa-drumstick-bite fa-2x text-warning"></i>
                        </div>
                    </div>
                    <h5 class="fw-bold mb-1">Meat Masters</h5>
                    <div class="mb-2">
                        <span class="badge bg-success">Verified</span>
                    </div>
                    <div class="mb-2">
                        <i class="fas fa-star text-warning"></i>
                        <i class="fas fa-star text-warning"></i>
                        <i class="fas fa-star text-warning"></i>
                        <i class="fas fa-star text-warning"></i>
                        <i class="far fa-star text-warning"></i>
                        <span class="text-muted small">4.5</span>
                    </div>
                    <p class="text-muted small mb-3">Premium meat supplier</p>
                    <a href="/prototype/marketplace/merchants/4" class="btn btn-outline-primary btn-sm">View Store</a>
                </div>
            </div>
        </div>
    </section>

    <!-- Trending Products -->
    <section class="mb-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="h3 fw-bold">Trending Products</h2>
            <a href="/prototype/marketplace/products" class="text-decoration-none">View All <i class="fas fa-arrow-right"></i></a>
        </div>
        <div class="row g-4">
            <!-- Product 1 -->
            <div class="col-lg-3 col-md-4 col-sm-6">
                <div class="card product-card">
                    <div class="position-relative">
                        <div class="product-image bg-light d-flex align-items-center justify-content-center">
                            <i class="fas fa-drumstick-bite fa-4x text-muted"></i>
                        </div>
                        <span class="badge-discount position-absolute top-0 start-0 m-2">-20%</span>
                        <button class="btn btn-sm btn-light position-absolute top-0 end-0 m-2">
                            <i class="far fa-heart"></i>
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="mb-2">
                            <span class="badge bg-success">Halal</span>
                            <span class="badge bg-info">Organic</span>
                        </div>
                        <h6 class="card-title mb-2">Fresh Organic Chicken Breast</h6>
                        <p class="text-muted small mb-2">Fresh Farm Co.</p>
                        <div class="price-comparison mb-2">
                            <span class="price-current">$20.00</span>
                            <span class="price-original">$25.00</span>
                            <span class="savings-badge">Save $5</span>
                        </div>
                        <div class="mb-2">
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star-half-alt text-warning"></i>
                            <span class="text-muted small">(125)</span>
                        </div>
                        <div class="d-flex gap-2">
                            <button class="btn btn-primary btn-sm flex-fill">
                                <i class="fas fa-cart-plus"></i> Add to Cart
                            </button>
                            <a href="/prototype/marketplace/products/1" class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-eye"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Product 2 -->
            <div class="col-lg-3 col-md-4 col-sm-6">
                <div class="card product-card">
                    <div class="position-relative">
                        <div class="product-image bg-light d-flex align-items-center justify-content-center">
                            <i class="fas fa-fish fa-4x text-muted"></i>
                        </div>
                        <span class="badge bg-success position-absolute top-0 start-0 m-2">In Stock</span>
                        <button class="btn btn-sm btn-light position-absolute top-0 end-0 m-2">
                            <i class="far fa-heart"></i>
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="mb-2">
                            <span class="badge bg-success">Halal</span>
                        </div>
                        <h6 class="card-title mb-2">Fresh Salmon Fillet</h6>
                        <p class="text-muted small mb-2">Ocean Fresh Seafood</p>
                        <div class="price-comparison mb-2">
                            <span class="price-current">$35.00</span>
                            <span class="price-original">$40.00</span>
                            <span class="savings-badge">Save $5</span>
                        </div>
                        <div class="mb-2">
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                            <span class="text-muted small">(89)</span>
                        </div>
                        <div class="d-flex gap-2">
                            <button class="btn btn-primary btn-sm flex-fill">
                                <i class="fas fa-cart-plus"></i> Add to Cart
                            </button>
                            <a href="/prototype/marketplace/products/2" class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-eye"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Product 3 -->
            <div class="col-lg-3 col-md-4 col-sm-6">
                <div class="card product-card">
                    <div class="position-relative">
                        <div class="product-image bg-light d-flex align-items-center justify-content-center">
                            <i class="fas fa-carrot fa-4x text-muted"></i>
                        </div>
                        <span class="badge bg-warning position-absolute top-0 start-0 m-2">Low Stock</span>
                        <button class="btn btn-sm btn-light position-absolute top-0 end-0 m-2">
                            <i class="far fa-heart"></i>
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="mb-2">
                            <span class="badge bg-info">Organic</span>
                        </div>
                        <h6 class="card-title mb-2">Organic Carrots (5kg)</h6>
                        <p class="text-muted small mb-2">Fresh Farm Co.</p>
                        <div class="price-comparison mb-2">
                            <span class="price-current">$12.00</span>
                            <span class="price-original">$15.00</span>
                            <span class="savings-badge">Save $3</span>
                        </div>
                        <div class="mb-2">
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                            <i class="far fa-star text-warning"></i>
                            <span class="text-muted small">(67)</span>
                        </div>
                        <div class="d-flex gap-2">
                            <button class="btn btn-primary btn-sm flex-fill">
                                <i class="fas fa-cart-plus"></i> Add to Cart
                            </button>
                            <a href="/prototype/marketplace/products/3" class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-eye"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Product 4 -->
            <div class="col-lg-3 col-md-4 col-sm-6">
                <div class="card product-card">
                    <div class="position-relative">
                        <div class="product-image bg-light d-flex align-items-center justify-content-center">
                            <i class="fas fa-wine-bottle fa-4x text-muted"></i>
                        </div>
                        <span class="badge-discount position-absolute top-0 start-0 m-2">-15%</span>
                        <button class="btn btn-sm btn-light position-absolute top-0 end-0 m-2">
                            <i class="far fa-heart"></i>
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="mb-2">
                            <span class="badge bg-primary">Premium</span>
                        </div>
                        <h6 class="card-title mb-2">Premium Red Wine (750ml)</h6>
                        <p class="text-muted small mb-2">Beverage Hub SG</p>
                        <div class="price-comparison mb-2">
                            <span class="price-current">$42.50</span>
                            <span class="price-original">$50.00</span>
                            <span class="savings-badge">Save $7.50</span>
                        </div>
                        <div class="mb-2">
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star-half-alt text-warning"></i>
                            <span class="text-muted small">(234)</span>
                        </div>
                        <div class="d-flex gap-2">
                            <button class="btn btn-primary btn-sm flex-fill">
                                <i class="fas fa-cart-plus"></i> Add to Cart
                            </button>
                            <a href="/prototype/marketplace/products/4" class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-eye"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Deals & Promotions -->
    <section class="mb-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="h3 fw-bold">Deals & Promotions</h2>
            <a href="#" class="text-decoration-none">View All <i class="fas fa-arrow-right"></i></a>
        </div>
        <div class="row g-4">
            <div class="col-md-4">
                <div class="card border-danger">
                    <div class="card-body bg-danger bg-opacity-10">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <h5 class="fw-bold text-danger">Flash Sale</h5>
                                <p class="mb-0">Up to 30% off on selected items</p>
                            </div>
                            <span class="badge bg-danger">Ends in 2h</span>
                        </div>
                        <a href="#" class="btn btn-danger">Shop Now</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-primary">
                    <div class="card-body bg-primary bg-opacity-10">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <h5 class="fw-bold text-primary">Bulk Discount</h5>
                                <p class="mb-0">Buy 10+ items, get 15% off</p>
                            </div>
                            <span class="badge bg-primary">Limited</span>
                        </div>
                        <a href="#" class="btn btn-primary">Learn More</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-success">
                    <div class="card-body bg-success bg-opacity-10">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <h5 class="fw-bold text-success">New Merchant</h5>
                                <p class="mb-0">Welcome bonus: $50 off first order</p>
                            </div>
                            <span class="badge bg-success">New</span>
                        </div>
                        <a href="#" class="btn btn-success">Claim Offer</a>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection

