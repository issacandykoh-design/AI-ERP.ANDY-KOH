@extends('prototype.layouts.app')

@section('content')
<!-- PRICING COMPARISON DISPLAY PROTOTYPE - HARDCODED DATA -->
<div class="content-wrapper">
    
    <div class="mb-4">
        <h1 class="f-28 font-weight-bold text-darkest-grey mb-2">Pricing Comparison</h1>
        <p class="text-dark-grey">See how much you save with B2B pricing</p>
    </div>

    <!-- Product Pricing Comparison -->
    <div class="bg-white rounded b-shadow-4 p-4 mb-4">
        <div class="row align-items-center mb-4">
            <div class="col-md-2 text-center">
                <img src="https://via.placeholder.com/150x150?text=Product" 
                     class="img-thumbnail" 
                     alt="Product">
            </div>
            <div class="col-md-10">
                <h3 class="f-20 font-weight-bold mb-2">Premium Organic Tomatoes (1kg)</h3>
                <p class="text-muted mb-0">SKU: TOM-001 | Category: Fresh Produce</p>
            </div>
        </div>

        <!-- Pricing Tiers Table -->
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead class="bg-light">
                    <tr>
                        <th>Pricing Tier</th>
                        <th class="text-center">Quantity</th>
                        <th class="text-right">Unit Price</th>
                        <th class="text-right">Discount</th>
                        <th class="text-right">Your Savings</th>
                        <th class="text-center">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <strong>Public Price</strong>
                            <br><small class="text-muted">Regular customers</small>
                        </td>
                        <td class="text-center">1+</td>
                        <td class="text-right">
                            <span class="f-18 font-weight-bold">$14.75</span>
                        </td>
                        <td class="text-right">
                            <span class="text-muted">0%</span>
                        </td>
                        <td class="text-right">
                            <span class="text-muted">-</span>
                        </td>
                        <td class="text-center">
                            <span class="badge badge-secondary">Public</span>
                        </td>
                    </tr>
                    <tr class="table-warning">
                        <td>
                            <strong>Your Price (Enterprise Tier)</strong>
                            <br><small class="text-success">✓ Currently Applied</small>
                        </td>
                        <td class="text-center">1+</td>
                        <td class="text-right">
                            <span class="f-18 font-weight-bold text-primary">$12.50</span>
                        </td>
                        <td class="text-right">
                            <span class="text-success font-weight-bold">15%</span>
                        </td>
                        <td class="text-right">
                            <span class="text-success font-weight-bold">$2.25</span>
                        </td>
                        <td class="text-center">
                            <span class="badge badge-success">Active</span>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <strong>Volume Tier 1</strong>
                            <br><small class="text-muted">10-49 units</small>
                        </td>
                        <td class="text-center">10-49</td>
                        <td class="text-right">
                            <span class="f-18 font-weight-bold">$11.50</span>
                        </td>
                        <td class="text-right">
                            <span class="text-success">22%</span>
                        </td>
                        <td class="text-right">
                            <span class="text-success">$3.25</span>
                        </td>
                        <td class="text-center">
                            <button class="btn btn-sm btn-outline-primary">Upgrade</button>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <strong>Volume Tier 2</strong>
                            <br><small class="text-muted">50-99 units</small>
                        </td>
                        <td class="text-center">50-99</td>
                        <td class="text-right">
                            <span class="f-18 font-weight-bold">$10.75</span>
                        </td>
                        <td class="text-right">
                            <span class="text-success">27%</span>
                        </td>
                        <td class="text-right">
                            <span class="text-success">$4.00</span>
                        </td>
                        <td class="text-center">
                            <button class="btn btn-sm btn-outline-primary">Upgrade</button>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <strong>Volume Tier 3</strong>
                            <br><small class="text-muted">100+ units</small>
                        </td>
                        <td class="text-center">100+</td>
                        <td class="text-right">
                            <span class="f-18 font-weight-bold">$10.00</span>
                        </td>
                        <td class="text-right">
                            <span class="text-success">32%</span>
                        </td>
                        <td class="text-right">
                            <span class="text-success">$4.75</span>
                        </td>
                        <td class="text-center">
                            <button class="btn btn-sm btn-outline-primary">Upgrade</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Savings Calculator -->
        <div class="bg-light rounded p-4 mt-4">
            <h5 class="f-16 font-weight-bold mb-3">Savings Calculator</h5>
            <div class="row">
                <div class="col-md-4">
                    <label class="f-14 font-weight-bold mb-2">Quantity:</label>
                    <input type="number" class="form-control" value="15" min="1">
                </div>
                <div class="col-md-4">
                    <label class="f-14 font-weight-bold mb-2">Your Price:</label>
                    <div class="f-24 font-weight-bold text-primary">$187.50</div>
                </div>
                <div class="col-md-4">
                    <label class="f-14 font-weight-bold mb-2">You Save:</label>
                    <div class="f-24 font-weight-bold text-success">$33.75</div>
                    <small class="text-muted">vs Public Price</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Multiple Products Comparison -->
    <div class="bg-white rounded b-shadow-4 p-4 mb-4">
        <h3 class="f-18 font-weight-bold mb-3">Cart Pricing Summary</h3>
        
        <div class="table-responsive">
            <table class="table">
                <thead class="bg-light">
                    <tr>
                        <th>Product</th>
                        <th class="text-center">Quantity</th>
                        <th class="text-right">Public Price</th>
                        <th class="text-right">Your Price</th>
                        <th class="text-right">Savings</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Premium Organic Tomatoes (1kg)</td>
                        <td class="text-center">15</td>
                        <td class="text-right">
                            <span class="text-muted text-decoration-line-through">$221.25</span>
                        </td>
                        <td class="text-right">
                            <span class="font-weight-bold text-primary">$187.50</span>
                        </td>
                        <td class="text-right">
                            <span class="text-success font-weight-bold">$33.75</span>
                        </td>
                    </tr>
                    <tr>
                        <td>Organic Carrots (500g)</td>
                        <td class="text-center">5</td>
                        <td class="text-right">
                            <span class="text-muted text-decoration-line-through">$49.50</span>
                        </td>
                        <td class="text-right">
                            <span class="font-weight-bold text-primary">$44.50</span>
                        </td>
                        <td class="text-right">
                            <span class="text-success font-weight-bold">$5.00</span>
                        </td>
                    </tr>
                    <tr>
                        <td>Artisan Coffee Beans (1kg)</td>
                        <td class="text-center">2</td>
                        <td class="text-right">
                            <span class="text-muted text-decoration-line-through">$59.80</span>
                        </td>
                        <td class="text-right">
                            <span class="font-weight-bold text-primary">$57.80</span>
                        </td>
                        <td class="text-right">
                            <span class="text-success font-weight-bold">$2.00</span>
                        </td>
                    </tr>
                </tbody>
                <tfoot class="bg-light">
                    <tr>
                        <td><strong>Total</strong></td>
                        <td class="text-center"><strong>22 items</strong></td>
                        <td class="text-right">
                            <span class="text-muted text-decoration-line-through">$330.55</span>
                        </td>
                        <td class="text-right">
                            <span class="f-20 font-weight-bold text-primary">$289.80</span>
                        </td>
                        <td class="text-right">
                            <span class="f-20 font-weight-bold text-success">$40.75</span>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Total Savings Alert -->
        <div class="alert alert-success mt-3">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-1">
                        <i class="fa fa-tag"></i> Total Savings
                    </h5>
                    <p class="mb-0">
                        You're saving <strong>$40.75 (12.3%)</strong> compared to public pricing
                    </p>
                </div>
                <div class="text-right">
                    <div class="f-32 font-weight-bold text-success">$40.75</div>
                    <small class="text-muted">Total Savings</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Pricing Tier Information -->
    <div class="bg-white rounded b-shadow-4 p-4 mb-4">
        <h3 class="f-18 font-weight-bold mb-3">Your Pricing Tier: Enterprise</h3>
        <div class="row">
            <div class="col-md-6">
                <h5 class="f-14 font-weight-bold mb-2">Tier Benefits:</h5>
                <ul>
                    <li>15% discount on all products</li>
                    <li>Access to volume discounts</li>
                    <li>Priority customer support</li>
                    <li>Net 30 payment terms</li>
                    <li>Dedicated account manager</li>
                </ul>
            </div>
            <div class="col-md-6">
                <h5 class="f-14 font-weight-bold mb-2">Upgrade Options:</h5>
                <div class="mb-2">
                    <strong>Premium Tier</strong> - 20% discount
                    <br><small class="text-muted">Minimum monthly order: $5,000</small>
                    <button class="btn btn-sm btn-outline-primary ml-2">Upgrade</button>
                </div>
                <div>
                    <strong>Platinum Tier</strong> - 25% discount
                    <br><small class="text-muted">Minimum monthly order: $10,000</small>
                    <button class="btn btn-sm btn-outline-primary ml-2">Upgrade</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

