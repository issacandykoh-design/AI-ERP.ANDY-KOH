@extends('prototype.layouts.app')

@section('content')
<!-- DELIVERY OPTIONS UI PROTOTYPE - HARDCODED DATA -->
<div class="content-wrapper">
    
    <div class="mb-4">
        <h1 class="f-28 font-weight-bold text-darkest-grey mb-2">
            <i class="fa fa-truck"></i> Delivery Options
        </h1>
        <p class="text-dark-grey">Select your preferred delivery method</p>
    </div>

    <!-- Delivery Address -->
    <div class="bg-white rounded b-shadow-4 p-4 mb-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3 class="f-18 font-weight-bold mb-0">Delivery Address</h3>
            <button class="btn btn-sm btn-outline-primary">Change Address</button>
        </div>
        <div class="border rounded p-3">
            <strong>John Doe</strong><br>
            ABC Restaurant<br>
            123 Main Street<br>
            Singapore 123456<br>
            <i class="fa fa-phone"></i> +65 1234 5678
        </div>
    </div>

    <!-- Delivery Options by Merchant -->
    <div class="bg-white rounded b-shadow-4 p-4 mb-4">
        <h3 class="f-18 font-weight-bold mb-3">Merchant: Fresh Farm Co.</h3>
        <p class="text-muted mb-3">2 items • Total: $232.00</p>

        <!-- Delivery Partner Options -->
        <div class="mb-4">
            <h5 class="f-16 font-weight-bold mb-3">Choose Delivery Partner</h5>

            <!-- Option 1: Ninja Van -->
            <div class="card mb-3 border-primary">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="flex-grow-1">
                            <div class="d-flex align-items-center mb-2">
                                <img src="https://via.placeholder.com/60x30?text=Ninja+Van" class="mr-3" alt="Ninja Van">
                                <div>
                                    <h5 class="mb-0">Ninja Van</h5>
                                    <small class="text-muted">Standard Delivery</small>
                                </div>
                                <span class="badge badge-success ml-3">Recommended</span>
                            </div>
                            <div class="row mb-2">
                                <div class="col-md-4">
                                    <small class="text-dark-grey">
                                        <i class="fa fa-dollar-sign"></i> <strong>Cost:</strong> $5.00
                                    </small>
                                </div>
                                <div class="col-md-4">
                                    <small class="text-dark-grey">
                                        <i class="fa fa-clock"></i> <strong>Delivery:</strong> 3-5 days
                                    </small>
                                </div>
                                <div class="col-md-4">
                                    <small class="text-success">
                                        <i class="fa fa-shield-alt"></i> <strong>Insurance:</strong> Included
                                    </small>
                                </div>
                            </div>
                            <div class="mb-2">
                                <small class="text-muted">
                                    <i class="fa fa-info-circle"></i> 
                                    Estimated delivery: Jan 23-25, 2024
                                </small>
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="delivery1" id="nv1" checked>
                                <label class="form-check-label" for="nv1">
                                    Select
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Option 2: Lalamove -->
            <div class="card mb-3">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="flex-grow-1">
                            <div class="d-flex align-items-center mb-2">
                                <img src="https://via.placeholder.com/60x30?text=Lalamove" class="mr-3" alt="Lalamove">
                                <div>
                                    <h5 class="mb-0">Lalamove</h5>
                                    <small class="text-muted">Express Delivery</small>
                                </div>
                                <span class="badge badge-primary ml-3">Fastest</span>
                            </div>
                            <div class="row mb-2">
                                <div class="col-md-4">
                                    <small class="text-dark-grey">
                                        <i class="fa fa-dollar-sign"></i> <strong>Cost:</strong> $12.00
                                    </small>
                                </div>
                                <div class="col-md-4">
                                    <small class="text-dark-grey">
                                        <i class="fa fa-clock"></i> <strong>Delivery:</strong> Same day
                                    </small>
                                </div>
                                <div class="col-md-4">
                                    <small class="text-success">
                                        <i class="fa fa-shield-alt"></i> <strong>Insurance:</strong> Included
                                    </small>
                                </div>
                            </div>
                            <div class="mb-2">
                                <small class="text-muted">
                                    <i class="fa fa-info-circle"></i> 
                                    Estimated delivery: Today, 6:00 PM - 9:00 PM
                                </small>
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="delivery1" id="ll1">
                                <label class="form-check-label" for="ll1">
                                    Select
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Option 3: Grab Express -->
            <div class="card mb-3">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="flex-grow-1">
                            <div class="d-flex align-items-center mb-2">
                                <img src="https://via.placeholder.com/60x30?text=Grab+Express" class="mr-3" alt="Grab Express">
                                <div>
                                    <h5 class="mb-0">Grab Express</h5>
                                    <small class="text-muted">Standard Delivery</small>
                                </div>
                                <span class="badge badge-warning ml-3">Cheapest</span>
                            </div>
                            <div class="row mb-2">
                                <div class="col-md-4">
                                    <small class="text-dark-grey">
                                        <i class="fa fa-dollar-sign"></i> <strong>Cost:</strong> $4.50
                                    </small>
                                </div>
                                <div class="col-md-4">
                                    <small class="text-dark-grey">
                                        <i class="fa fa-clock"></i> <strong>Delivery:</strong> 2-3 days
                                    </small>
                                </div>
                                <div class="col-md-4">
                                    <small class="text-success">
                                        <i class="fa fa-shield-alt"></i> <strong>Insurance:</strong> Included
                                    </small>
                                </div>
                            </div>
                            <div class="mb-2">
                                <small class="text-muted">
                                    <i class="fa fa-info-circle"></i> 
                                    Estimated delivery: Jan 22-23, 2024
                                </small>
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="delivery1" id="ge1">
                                <label class="form-check-label" for="ge1">
                                    Select
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Option 4: SingPost -->
            <div class="card mb-0">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="flex-grow-1">
                            <div class="d-flex align-items-center mb-2">
                                <img src="https://via.placeholder.com/60x30?text=SingPost" class="mr-3" alt="SingPost">
                                <div>
                                    <h5 class="mb-0">SingPost</h5>
                                    <small class="text-muted">Standard Delivery</small>
                                </div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-md-4">
                                    <small class="text-dark-grey">
                                        <i class="fa fa-dollar-sign"></i> <strong>Cost:</strong> $5.50
                                    </small>
                                </div>
                                <div class="col-md-4">
                                    <small class="text-dark-grey">
                                        <i class="fa fa-clock"></i> <strong>Delivery:</strong> 4-6 days
                                    </small>
                                </div>
                                <div class="col-md-4">
                                    <small class="text-success">
                                        <i class="fa fa-shield-alt"></i> <strong>Insurance:</strong> Optional
                                    </small>
                                </div>
                            </div>
                            <div class="mb-2">
                                <small class="text-muted">
                                    <i class="fa fa-info-circle"></i> 
                                    Estimated delivery: Jan 24-26, 2024
                                </small>
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="delivery1" id="sp1">
                                <label class="form-check-label" for="sp1">
                                    Select
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- AI Recommendations -->
    <div class="bg-white rounded b-shadow-4 p-4 mb-4">
        <h3 class="f-18 font-weight-bold mb-3">
            <i class="fa fa-robot"></i> AI Recommendations
        </h3>
        
        <div class="row">
            <div class="col-md-4 mb-3">
                <div class="card border-success">
                    <div class="card-body text-center">
                        <div class="badge badge-success p-2 mb-2">Cheapest</div>
                        <h5 class="mb-2">Grab Express</h5>
                        <p class="f-24 font-weight-bold text-success mb-2">$4.50</p>
                        <small class="text-muted">Save $0.50 compared to Ninja Van</small>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="card border-primary">
                    <div class="card-body text-center">
                        <div class="badge badge-primary p-2 mb-2">Fastest</div>
                        <h5 class="mb-2">Lalamove</h5>
                        <p class="f-24 font-weight-bold text-primary mb-2">Same Day</p>
                        <small class="text-muted">Get it today, 6:00 PM - 9:00 PM</small>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="card border-warning">
                    <div class="card-body text-center">
                        <div class="badge badge-warning p-2 mb-2">Most Reliable</div>
                        <h5 class="mb-2">Ninja Van</h5>
                        <p class="f-24 font-weight-bold text-warning mb-2">4.8 Rating</p>
                        <small class="text-muted">Best customer satisfaction</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Delivery Summary -->
    <div class="bg-white rounded b-shadow-4 p-4 mb-4">
        <h3 class="f-18 font-weight-bold mb-3">Delivery Summary</h3>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Merchant</th>
                        <th>Items</th>
                        <th>Selected Partner</th>
                        <th>Cost</th>
                        <th>Estimated Delivery</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Fresh Farm Co.</td>
                        <td>2 items</td>
                        <td>Ninja Van (Standard)</td>
                        <td>$5.00</td>
                        <td>Jan 23-25, 2024</td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary">Change</button>
                        </td>
                    </tr>
                    <tr>
                        <td>Coffee Masters</td>
                        <td>1 item</td>
                        <td>Lalamove (Express)</td>
                        <td>$12.00</td>
                        <td>Today, 6:00 PM - 9:00 PM</td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary">Change</button>
                        </td>
                    </tr>
                    <tr>
                        <td>Italian Delights</td>
                        <td>2 items</td>
                        <td>Grab Express (Standard)</td>
                        <td>$4.50</td>
                        <td>Jan 22-23, 2024</td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary">Change</button>
                        </td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="3" class="text-right"><strong>Total Delivery Cost:</strong></td>
                        <td><strong>$21.50</strong></td>
                        <td colspan="2"></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <!-- Actions -->
    <div class="bg-white rounded b-shadow-4 p-4">
        <div class="d-flex justify-content-between align-items-center">
            <a href="#" class="btn btn-outline-secondary">
                <i class="fa fa-arrow-left"></i> Back to Cart
            </a>
            <button class="btn btn-primary btn-lg">
                <i class="fa fa-check"></i> Confirm Delivery Options
            </button>
        </div>
    </div>
</div>
@endsection

