<!-- ORDER MANAGEMENT PAGE PROTOTYPE - HARDCODED DATA -->
<div class="content-wrapper">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="f-28 font-weight-bold text-darkest-grey mb-0">My Orders</h1>
        <div>
            <button class="btn btn-primary">
                <i class="fa fa-plus"></i> Create Order
            </button>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded b-shadow-4 p-3 mb-4">
        <div class="row">
            <div class="col-md-3">
                <select class="form-control">
                    <option>All Status</option>
                    <option>Pending</option>
                    <option>Approved</option>
                    <option>Processing</option>
                    <option>Shipped</option>
                    <option>Delivered</option>
                    <option>Cancelled</option>
                </select>
            </div>
            <div class="col-md-3">
                <input type="text" class="form-control" placeholder="Search orders..." id="order-search">
            </div>
            <div class="col-md-3">
                <input type="text" class="form-control" placeholder="Date Range" id="date-range">
            </div>
            <div class="col-md-3">
                <button class="btn btn-primary btn-block">Apply Filters</button>
            </div>
        </div>
    </div>

    <!-- Orders List -->
    <div class="bg-white rounded b-shadow-4 mb-4">
        <!-- Order 1 -->
        <div class="border-bottom p-4">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div>
                    <h4 class="f-18 font-weight-bold mb-1">
                        Order #ORD-2024-001234
                        <span class="badge badge-success ml-2">Delivered</span>
                    </h4>
                    <p class="text-muted mb-1">
                        <i class="fa fa-calendar"></i> Placed on: Jan 15, 2024
                        <span class="ml-3"><i class="fa fa-store"></i> 3 merchants</span>
                    </p>
                </div>
                <div class="text-right">
                    <span class="f-20 font-weight-bold text-darkest-grey">$419.72</span>
                    <br><small class="text-muted">Total</small>
                </div>
            </div>

            <!-- Order Items Summary -->
            <div class="mb-3">
                <div class="row">
                    <div class="col-md-4 mb-2">
                        <div class="bg-light rounded p-2">
                            <strong>Fresh Farm Co.</strong>
                            <br><small class="text-muted">2 items • $232.00</small>
                            <br><small class="text-success">
                                <i class="fa fa-check-circle"></i> Delivered
                            </small>
                        </div>
                    </div>
                    <div class="col-md-4 mb-2">
                        <div class="bg-light rounded p-2">
                            <strong>Coffee Masters</strong>
                            <br><small class="text-muted">1 item • $57.80</small>
                            <br><small class="text-success">
                                <i class="fa fa-check-circle"></i> Delivered
                            </small>
                        </div>
                    </div>
                    <div class="col-md-4 mb-2">
                        <div class="bg-light rounded p-2">
                            <strong>Italian Delights</strong>
                            <br><small class="text-muted">2 items • $80.00</small>
                            <br><small class="text-success">
                                <i class="fa fa-check-circle"></i> Delivered
                            </small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <button class="btn btn-sm btn-outline-primary mr-2">
                        <i class="fa fa-eye"></i> View Details
                    </button>
                    <button class="btn btn-sm btn-outline-success mr-2">
                        <i class="fa fa-redo"></i> Reorder
                    </button>
                    <button class="btn btn-sm btn-outline-secondary">
                        <i class="fa fa-file-invoice"></i> Invoice
                    </button>
                </div>
                <div>
                    <button class="btn btn-sm btn-outline-warning">
                        <i class="fa fa-star"></i> Rate & Review
                    </button>
                </div>
            </div>
        </div>

        <!-- Order 2 -->
        <div class="border-bottom p-4">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div>
                    <h4 class="f-18 font-weight-bold mb-1">
                        Order #ORD-2024-001235
                        <span class="badge badge-info ml-2">Processing</span>
                    </h4>
                    <p class="text-muted mb-1">
                        <i class="fa fa-calendar"></i> Placed on: Jan 18, 2024
                        <span class="ml-3"><i class="fa fa-store"></i> 2 merchants</span>
                    </p>
                </div>
                <div class="text-right">
                    <span class="f-20 font-weight-bold text-darkest-grey">$289.50</span>
                    <br><small class="text-muted">Total</small>
                </div>
            </div>

            <!-- Order Items Summary -->
            <div class="mb-3">
                <div class="row">
                    <div class="col-md-6 mb-2">
                        <div class="bg-light rounded p-2">
                            <strong>Fresh Farm Co.</strong>
                            <br><small class="text-muted">3 items • $187.50</small>
                            <br><small class="text-info">
                                <i class="fa fa-truck"></i> Shipped (Tracking: TRK123456)
                            </small>
                        </div>
                    </div>
                    <div class="col-md-6 mb-2">
                        <div class="bg-light rounded p-2">
                            <strong>Coffee Masters</strong>
                            <br><small class="text-muted">2 items • $102.00</small>
                            <br><small class="text-warning">
                                <i class="fa fa-clock"></i> Processing
                            </small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <button class="btn btn-sm btn-outline-primary mr-2">
                        <i class="fa fa-eye"></i> View Details
                    </button>
                    <button class="btn btn-sm btn-outline-info mr-2">
                        <i class="fa fa-truck"></i> Track Order
                    </button>
                    <button class="btn btn-sm btn-outline-danger">
                        <i class="fa fa-times"></i> Cancel Order
                    </button>
                </div>
            </div>
        </div>

        <!-- Order 3 - Pending Approval -->
        <div class="border-bottom p-4">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div>
                    <h4 class="f-18 font-weight-bold mb-1">
                        Order #ORD-2024-001236
                        <span class="badge badge-warning ml-2">Pending Approval</span>
                    </h4>
                    <p class="text-muted mb-1">
                        <i class="fa fa-calendar"></i> Placed on: Jan 20, 2024
                        <span class="ml-3"><i class="fa fa-store"></i> 1 merchant</span>
                    </p>
                    <div class="alert alert-warning mt-2 mb-0 p-2">
                        <i class="fa fa-exclamation-triangle"></i> 
                        This order requires approval from your manager before processing.
                        <br><small>Awaiting approval from: John Manager</small>
                    </div>
                </div>
                <div class="text-right">
                    <span class="f-20 font-weight-bold text-darkest-grey">$1,245.00</span>
                    <br><small class="text-muted">Total</small>
                </div>
            </div>

            <!-- Order Items Summary -->
            <div class="mb-3">
                <div class="bg-light rounded p-2">
                    <strong>Fresh Farm Co.</strong>
                    <br><small class="text-muted">15 items • $1,245.00</small>
                    <br><small class="text-warning">
                        <i class="fa fa-clock"></i> Pending Approval
                    </small>
                </div>
            </div>

            <!-- Actions -->
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <button class="btn btn-sm btn-outline-primary mr-2">
                        <i class="fa fa-eye"></i> View Details
                    </button>
                    <button class="btn btn-sm btn-outline-danger">
                        <i class="fa fa-times"></i> Cancel Order
                    </button>
                </div>
            </div>
        </div>

        <!-- Order 4 - Credit Terms -->
        <div class="p-4">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div>
                    <h4 class="f-18 font-weight-bold mb-1">
                        Order #ORD-2024-001237
                        <span class="badge badge-secondary ml-2">Net 30</span>
                        <span class="badge badge-success ml-2">Delivered</span>
                    </h4>
                    <p class="text-muted mb-1">
                        <i class="fa fa-calendar"></i> Placed on: Jan 10, 2024
                        <span class="ml-3"><i class="fa fa-store"></i> 2 merchants</span>
                    </p>
                    <div class="alert alert-info mt-2 mb-0 p-2">
                        <i class="fa fa-info-circle"></i> 
                        Payment due: Feb 9, 2024 (Net 30)
                        <br><small>Invoice #INV-2024-001237</small>
                    </div>
                </div>
                <div class="text-right">
                    <span class="f-20 font-weight-bold text-darkest-grey">$856.30</span>
                    <br><small class="text-muted">Total</small>
                    <br><small class="text-warning">Due: $856.30</small>
                </div>
            </div>

            <!-- Order Items Summary -->
            <div class="mb-3">
                <div class="row">
                    <div class="col-md-6 mb-2">
                        <div class="bg-light rounded p-2">
                            <strong>Fresh Farm Co.</strong>
                            <br><small class="text-muted">5 items • $456.30</small>
                            <br><small class="text-success">
                                <i class="fa fa-check-circle"></i> Delivered
                            </small>
                        </div>
                    </div>
                    <div class="col-md-6 mb-2">
                        <div class="bg-light rounded p-2">
                            <strong>Coffee Masters</strong>
                            <br><small class="text-muted">3 items • $400.00</small>
                            <br><small class="text-success">
                                <i class="fa fa-check-circle"></i> Delivered
                            </small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <button class="btn btn-sm btn-outline-primary mr-2">
                        <i class="fa fa-eye"></i> View Details
                    </button>
                    <button class="btn btn-sm btn-outline-success mr-2">
                        <i class="fa fa-credit-card"></i> Pay Invoice
                    </button>
                    <button class="btn btn-sm btn-outline-secondary">
                        <i class="fa fa-file-invoice"></i> View Invoice
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Order Details Modal Content (for reference) -->
    <div class="bg-white rounded b-shadow-4 p-4 mb-4" style="display: none;" id="order-details">
        <h3 class="f-18 font-weight-bold mb-3">Order Details</h3>
        
        <!-- Order Info -->
        <div class="row mb-4">
            <div class="col-md-6">
                <h5 class="f-14 font-weight-bold mb-2">Order Information</h5>
                <p class="mb-1"><strong>Order Number:</strong> #ORD-2024-001234</p>
                <p class="mb-1"><strong>Order Date:</strong> Jan 15, 2024</p>
                <p class="mb-1"><strong>Status:</strong> <span class="badge badge-success">Delivered</span></p>
                <p class="mb-1"><strong>Payment Method:</strong> Credit Card</p>
            </div>
            <div class="col-md-6">
                <h5 class="f-14 font-weight-bold mb-2">Delivery Address</h5>
                <p class="mb-1">John Doe</p>
                <p class="mb-1">ABC Restaurant</p>
                <p class="mb-1">123 Main Street</p>
                <p class="mb-1">Singapore 123456</p>
            </div>
        </div>

        <!-- Order Items by Merchant -->
        <div class="mb-4">
            <h5 class="f-14 font-weight-bold mb-3">Order Items</h5>
            <!-- Similar structure to shopping cart -->
        </div>

        <!-- Order Timeline -->
        <div class="mb-4">
            <h5 class="f-14 font-weight-bold mb-3">Order Timeline</h5>
            <div class="timeline">
                <div class="timeline-item mb-3">
                    <div class="d-flex">
                        <div class="mr-3">
                            <i class="fa fa-check-circle text-success fa-2x"></i>
                        </div>
                        <div>
                            <strong>Order Placed</strong>
                            <br><small class="text-muted">Jan 15, 2024 10:30 AM</small>
                        </div>
                    </div>
                </div>
                <div class="timeline-item mb-3">
                    <div class="d-flex">
                        <div class="mr-3">
                            <i class="fa fa-check-circle text-success fa-2x"></i>
                        </div>
                        <div>
                            <strong>Order Approved</strong>
                            <br><small class="text-muted">Jan 15, 2024 11:00 AM</small>
                        </div>
                    </div>
                </div>
                <div class="timeline-item mb-3">
                    <div class="d-flex">
                        <div class="mr-3">
                            <i class="fa fa-check-circle text-success fa-2x"></i>
                        </div>
                        <div>
                            <strong>Order Shipped</strong>
                            <br><small class="text-muted">Jan 16, 2024 2:00 PM</small>
                            <br><small class="text-primary">Tracking: TRK123456</small>
                        </div>
                    </div>
                </div>
                <div class="timeline-item">
                    <div class="d-flex">
                        <div class="mr-3">
                            <i class="fa fa-check-circle text-success fa-2x"></i>
                        </div>
                        <div>
                            <strong>Order Delivered</strong>
                            <br><small class="text-muted">Jan 18, 2024 3:30 PM</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

