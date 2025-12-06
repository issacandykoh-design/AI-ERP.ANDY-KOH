<!-- CHECKOUT PAGE PROTOTYPE - HARDCODED DATA -->
<div class="content-wrapper">
    
    <div class="mb-4">
        <h1 class="f-28 font-weight-bold text-darkest-grey mb-2">Checkout</h1>
        <div class="progress mb-4">
            <div class="progress-bar" role="progressbar" style="width: 66%;" aria-valuenow="66" aria-valuemin="0" aria-valuemax="100">
                Step 2 of 3: Review & Payment
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Left Column: Order Details -->
        <div class="col-lg-8">
            <!-- Delivery Address -->
            <div class="bg-white rounded b-shadow-4 p-4 mb-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h3 class="f-18 font-weight-bold mb-0">Delivery Address</h3>
                    <button class="btn btn-sm btn-outline-primary">Change</button>
                </div>
                <div class="border rounded p-3">
                    <strong>John Doe</strong><br>
                    ABC Restaurant<br>
                    123 Main Street<br>
                    Singapore 123456<br>
                    <i class="fa fa-phone"></i> +65 1234 5678
                </div>
            </div>

            <!-- Order Items by Merchant -->
            <div class="bg-white rounded b-shadow-4 p-4 mb-4">
                <h3 class="f-18 font-weight-bold mb-3">Order Items</h3>
                
                <!-- Merchant 1 -->
                <div class="mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h5 class="mb-0">
                            <i class="fa fa-store"></i> Fresh Farm Co.
                            <span class="badge badge-success">Verified</span>
                        </h5>
                        <span class="text-muted">2 items</span>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Quantity</th>
                                    <th class="text-right">Price</th>
                                    <th class="text-right">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Premium Organic Tomatoes (1kg)</td>
                                    <td>15</td>
                                    <td class="text-right">$12.50</td>
                                    <td class="text-right font-weight-bold">$187.50</td>
                                </tr>
                                <tr>
                                    <td>Organic Carrots (500g)</td>
                                    <td>5</td>
                                    <td class="text-right">$8.90</td>
                                    <td class="text-right font-weight-bold">$44.50</td>
                                </tr>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="3" class="text-right"><strong>Merchant Subtotal:</strong></td>
                                    <td class="text-right font-weight-bold">$232.00</td>
                                </tr>
                                <tr>
                                    <td colspan="3" class="text-right"><small>Delivery (Standard):</small></td>
                                    <td class="text-right"><small>$5.00</small></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                <!-- Merchant 2 -->
                <div class="mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h5 class="mb-0">
                            <i class="fa fa-store"></i> Coffee Masters
                            <span class="badge badge-success">Verified</span>
                        </h5>
                        <span class="text-muted">1 item</span>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Quantity</th>
                                    <th class="text-right">Price</th>
                                    <th class="text-right">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Artisan Coffee Beans (1kg)</td>
                                    <td>2</td>
                                    <td class="text-right">$28.90</td>
                                    <td class="text-right font-weight-bold">$57.80</td>
                                </tr>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="3" class="text-right"><strong>Merchant Subtotal:</strong></td>
                                    <td class="text-right font-weight-bold">$57.80</td>
                                </tr>
                                <tr>
                                    <td colspan="3" class="text-right"><small>Delivery (Express):</small></td>
                                    <td class="text-right"><small>$8.00</small></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                <!-- Merchant 3 -->
                <div class="mb-0">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h5 class="mb-0">
                            <i class="fa fa-store"></i> Italian Delights
                            <span class="badge badge-success">Verified</span>
                        </h5>
                        <span class="text-muted">2 items</span>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Quantity</th>
                                    <th class="text-right">Price</th>
                                    <th class="text-right">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Vegetable Starter Pack (Combo)</td>
                                    <td>1</td>
                                    <td class="text-right">$35.00</td>
                                    <td class="text-right font-weight-bold">$35.00</td>
                                </tr>
                                <tr>
                                    <td>Gourmet Pasta Set (5 Varieties)</td>
                                    <td>1</td>
                                    <td class="text-right">$45.00</td>
                                    <td class="text-right font-weight-bold">$45.00</td>
                                </tr>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="3" class="text-right"><strong>Merchant Subtotal:</strong></td>
                                    <td class="text-right font-weight-bold">$80.00</td>
                                </tr>
                                <tr>
                                    <td colspan="3" class="text-right"><small>Delivery (Standard):</small></td>
                                    <td class="text-right"><small>$6.00</small></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Delivery Options -->
            <div class="bg-white rounded b-shadow-4 p-4 mb-4">
                <h3 class="f-18 font-weight-bold mb-3">Delivery Options</h3>
                
                <!-- Merchant 1 Delivery -->
                <div class="mb-3">
                    <h5 class="f-14 font-weight-bold mb-2">Fresh Farm Co.</h5>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="delivery1" id="del1-1" checked>
                        <label class="form-check-label" for="del1-1">
                            <strong>Standard Delivery</strong> - $5.00
                            <br><small class="text-muted">3-5 business days</small>
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="delivery1" id="del1-2">
                        <label class="form-check-label" for="del1-2">
                            <strong>Express Delivery</strong> - $12.00
                            <br><small class="text-muted">1-2 business days</small>
                        </label>
                    </div>
                </div>

                <!-- Merchant 2 Delivery -->
                <div class="mb-3">
                    <h5 class="f-14 font-weight-bold mb-2">Coffee Masters</h5>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="delivery2" id="del2-1">
                        <label class="form-check-label" for="del2-1">
                            <strong>Standard Delivery</strong> - $5.00
                            <br><small class="text-muted">3-5 business days</small>
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="delivery2" id="del2-2" checked>
                        <label class="form-check-label" for="del2-2">
                            <strong>Express Delivery</strong> - $8.00
                            <br><small class="text-muted">1-2 business days</small>
                        </label>
                    </div>
                </div>

                <!-- Merchant 3 Delivery -->
                <div class="mb-0">
                    <h5 class="f-14 font-weight-bold mb-2">Italian Delights</h5>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="delivery3" id="del3-1" checked>
                        <label class="form-check-label" for="del3-1">
                            <strong>Standard Delivery</strong> - $6.00
                            <br><small class="text-muted">3-5 business days</small>
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="delivery3" id="del3-2">
                        <label class="form-check-label" for="del3-2">
                            <strong>Express Delivery</strong> - $12.00
                            <br><small class="text-muted">1-2 business days</small>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Payment Method -->
            <div class="bg-white rounded b-shadow-4 p-4 mb-4">
                <h3 class="f-18 font-weight-bold mb-3">Payment Method</h3>
                
                <!-- Payment Options -->
                <div class="mb-3">
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="radio" name="payment" id="pay-card" checked>
                        <label class="form-check-label" for="pay-card">
                            <div class="d-flex align-items-center">
                                <i class="fa fa-credit-card fa-2x mr-3"></i>
                                <div>
                                    <strong>Credit / Debit Card</strong>
                                    <br><small class="text-muted">Visa, Mastercard, Amex</small>
                                </div>
                            </div>
                        </label>
                    </div>

                    <div id="card-details" class="ml-5 mb-3">
                        <div class="row">
                            <div class="col-12 mb-3">
                                <label>Card Number</label>
                                <input type="text" class="form-control" placeholder="1234 5678 9012 3456" value="1234 5678 9012 3456">
                            </div>
                            <div class="col-6 mb-3">
                                <label>Expiry Date</label>
                                <input type="text" class="form-control" placeholder="MM/YY" value="12/25">
                            </div>
                            <div class="col-6 mb-3">
                                <label>CVV</label>
                                <input type="text" class="form-control" placeholder="123" value="123">
                            </div>
                            <div class="col-12">
                                <label>Cardholder Name</label>
                                <input type="text" class="form-control" placeholder="John Doe" value="John Doe">
                            </div>
                        </div>
                    </div>

                    <div class="form-check mb-3">
                        <input class="form-check-input" type="radio" name="payment" id="pay-paynow">
                        <label class="form-check-label" for="pay-paynow">
                            <div class="d-flex align-items-center">
                                <i class="fa fa-mobile-alt fa-2x mr-3"></i>
                                <div>
                                    <strong>PayNow</strong>
                                    <br><small class="text-muted">Instant payment via mobile</small>
                                </div>
                            </div>
                        </label>
                    </div>

                    <div class="form-check mb-3">
                        <input class="form-check-input" type="radio" name="payment" id="pay-dbs">
                        <label class="form-check-label" for="pay-dbs">
                            <div class="d-flex align-items-center">
                                <i class="fa fa-wallet fa-2x mr-3"></i>
                                <div>
                                    <strong>DBS PayLah!</strong>
                                    <br><small class="text-muted">Pay via DBS PayLah!</small>
                                </div>
                            </div>
                        </label>
                    </div>

                    <div class="form-check mb-3">
                        <input class="form-check-input" type="radio" name="payment" id="pay-grab">
                        <label class="form-check-label" for="pay-grab">
                            <div class="d-flex align-items-center">
                                <i class="fa fa-mobile-alt fa-2x mr-3"></i>
                                <div>
                                    <strong>GrabPay</strong>
                                    <br><small class="text-muted">Pay via GrabPay wallet</small>
                                </div>
                            </div>
                        </label>
                    </div>

                    <div class="form-check mb-3">
                        <input class="form-check-input" type="radio" name="payment" id="pay-credit">
                        <label class="form-check-label" for="pay-credit">
                            <div class="d-flex align-items-center">
                                <i class="fa fa-calendar-alt fa-2x mr-3"></i>
                                <div>
                                    <strong>Net 30 (Credit Terms)</strong>
                                    <br><small class="text-muted">Pay within 30 days</small>
                                    <br><small class="text-success">✓ Approved for your account</small>
                                </div>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Payment Comparison -->
                <div class="alert alert-info">
                    <h6 class="font-weight-bold mb-2">Payment Comparison:</h6>
                    <div class="row">
                        <div class="col-4 text-center">
                            <div class="badge badge-success p-2 mb-1">Cheapest</div>
                            <br><small>PayNow</small>
                        </div>
                        <div class="col-4 text-center">
                            <div class="badge badge-primary p-2 mb-1">Fastest</div>
                            <br><small>PayNow</small>
                        </div>
                        <div class="col-4 text-center">
                            <div class="badge badge-warning p-2 mb-1">Most Secure</div>
                            <br><small>Credit Card</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Order Notes -->
            <div class="bg-white rounded b-shadow-4 p-4 mb-4">
                <h3 class="f-18 font-weight-bold mb-3">Order Notes</h3>
                <textarea class="form-control" rows="3" placeholder="Special delivery instructions or notes..."></textarea>
            </div>
        </div>

        <!-- Right Column: Order Summary -->
        <div class="col-lg-4">
            <div class="bg-white rounded b-shadow-4 p-4 sticky-top" style="top: 20px;">
                <h3 class="f-18 font-weight-bold mb-3">Order Summary</h3>
                
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
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-dark-grey">Platform Fee:</span>
                        <span class="font-weight-bold">$3.70</span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between mb-3">
                        <span class="f-18 font-weight-bold">Total:</span>
                        <span class="f-24 font-weight-bold text-primary">$419.72</span>
                    </div>
                </div>

                <!-- Savings -->
                <div class="alert alert-success mb-3">
                    <div class="d-flex justify-content-between">
                        <span><strong>You Saved:</strong></span>
                        <span class="font-weight-bold">$7.25</span>
                    </div>
                    <small class="text-muted">
                        Volume discounts + Combo savings
                    </small>
                </div>

                <!-- Approval Required -->
                <div class="alert alert-warning mb-3">
                    <i class="fa fa-exclamation-triangle"></i>
                    <strong>Approval Required</strong>
                    <p class="mb-0 small">This order requires approval before processing.</p>
                </div>

                <!-- Terms & Conditions -->
                <div class="mb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="terms">
                        <label class="form-check-label small" for="terms">
                            I agree to the <a href="#">Terms & Conditions</a> and <a href="#">Privacy Policy</a>
                        </label>
                    </div>
                </div>

                <!-- Place Order Button -->
                <button class="btn btn-primary btn-lg btn-block mb-3">
                    <i class="fa fa-lock"></i> Place Order
                </button>

                <!-- Security -->
                <div class="text-center">
                    <small class="text-muted">
                        <i class="fa fa-shield-alt"></i> Secure Checkout | SSL Encrypted
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Show/hide card details based on payment method
$('input[name="payment"]').change(function() {
    if ($(this).attr('id') === 'pay-card') {
        $('#card-details').show();
    } else {
        $('#card-details').hide();
    }
});
</script>

<style>
.sticky-top {
    position: sticky;
}
</style>

