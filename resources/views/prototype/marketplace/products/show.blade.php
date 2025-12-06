@extends('prototype.layouts.app')

@section('title', 'Product Detail - B2B Marketplace')

@push('styles')
<style>
    .product-detail-image {
        border-radius: 8px;
        overflow: hidden;
        background: var(--gray-100);
        height: 500px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .product-thumbnail {
        width: 80px;
        height: 80px;
        border-radius: 8px;
        cursor: pointer;
        border: 2px solid transparent;
        transition: all 0.3s;
    }
    .product-thumbnail:hover,
    .product-thumbnail.active {
        border-color: var(--primary-color);
    }
    .pricing-card {
        background: var(--gray-50);
        border-radius: 8px;
        padding: 1.5rem;
    }
    .delivery-option {
        border: 2px solid var(--gray-200);
        border-radius: 8px;
        padding: 1rem;
        cursor: pointer;
        transition: all 0.3s;
    }
    .delivery-option:hover,
    .delivery-option.selected {
        border-color: var(--primary-color);
        background: var(--primary-color);
        background-opacity: 0.1;
    }
    .tab-content {
        padding: 2rem 0;
    }
    .nutrition-table {
        width: 100%;
    }
    .nutrition-table td {
        padding: 0.5rem;
        border-bottom: 1px solid var(--gray-200);
    }
</style>
@endpush

@section('content')
<div class="container my-5">
    <!-- Breadcrumbs -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/prototype/marketplace">Home</a></li>
            <li class="breadcrumb-item"><a href="/prototype/marketplace/products">Products</a></li>
            <li class="breadcrumb-item"><a href="#">Meat & Seafood</a></li>
            <li class="breadcrumb-item active">Fresh Organic Chicken Breast</li>
        </ol>
    </nav>

    <div class="row">
        <!-- Product Images -->
        <div class="col-lg-6 mb-4">
            <div class="product-detail-image mb-3">
                <i class="fas fa-drumstick-bite fa-8x text-muted"></i>
            </div>
            <div class="d-flex gap-2">
                <div class="product-thumbnail active bg-light d-flex align-items-center justify-content-center">
                    <i class="fas fa-drumstick-bite fa-2x text-muted"></i>
                </div>
                <div class="product-thumbnail bg-light d-flex align-items-center justify-content-center">
                    <i class="fas fa-drumstick-bite fa-2x text-muted"></i>
                </div>
                <div class="product-thumbnail bg-light d-flex align-items-center justify-content-center">
                    <i class="fas fa-drumstick-bite fa-2x text-muted"></i>
                </div>
                <div class="product-thumbnail bg-light d-flex align-items-center justify-content-center">
                    <i class="fas fa-drumstick-bite fa-2x text-muted"></i>
                </div>
            </div>
        </div>

        <!-- Product Info -->
        <div class="col-lg-6">
            <div class="mb-3">
                <span class="badge bg-success">Halal</span>
                <span class="badge bg-info">Organic</span>
                <span class="badge bg-warning">Premium</span>
            </div>
            
            <h1 class="h2 fw-bold mb-3">Fresh Organic Chicken Breast</h1>
            
            <div class="mb-3">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <div>
                        <i class="fas fa-star text-warning"></i>
                        <i class="fas fa-star text-warning"></i>
                        <i class="fas fa-star text-warning"></i>
                        <i class="fas fa-star text-warning"></i>
                        <i class="fas fa-star-half-alt text-warning"></i>
                        <span class="ms-2">4.7</span>
                    </div>
                    <span class="text-muted">(125 reviews)</span>
                    <span class="text-muted">|</span>
                    <span class="text-success"><i class="fas fa-check-circle"></i> 98% would buy again</span>
                </div>
            </div>

            <div class="pricing-card mb-4">
                <div class="mb-2">
                    <span class="text-muted small">Merchant:</span>
                    <strong>Fresh Farm Co.</strong>
                    <span class="badge bg-success ms-2">Verified</span>
                </div>
                
                <div class="mb-3">
                    <div class="d-flex align-items-baseline gap-2 mb-1">
                        <span class="h3 fw-bold text-primary">$20.00</span>
                        <span class="text-muted text-decoration-line-through">$25.00</span>
                        <span class="badge bg-success">Save $5.00</span>
                    </div>
                    <small class="text-muted">Your B2B Price (Tier 1)</small>
                </div>

                <div class="border-top pt-3">
                    <small class="text-muted d-block mb-2">Pricing Comparison:</small>
                    <div class="row g-2">
                        <div class="col-6">
                            <div class="bg-white p-2 rounded border">
                                <small class="text-muted d-block">Public Price</small>
                                <strong>$25.00</strong>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="bg-white p-2 rounded border border-primary">
                                <small class="text-primary d-block">Your Price</small>
                                <strong class="text-primary">$20.00</strong>
                            </div>
                        </div>
                    </div>
                    <div class="mt-2 text-center">
                        <span class="badge bg-success">You Save: $5.00 (20%)</span>
                    </div>
                </div>
            </div>

            <!-- Stock Status -->
            <div class="alert alert-success mb-4">
                <i class="fas fa-check-circle"></i> <strong>In Stock</strong> - 150 units available
            </div>

            <!-- Quantity Selector -->
            <div class="mb-4">
                <label class="form-label">Quantity</label>
                <div class="d-flex align-items-center gap-3">
                    <div class="input-group" style="max-width: 150px;">
                        <button class="btn btn-outline-secondary" type="button">-</button>
                        <input type="number" class="form-control text-center" value="1" min="1" max="150">
                        <button class="btn btn-outline-secondary" type="button">+</button>
                    </div>
                    <small class="text-muted">MOQ: 10 units | Max: 150 units</small>
                </div>
            </div>

            <!-- Delivery Options -->
            <div class="mb-4">
                <label class="form-label mb-3">Delivery Options</label>
                <div class="row g-2">
                    <div class="col-6">
                        <div class="delivery-option selected">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <i class="fas fa-bolt text-warning"></i>
                                    <strong class="d-block">Same Day</strong>
                                    <small class="text-muted">Today by 6 PM</small>
                                </div>
                                <strong>$8.00</strong>
                            </div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="delivery-option">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <i class="fas fa-truck text-primary"></i>
                                    <strong class="d-block">Next Day</strong>
                                    <small class="text-muted">Tomorrow</small>
                                </div>
                                <strong>$5.00</strong>
                            </div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="delivery-option">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <i class="fas fa-box text-success"></i>
                                    <strong class="d-block">Standard</strong>
                                    <small class="text-muted">3-5 days</small>
                                </div>
                                <strong>$3.00</strong>
                            </div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="delivery-option">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <i class="fas fa-piggy-bank text-info"></i>
                                    <strong class="d-block">Economy</strong>
                                    <small class="text-muted">7-10 days</small>
                                </div>
                                <strong>Free</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="d-flex gap-2 mb-4">
                <button class="btn btn-primary btn-lg flex-fill">
                    <i class="fas fa-cart-plus"></i> Add to Cart
                </button>
                <button class="btn btn-outline-primary btn-lg">
                    <i class="fas fa-heart"></i>
                </button>
                <button class="btn btn-outline-primary btn-lg">
                    <i class="fas fa-share-alt"></i>
                </button>
            </div>

            <!-- Product Info -->
            <div class="border-top pt-4">
                <div class="row g-3">
                    <div class="col-6">
                        <small class="text-muted d-block">SKU</small>
                        <strong>CHK-BRST-001</strong>
                    </div>
                    <div class="col-6">
                        <small class="text-muted d-block">Category</small>
                        <strong>Meat & Seafood > Poultry</strong>
                    </div>
                    <div class="col-6">
                        <small class="text-muted d-block">Weight</small>
                        <strong>500g per pack</strong>
                    </div>
                    <div class="col-6">
                        <small class="text-muted d-block">Expiry</small>
                        <strong>Best before: Dec 31, 2024</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Product Tabs -->
    <div class="row mt-5">
        <div class="col-12">
            <ul class="nav nav-tabs" id="productTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="description-tab" data-bs-toggle="tab" data-bs-target="#description" type="button">
                        Description
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="specifications-tab" data-bs-toggle="tab" data-bs-target="#specifications" type="button">
                        Specifications
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="nutrition-tab" data-bs-toggle="tab" data-bs-target="#nutrition" type="button">
                        Nutrition Facts
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="reviews-tab" data-bs-toggle="tab" data-bs-target="#reviews" type="button">
                        Reviews (125)
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="faq-tab" data-bs-toggle="tab" data-bs-target="#faq" type="button">
                        FAQs
                    </button>
                </li>
            </ul>
            
            <div class="tab-content" id="productTabsContent">
                <!-- Description Tab -->
                <div class="tab-pane fade show active" id="description" role="tabpanel">
                    <h4 class="mb-3">Product Description</h4>
                    <p>Fresh organic chicken breast from free-range chickens. Raised without antibiotics or hormones, these premium chicken breasts are perfect for restaurants, cafes, and catering businesses.</p>
                    
                    <h5 class="mt-4 mb-3">Key Features:</h5>
                    <ul>
                        <li>100% organic and free-range</li>
                        <li>Halal certified</li>
                        <li>No antibiotics or hormones</li>
                        <li>Fresh, never frozen</li>
                        <li>Premium quality guaranteed</li>
                    </ul>

                    <h5 class="mt-4 mb-3">Storage Instructions:</h5>
                    <p>Keep refrigerated at 2-4°C. Consume within 3 days of delivery. Can be frozen for up to 3 months.</p>

                    <h5 class="mt-4 mb-3">Preparation:</h5>
                    <p>Thaw if frozen. Cook thoroughly before consumption. Perfect for grilling, pan-frying, or baking.</p>
                </div>

                <!-- Specifications Tab -->
                <div class="tab-pane fade" id="specifications" role="tabpanel">
                    <h4 class="mb-3">Product Specifications</h4>
                    <table class="table">
                        <tbody>
                            <tr>
                                <td><strong>Product Name</strong></td>
                                <td>Fresh Organic Chicken Breast</td>
                            </tr>
                            <tr>
                                <td><strong>Brand</strong></td>
                                <td>Fresh Farm Co.</td>
                            </tr>
                            <tr>
                                <td><strong>Weight</strong></td>
                                <td>500g per pack</td>
                            </tr>
                            <tr>
                                <td><strong>Origin</strong></td>
                                <td>Singapore</td>
                            </tr>
                            <tr>
                                <td><strong>Certifications</strong></td>
                                <td>Halal, Organic</td>
                            </tr>
                            <tr>
                                <td><strong>Allergens</strong></td>
                                <td>None</td>
                            </tr>
                            <tr>
                                <td><strong>Shelf Life</strong></td>
                                <td>3 days (refrigerated)</td>
                            </tr>
                            <tr>
                                <td><strong>Storage</strong></td>
                                <td>Refrigerate at 2-4°C</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Nutrition Facts Tab -->
                <div class="tab-pane fade" id="nutrition" role="tabpanel">
                    <h4 class="mb-3">Nutrition Facts</h4>
                    <div class="card">
                        <div class="card-body">
                            <p class="text-muted mb-3">Per 100g serving</p>
                            <table class="nutrition-table">
                                <tr>
                                    <td><strong>Calories</strong></td>
                                    <td class="text-end">165 kcal</td>
                                </tr>
                                <tr>
                                    <td><strong>Protein</strong></td>
                                    <td class="text-end">31g</td>
                                </tr>
                                <tr>
                                    <td><strong>Total Fat</strong></td>
                                    <td class="text-end">3.6g</td>
                                </tr>
                                <tr>
                                    <td>&nbsp;&nbsp;Saturated Fat</td>
                                    <td class="text-end">1.0g</td>
                                </tr>
                                <tr>
                                    <td><strong>Cholesterol</strong></td>
                                    <td class="text-end">85mg</td>
                                </tr>
                                <tr>
                                    <td><strong>Sodium</strong></td>
                                    <td class="text-end">74mg</td>
                                </tr>
                                <tr>
                                    <td><strong>Total Carbohydrates</strong></td>
                                    <td class="text-end">0g</td>
                                </tr>
                                <tr>
                                    <td><strong>Dietary Fiber</strong></td>
                                    <td class="text-end">0g</td>
                                </tr>
                                <tr>
                                    <td><strong>Sugars</strong></td>
                                    <td class="text-end">0g</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Reviews Tab -->
                <div class="tab-pane fade" id="reviews" role="tabpanel">
                    <h4 class="mb-4">Customer Reviews</h4>
                    
                    <div class="mb-4">
                        <div class="row">
                            <div class="col-md-4 text-center mb-3">
                                <div class="display-4 fw-bold">4.7</div>
                                <div class="mb-2">
                                    <i class="fas fa-star text-warning"></i>
                                    <i class="fas fa-star text-warning"></i>
                                    <i class="fas fa-star text-warning"></i>
                                    <i class="fas fa-star text-warning"></i>
                                    <i class="fas fa-star-half-alt text-warning"></i>
                                </div>
                                <p class="text-muted">Based on 125 reviews</p>
                            </div>
                            <div class="col-md-8">
                                <div class="mb-2">
                                    <div class="d-flex align-items-center gap-2">
                                        <small>5 stars</small>
                                        <div class="progress flex-fill" style="height: 8px;">
                                            <div class="progress-bar" style="width: 65%"></div>
                                        </div>
                                        <small>65%</small>
                                    </div>
                                </div>
                                <div class="mb-2">
                                    <div class="d-flex align-items-center gap-2">
                                        <small>4 stars</small>
                                        <div class="progress flex-fill" style="height: 8px;">
                                            <div class="progress-bar" style="width: 25%"></div>
                                        </div>
                                        <small>25%</small>
                                    </div>
                                </div>
                                <div class="mb-2">
                                    <div class="d-flex align-items-center gap-2">
                                        <small>3 stars</small>
                                        <div class="progress flex-fill" style="height: 8px;">
                                            <div class="progress-bar" style="width: 7%"></div>
                                        </div>
                                        <small>7%</small>
                                    </div>
                                </div>
                                <div class="mb-2">
                                    <div class="d-flex align-items-center gap-2">
                                        <small>2 stars</small>
                                        <div class="progress flex-fill" style="height: 8px;">
                                            <div class="progress-bar" style="width: 2%"></div>
                                        </div>
                                        <small>2%</small>
                                    </div>
                                </div>
                                <div class="mb-2">
                                    <div class="d-flex align-items-center gap-2">
                                        <small>1 star</small>
                                        <div class="progress flex-fill" style="height: 8px;">
                                            <div class="progress-bar" style="width: 1%"></div>
                                        </div>
                                        <small>1%</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Review List -->
                    <div class="review-list">
                        <div class="card mb-3">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <strong>John Restaurant</strong>
                                        <div class="mb-1">
                                            <i class="fas fa-star text-warning"></i>
                                            <i class="fas fa-star text-warning"></i>
                                            <i class="fas fa-star text-warning"></i>
                                            <i class="fas fa-star text-warning"></i>
                                            <i class="fas fa-star text-warning"></i>
                                        </div>
                                    </div>
                                    <small class="text-muted">2 days ago</small>
                                </div>
                                <p class="mb-0">Excellent quality chicken breast. Very fresh and tender. Will definitely order again!</p>
                                <div class="mt-2">
                                    <span class="badge bg-success">Verified Purchase</span>
                                </div>
                            </div>
                        </div>

                        <div class="card mb-3">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <strong>Sarah's Cafe</strong>
                                        <div class="mb-1">
                                            <i class="fas fa-star text-warning"></i>
                                            <i class="fas fa-star text-warning"></i>
                                            <i class="fas fa-star text-warning"></i>
                                            <i class="fas fa-star text-warning"></i>
                                            <i class="far fa-star text-warning"></i>
                                        </div>
                                    </div>
                                    <small class="text-muted">1 week ago</small>
                                </div>
                                <p class="mb-0">Good quality, fast delivery. Price is competitive for organic chicken.</p>
                                <div class="mt-2">
                                    <span class="badge bg-success">Verified Purchase</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- FAQs Tab -->
                <div class="tab-pane fade" id="faq" role="tabpanel">
                    <h4 class="mb-4">Frequently Asked Questions</h4>
                    
                    <div class="accordion" id="faqAccordion">
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                    Is this chicken halal certified?
                                </button>
                            </h2>
                            <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    Yes, this product is halal certified by MUIS (Majlis Ugama Islam Singapura).
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                    What is the minimum order quantity?
                                </button>
                            </h2>
                            <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    The minimum order quantity is 10 units (packs).
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                                    How long does delivery take?
                                </button>
                            </h2>
                            <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    We offer same-day, next-day, standard (3-5 days), and economy (7-10 days) delivery options. Delivery time depends on your selected option.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Related Products -->
    <div class="row mt-5">
        <div class="col-12">
            <h3 class="mb-4">You May Also Like</h3>
            <div class="row g-4">
                <!-- Related Product Cards -->
                <div class="col-md-3">
                    <div class="card product-card">
                        <div class="product-image bg-light d-flex align-items-center justify-content-center" style="height: 200px;">
                            <i class="fas fa-drumstick-bite fa-4x text-muted"></i>
                        </div>
                        <div class="card-body">
                            <h6 class="card-title">Chicken Thighs</h6>
                            <p class="text-muted small mb-2">Fresh Farm Co.</p>
                            <div class="price-comparison mb-2">
                                <span class="price-current">$18.00</span>
                                <span class="price-original">$22.00</span>
                            </div>
                            <button class="btn btn-primary btn-sm w-100">Add to Cart</button>
                        </div>
                    </div>
                </div>
                <!-- Add more related products -->
            </div>
        </div>
    </div>
</div>
@endsection

