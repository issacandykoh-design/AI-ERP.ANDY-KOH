@extends('prototype.layouts.app')

@section('content')
<!-- SHOPPING CART PAGE PROTOTYPE - MULTI-MERCHANT - HARDCODED DATA -->
<div class="content-wrapper">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="f-28 font-weight-bold text-darkest-grey mb-0">Shopping Cart</h1>
        <div>
            <span class="badge badge-primary p-2">
                <i class="fa fa-shopping-cart"></i> 5 items from 3 merchants
            </span>
        </div>
    </div>

    <!-- Multi-Merchant Cart -->
    <div class="bg-white rounded b-shadow-4 mb-4">
        <div class="p-4 border-bottom-grey">
            <h3 class="f-21 font-weight-bold text-darkest-grey mb-0">
                <i class="fa fa-store"></i> Merchant: Fresh Farm Co.
                <span class="badge badge-success ml-2">Verified</span>
            </h3>
        </div>

        <!-- Cart Items from Merchant 1 -->
        <div class="p-4">
            <!-- Item 1 -->
            <div class="d-flex border-bottom pb-4 mb-4">
                <div class="mr-4">
                    <img src="https://via.placeholder.com/150x150?text=Product" 
                         class="img-thumbnail" 
                         style="width: 120px; height: 120px; object-fit: cover;"
                         alt="Product">
                </div>
                <div class="flex-grow-1">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h5 class="f-18 font-weight-bold mb-1">Premium Organic Tomatoes (1kg)</h5>
                            <p class="text-muted mb-2 small">SKU: TOM-001 | Category: Fresh Produce</p>
                            
                            <!-- Pricing -->
                            <div class="mb-2">
                                <div class="d-flex align-items-baseline">
                                    <span class="text-dark-grey f-14 mr-2">Your Price:</span>
                                    <span class="f-20 font-weight-bold text-darkest-grey">$12.50</span>
                                    <span class="text-muted text-decoration-line-through ml-2 f-14">$14.75</span>
                                    <span class="badge badge-success ml-2">Save $2.25</span>
                                </div>
                                <div class="mt-1">
                                    <small class="text-success">
                                        <i class="fa fa-tag"></i> Volume discount: Buy 10+ for $11.50 each
                                    </small>
                                </div>
                            </div>

                            <!-- Stock Status -->
                            <div class="mb-2">
                                <span class="badge badge-success">
                                    <i class="fa fa-check-circle"></i> In Stock (234 units)
                                </span>
                            </div>
                        </div>
                        <div class="text-right">
                            <button class="btn btn-sm btn-outline-danger">
                                <i class="fa fa-trash"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Quantity & Actions -->
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <div class="d-flex align-items-center">
                            <label class="mr-2 mb-0">Quantity:</label>
                            <div class="input-group" style="width: 120px;">
                                <button class="btn btn-outline-secondary btn-sm" type="button">-</button>
                                <input type="number" class="form-control text-center" value="15" min="10">
                                <button class="btn btn-outline-secondary btn-sm" type="button">+</button>
                            </div>
                            <small class="text-muted ml-2">Min: 10 units</small>
                        </div>
                        <div>
                            <span class="f-20 font-weight-bold text-darkest-grey">
                                Subtotal: $187.50
                            </span>
                            <div class="text-right">
                                <small class="text-muted">$12.50 × 15 units</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Item 2 -->
            <div class="d-flex border-bottom pb-4 mb-4">
                <div class="mr-4">
                    <img src="https://via.placeholder.com/150x150?text=Product" 
                         class="img-thumbnail" 
                         style="width: 120px; height: 120px; object-fit: cover;"
                         alt="Product">
                </div>
                <div class="flex-grow-1">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h5 class="f-18 font-weight-bold mb-1">Organic Carrots (500g)</h5>
                            <p class="text-muted mb-2 small">SKU: CAR-002 | Category: Fresh Produce</p>
                            
                            <div class="mb-2">
                                <div class="d-flex align-items-baseline">
                                    <span class="text-dark-grey f-14 mr-2">Your Price:</span>
                                    <span class="f-20 font-weight-bold text-darkest-grey">$8.90</span>
                                </div>
                            </div>

                            <div class="mb-2">
                                <span class="badge badge-success">
                                    <i class="fa fa-check-circle"></i> In Stock
                                </span>
                            </div>
                        </div>
                        <div class="text-right">
                            <button class="btn btn-sm btn-outline-danger">
                                <i class="fa fa-trash"></i>
                            </button>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <div class="d-flex align-items-center">
                            <label class="mr-2 mb-0">Quantity:</label>
                            <div class="input-group" style="width: 120px;">
                                <button class="btn btn-outline-secondary btn-sm" type="button">-</button>
                                <input type="number" class="form-control text-center" value="5" min="1">
                                <button class="btn btn-outline-secondary btn-sm" type="button">+</button>
                            </div>
                        </div>
                        <div>
                            <span class="f-20 font-weight-bold text-darkest-grey">
                                Subtotal: $44.50
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Merchant Subtotal -->
            <div class="bg-light rounded p-3 mb-0">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <strong>Merchant Subtotal:</strong>
                        <span class="text-muted ml-2">2 items</span>
                    </div>
                    <div>
                        <span class="f-24 font-weight-bold text-darkest-grey">$232.00</span>
                    </div>
                </div>
                <div class="mt-2">
                    <small class="text-dark-grey">
                        <i class="fa fa-truck"></i> Delivery: $5.00 (Standard) | 
                        <a href="#" class="text-primary">Change</a>
                    </small>
                </div>
            </div>
        </div>
    </div>

    <!-- Merchant 2 -->
    <div class="bg-white rounded b-shadow-4 mb-4">
        <div class="p-4 border-bottom-grey">
            <h3 class="f-21 font-weight-bold text-darkest-grey mb-0">
                <i class="fa fa-store"></i> Merchant: Coffee Masters
                <span class="badge badge-success ml-2">Verified</span>
            </h3>
        </div>

        <div class="p-4">
            <!-- Item 3 -->
            <div class="d-flex border-bottom pb-4 mb-4">
                <div class="mr-4">
                    <img src="https://via.placeholder.com/150x150?text=Product" 
                         class="img-thumbnail" 
                         style="width: 120px; height: 120px; object-fit: cover;"
                         alt="Product">
                </div>
                <div class="flex-grow-1">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h5 class="f-18 font-weight-bold mb-1">Artisan Coffee Beans (1kg)</h5>
                            <p class="text-muted mb-2 small">SKU: COF-003 | Category: Beverages</p>
                            
                            <div class="mb-2">
                                <div class="d-flex align-items-baseline">
                                    <span class="text-dark-grey f-14 mr-2">Your Price:</span>
                                    <span class="f-20 font-weight-bold text-darkest-grey">$28.90</span>
                                </div>
                            </div>

                            <div class="mb-2">
                                <span class="badge badge-success">
                                    <i class="fa fa-check-circle"></i> In Stock
                                </span>
                            </div>
                        </div>
                        <div class="text-right">
                            <button class="btn btn-sm btn-outline-danger">
                                <i class="fa fa-trash"></i>
                            </button>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <div class="d-flex align-items-center">
                            <label class="mr-2 mb-0">Quantity:</label>
                            <div class="input-group" style="width: 120px;">
                                <button class="btn btn-outline-secondary btn-sm" type="button">-</button>
                                <input type="number" class="form-control text-center" value="2" min="1">
                                <button class="btn btn-outline-secondary btn-sm" type="button">+</button>
                            </div>
                        </div>
                        <div>
                            <span class="f-20 font-weight-bold text-darkest-grey">
                                Subtotal: $57.80
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Merchant Subtotal -->
            <div class="bg-light rounded p-3 mb-0">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <strong>Merchant Subtotal:</strong>
                        <span class="text-muted ml-2">1 item</span>
                    </div>
                    <div>
                        <span class="f-24 font-weight-bold text-darkest-grey">$57.80</span>
                    </div>
                </div>
                <div class="mt-2">
                    <small class="text-dark-grey">
                        <i class="fa fa-truck"></i> Delivery: $8.00 (Express) | 
                        <a href="#" class="text-primary">Change</a>
                    </small>
                </div>
            </div>
        </div>
    </div>

    <!-- Merchant 3 -->
    <div class="bg-white rounded b-shadow-4 mb-4">
        <div class="p-4 border-bottom-grey">
            <h3 class="f-21 font-weight-bold text-darkest-grey mb-0">
                <i class="fa fa-store"></i> Merchant: Italian Delights
                <span class="badge badge-success ml-2">Verified</span>
            </h3>
        </div>

        <div class="p-4">
            <!-- Item 4 - Combo Product -->
            <div class="d-flex border-bottom pb-4 mb-4">
                <div class="mr-4">
                    <img src="https://via.placeholder.com/150x150?text=Combo" 
                         class="img-thumbnail border-primary" 
                         style="width: 120px; height: 120px; object-fit: cover;"
                         alt="Combo">
                    <span class="badge badge-danger position-absolute">Combo</span>
                </div>
                <div class="flex-grow-1">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h5 class="f-18 font-weight-bold mb-1">Vegetable Starter Pack</h5>
                            <p class="text-muted mb-2 small">Combo Deal | Includes: Tomatoes + Carrots + Onions + Potatoes</p>
                            
                            <div class="mb-2">
                                <div class="d-flex align-items-baseline">
                                    <span class="text-dark-grey f-14 mr-2">Combo Price:</span>
                                    <span class="f-20 font-weight-bold text-darkest-grey">$35.00</span>
                                    <span class="text-muted text-decoration-line-through ml-2 f-14">$40.00</span>
                                    <span class="badge badge-success ml-2">Save $5.00</span>
                                </div>
                            </div>

                            <div class="mb-2">
                                <span class="badge badge-success">
                                    <i class="fa fa-check-circle"></i> In Stock
                                </span>
                            </div>
                        </div>
                        <div class="text-right">
                            <button class="btn btn-sm btn-outline-danger">
                                <i class="fa fa-trash"></i>
                            </button>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <div class="d-flex align-items-center">
                            <label class="mr-2 mb-0">Quantity:</label>
                            <div class="input-group" style="width: 120px;">
                                <button class="btn btn-outline-secondary btn-sm" type="button">-</button>
                                <input type="number" class="form-control text-center" value="1" min="1">
                                <button class="btn btn-outline-secondary btn-sm" type="button">+</button>
                            </div>
                        </div>
                        <div>
                            <span class="f-20 font-weight-bold text-darkest-grey">
                                Subtotal: $35.00
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Item 5 -->
            <div class="d-flex pb-4 mb-4">
                <div class="mr-4">
                    <img src="https://via.placeholder.com/150x150?text=Product" 
                         class="img-thumbnail" 
                         style="width: 120px; height: 120px; object-fit: cover;"
                         alt="Product">
                </div>
                <div class="flex-grow-1">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h5 class="f-18 font-weight-bold mb-1">Gourmet Pasta Set (5 Varieties)</h5>
                            <p class="text-muted mb-2 small">SKU: PAS-004 | Category: Packaged Foods</p>
                            
                            <div class="mb-2">
                                <div class="d-flex align-items-baseline">
                                    <span class="text-dark-grey f-14 mr-2">Your Price:</span>
                                    <span class="f-20 font-weight-bold text-darkest-grey">$45.00</span>
                                    <span class="text-muted text-decoration-line-through ml-2 f-14">$55.00</span>
                                </div>
                            </div>

                            <div class="mb-2">
                                <span class="badge badge-success">
                                    <i class="fa fa-check-circle"></i> In Stock
                                </span>
                            </div>
                        </div>
                        <div class="text-right">
                            <button class="btn btn-sm btn-outline-danger">
                                <i class="fa fa-trash"></i>
                            </button>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <div class="d-flex align-items-center">
                            <label class="mr-2 mb-0">Quantity:</label>
                            <div class="input-group" style="width: 120px;">
                                <button class="btn btn-outline-secondary btn-sm" type="button">-</button>
                                <input type="number" class="form-control text-center" value="1" min="1">
                                <button class="btn btn-outline-secondary btn-sm" type="button">+</button>
                            </div>
                        </div>
                        <div>
                            <span class="f-20 font-weight-bold text-darkest-grey">
                                Subtotal: $45.00
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Merchant Subtotal -->
            <div class="bg-light rounded p-3 mb-0">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <strong>Merchant Subtotal:</strong>
                        <span class="text-muted ml-2">2 items</span>
                    </div>
                    <div>
                        <span class="f-24 font-weight-bold text-darkest-grey">$80.00</span>
                    </div>
                </div>
                <div class="mt-2">
                    <small class="text-dark-grey">
                        <i class="fa fa-truck"></i> Delivery: $6.00 (Standard) | 
                        <a href="#" class="text-primary">Change</a>
                    </small>
                </div>
            </div>
        </div>
    </div>

    <!-- Order Summary -->
    <div class="row">
        <div class="col-lg-8">
            <!-- Continue Shopping -->
            <div class="bg-white rounded b-shadow-4 p-4 mb-4">
                <a href="#" class="text-primary">
                    <i class="fa fa-arrow-left"></i> Continue Shopping
                </a>
            </div>

            <!-- Delivery Options Summary -->
            <div class="bg-white rounded b-shadow-4 p-4">
                <h4 class="f-18 font-weight-bold mb-3">Delivery Summary</h4>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Merchant</th>
                                <th>Items</th>
                                <th>Delivery Method</th>
                                <th>Delivery Cost</th>
                                <th>Estimated Delivery</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Fresh Farm Co.</td>
                                <td>2 items</td>
                                <td>Standard</td>
                                <td>$5.00</td>
                                <td>3-5 business days</td>
                            </tr>
                            <tr>
                                <td>Coffee Masters</td>
                                <td>1 item</td>
                                <td>Express</td>
                                <td>$8.00</td>
                                <td>1-2 business days</td>
                            </tr>
                            <tr>
                                <td>Italian Delights</td>
                                <td>2 items</td>
                                <td>Standard</td>
                                <td>$6.00</td>
                                <td>3-5 business days</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Order Summary Card -->
            <div class="bg-white rounded b-shadow-4 p-4 sticky-top" style="top: 20px;">
                <h4 class="f-18 font-weight-bold mb-3">Order Summary</h4>
                
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-dark-grey">Subtotal (5 items):</span>
                        <span class="font-weight-bold">$369.80</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-dark-grey">Delivery:</span>
                        <span class="font-weight-bold">$19.00</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-dark-grey">Tax (GST 7%):</span>
                        <span class="font-weight-bold">$27.22</span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between mb-3">
                        <span class="f-18 font-weight-bold">Total:</span>
                        <span class="f-24 font-weight-bold text-primary">$416.02</span>
                    </div>
                </div>

                <!-- Savings Display -->
                <div class="alert alert-success mb-3">
                    <div class="d-flex justify-content-between">
                        <span><strong>You Saved:</strong></span>
                        <span class="font-weight-bold">$7.25</span>
                    </div>
                    <small class="text-muted">
                        Volume discounts + Combo savings
                    </small>
                </div>

                <!-- Payment Methods -->
                <div class="mb-3">
                    <h5 class="f-14 font-weight-bold mb-2">Payment Methods:</h5>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="radio" name="payment" id="pay1" checked>
                        <label class="form-check-label" for="pay1">
                            Credit Card / Debit Card
                        </label>
                    </div>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="radio" name="payment" id="pay2">
                        <label class="form-check-label" for="pay2">
                            PayNow
                        </label>
                    </div>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="radio" name="payment" id="pay3">
                        <label class="form-check-label" for="pay3">
                            Net 30 (Credit Terms)
                        </label>
                        <small class="text-muted d-block ml-4">Available for approved accounts</small>
                    </div>
                </div>

                <!-- Checkout Button -->
                <button class="btn btn-primary btn-lg btn-block mb-3">
                    <i class="fa fa-lock"></i> Proceed to Checkout
                </button>

                <!-- Security Badge -->
                <div class="text-center">
                    <small class="text-muted">
                        <i class="fa fa-shield-alt"></i> Secure Checkout
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.sticky-top {
    position: sticky;
}
</style>
@endsection

