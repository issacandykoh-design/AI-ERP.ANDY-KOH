@extends('prototype.layouts.app')

@section('title', 'Shopping Cart - B2B Marketplace')

@push('styles')
<style>
    .cart-item {
        border-bottom: 1px solid var(--gray-200);
        padding: 1.5rem 0;
    }
    .cart-item:last-child {
        border-bottom: none;
    }
    .cart-summary {
        background: var(--gray-50);
        border-radius: 8px;
        padding: 1.5rem;
        position: sticky;
        top: 100px;
    }
    .merchant-group {
        background: white;
        border-radius: 8px;
        padding: 1rem;
        margin-bottom: 1.5rem;
        border: 1px solid var(--gray-200);
    }
    .merchant-header {
        border-bottom: 2px solid var(--gray-200);
        padding-bottom: 0.75rem;
        margin-bottom: 1rem;
    }
</style>
@endpush

@section('content')
<div class="container my-5">
    <h1 class="h2 fw-bold mb-4">Shopping Cart</h1>
    
    <div class="row">
        <!-- Cart Items -->
        <div class="col-lg-8">
            <!-- Merchant 1 Group -->
            <div class="merchant-group">
                <div class="merchant-header d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0">
                            <i class="fas fa-store text-primary"></i> Fresh Farm Co.
                        </h5>
                        <small class="text-muted">
                            <span class="badge bg-success">Verified</span>
                            <i class="fas fa-star text-warning"></i> 4.8
                        </small>
                    </div>
                    <small class="text-muted">Order will ship separately</small>
                </div>

                <!-- Item 1 -->
                <div class="cart-item">
                    <div class="row align-items-center">
                        <div class="col-md-2">
                            <div class="bg-light rounded d-flex align-items-center justify-content-center" style="height: 100px;">
                                <i class="fas fa-drumstick-bite fa-3x text-muted"></i>
                            </div>
                        </div>
                        <div class="col-md-5">
                            <h6 class="mb-1">Fresh Organic Chicken Breast</h6>
                            <p class="text-muted small mb-2">SKU: CHK-BRST-001</p>
                            <div class="mb-2">
                                <span class="badge bg-success">Halal</span>
                                <span class="badge bg-info">Organic</span>
                            </div>
                            <div class="price-comparison">
                                <span class="price-current">$20.00</span>
                                <span class="price-original">$25.00</span>
                                <span class="savings-badge">Save $5</span>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small">Quantity</label>
                            <div class="input-group input-group-sm">
                                <button class="btn btn-outline-secondary" type="button">-</button>
                                <input type="number" class="form-control text-center" value="5" min="1">
                                <button class="btn btn-outline-secondary" type="button">+</button>
                            </div>
                        </div>
                        <div class="col-md-2 text-end">
                            <div class="mb-2">
                                <strong class="h5">$100.00</strong>
                            </div>
                            <small class="text-muted">$20.00 × 5</small>
                        </div>
                        <div class="col-md-1 text-end">
                            <button class="btn btn-link text-danger p-0">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Item 2 -->
                <div class="cart-item">
                    <div class="row align-items-center">
                        <div class="col-md-2">
                            <div class="bg-light rounded d-flex align-items-center justify-content-center" style="height: 100px;">
                                <i class="fas fa-carrot fa-3x text-muted"></i>
                            </div>
                        </div>
                        <div class="col-md-5">
                            <h6 class="mb-1">Organic Carrots (5kg)</h6>
                            <p class="text-muted small mb-2">SKU: CRT-ORG-001</p>
                            <div class="mb-2">
                                <span class="badge bg-info">Organic</span>
                            </div>
                            <div class="price-comparison">
                                <span class="price-current">$12.00</span>
                                <span class="price-original">$15.00</span>
                                <span class="savings-badge">Save $3</span>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small">Quantity</label>
                            <div class="input-group input-group-sm">
                                <button class="btn btn-outline-secondary" type="button">-</button>
                                <input type="number" class="form-control text-center" value="3" min="1">
                                <button class="btn btn-outline-secondary" type="button">+</button>
                            </div>
                        </div>
                        <div class="col-md-2 text-end">
                            <div class="mb-2">
                                <strong class="h5">$36.00</strong>
                            </div>
                            <small class="text-muted">$12.00 × 3</small>
                        </div>
                        <div class="col-md-1 text-end">
                            <button class="btn btn-link text-danger p-0">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Merchant Subtotal -->
                <div class="border-top pt-3 mt-3">
                    <div class="row">
                        <div class="col-md-8">
                            <strong>Subtotal (Fresh Farm Co.):</strong>
                        </div>
                        <div class="col-md-4 text-end">
                            <strong>$136.00</strong>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Merchant 2 Group -->
            <div class="merchant-group">
                <div class="merchant-header d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0">
                            <i class="fas fa-fish text-info"></i> Ocean Fresh Seafood
                        </h5>
                        <small class="text-muted">
                            <span class="badge bg-success">Verified</span>
                            <i class="fas fa-star text-warning"></i> 4.7
                        </small>
                    </div>
                    <small class="text-muted">Order will ship separately</small>
                </div>

                <!-- Item 3 -->
                <div class="cart-item">
                    <div class="row align-items-center">
                        <div class="col-md-2">
                            <div class="bg-light rounded d-flex align-items-center justify-content-center" style="height: 100px;">
                                <i class="fas fa-fish fa-3x text-muted"></i>
                            </div>
                        </div>
                        <div class="col-md-5">
                            <h6 class="mb-1">Fresh Salmon Fillet</h6>
                            <p class="text-muted small mb-2">SKU: SLM-FLT-001</p>
                            <div class="mb-2">
                                <span class="badge bg-success">Halal</span>
                            </div>
                            <div class="price-comparison">
                                <span class="price-current">$35.00</span>
                                <span class="price-original">$40.00</span>
                                <span class="savings-badge">Save $5</span>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small">Quantity</label>
                            <div class="input-group input-group-sm">
                                <button class="btn btn-outline-secondary" type="button">-</button>
                                <input type="number" class="form-control text-center" value="2" min="1">
                                <button class="btn btn-outline-secondary" type="button">+</button>
                            </div>
                        </div>
                        <div class="col-md-2 text-end">
                            <div class="mb-2">
                                <strong class="h5">$70.00</strong>
                            </div>
                            <small class="text-muted">$35.00 × 2</small>
                        </div>
                        <div class="col-md-1 text-end">
                            <button class="btn btn-link text-danger p-0">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Merchant Subtotal -->
                <div class="border-top pt-3 mt-3">
                    <div class="row">
                        <div class="col-md-8">
                            <strong>Subtotal (Ocean Fresh Seafood):</strong>
                        </div>
                        <div class="col-md-4 text-end">
                            <strong>$70.00</strong>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Continue Shopping -->
            <div class="mt-4">
                <a href="/prototype/marketplace/products" class="btn btn-outline-primary">
                    <i class="fas fa-arrow-left"></i> Continue Shopping
                </a>
            </div>
        </div>

        <!-- Order Summary -->
        <div class="col-lg-4">
            <div class="cart-summary">
                <h5 class="fw-bold mb-4">Order Summary</h5>
                
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Subtotal (2 merchants)</span>
                        <strong>$206.00</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Shipping (Fresh Farm Co.)</span>
                        <span>$8.00</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Shipping (Ocean Fresh)</span>
                        <span>$5.00</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2 text-success">
                        <span>Total Savings</span>
                        <strong>-$13.00</strong>
                    </div>
                </div>

                <hr>

                <div class="mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0">Total</h5>
                        <h4 class="mb-0 text-primary">$214.00</h4>
                    </div>
                    <small class="text-muted">2 separate shipments</small>
                </div>

                <!-- Delivery Info -->
                <div class="alert alert-info mb-4">
                    <h6 class="alert-heading"><i class="fas fa-info-circle"></i> Multi-Merchant Order</h6>
                    <p class="small mb-0">Your order contains items from 2 different merchants. Each merchant will ship separately, and you'll receive individual tracking numbers.</p>
                </div>

                <!-- Checkout Button -->
                <a href="/prototype/marketplace/checkout" class="btn btn-primary btn-lg w-100 mb-3">
                    <i class="fas fa-lock"></i> Proceed to Checkout
                </a>

                <!-- Payment Methods -->
                <div class="text-center">
                    <small class="text-muted d-block mb-2">We Accept:</small>
                    <div class="d-flex justify-content-center gap-2 flex-wrap">
                        <i class="fab fa-cc-visa fa-2x text-primary"></i>
                        <i class="fab fa-cc-mastercard fa-2x text-danger"></i>
                        <i class="fab fa-cc-paypal fa-2x text-info"></i>
                        <span class="badge bg-dark">PayNow</span>
                        <span class="badge bg-primary">DBS PayLah!</span>
                    </div>
                </div>

                <!-- Security Badge -->
                <div class="text-center mt-4">
                    <small class="text-muted">
                        <i class="fas fa-lock"></i> Secure Checkout
                    </small>
                </div>
            </div>

            <!-- Trust Badges -->
            <div class="card mt-4">
                <div class="card-body text-center">
                    <h6 class="mb-3">Why Shop With Us?</h6>
                    <div class="row g-3">
                        <div class="col-6">
                            <i class="fas fa-shield-alt fa-2x text-success mb-2"></i>
                            <p class="small mb-0">Verified Merchants</p>
                        </div>
                        <div class="col-6">
                            <i class="fas fa-truck fa-2x text-primary mb-2"></i>
                            <p class="small mb-0">Fast Delivery</p>
                        </div>
                        <div class="col-6">
                            <i class="fas fa-undo fa-2x text-info mb-2"></i>
                            <p class="small mb-0">Easy Returns</p>
                        </div>
                        <div class="col-6">
                            <i class="fas fa-headset fa-2x text-warning mb-2"></i>
                            <p class="small mb-0">24/7 Support</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

