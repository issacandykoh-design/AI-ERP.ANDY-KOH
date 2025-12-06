@extends('prototype.layouts.app')

@section('title', 'Buyer Dashboard - B2B Marketplace')

@section('content')
<div class="container my-5">
    <h1 class="h2 fw-bold mb-4">Buyer Dashboard</h1>
    
    <!-- Stats Cards -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="text-muted">Total Orders</h6>
                    <h2 class="fw-bold">24</h2>
                    <small class="text-success">↑ 12% from last month</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="text-muted">Pending Orders</h6>
                    <h2 class="fw-bold">3</h2>
                    <small class="text-warning">Awaiting approval</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="text-muted">Total Spent</h6>
                    <h2 class="fw-bold">$12,450</h2>
                    <small class="text-muted">This month</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="text-muted">Savings</h6>
                    <h2 class="fw-bold text-success">$1,245</h2>
                    <small class="text-success">B2B discounts</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Orders -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Recent Orders</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Order #</th>
                            <th>Date</th>
                            <th>Merchants</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>#ORD-2024-001234</td>
                            <td>Jan 15, 2024</td>
                            <td>3 merchants</td>
                            <td>$419.72</td>
                            <td><span class="badge bg-success">Delivered</span></td>
                            <td><a href="/prototype/buyer/orders/1" class="btn btn-sm btn-outline-primary">View</a></td>
                        </tr>
                        <tr>
                            <td>#ORD-2024-001235</td>
                            <td>Jan 18, 2024</td>
                            <td>2 merchants</td>
                            <td>$289.50</td>
                            <td><span class="badge bg-info">Processing</span></td>
                            <td><a href="/prototype/buyer/orders/2" class="btn btn-sm btn-outline-primary">View</a></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row g-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Quick Actions</h5>
                    <a href="/prototype/marketplace/products" class="btn btn-primary mb-2 w-100">Browse Products</a>
                    <a href="/prototype/marketplace/cart" class="btn btn-outline-primary mb-2 w-100">View Cart</a>
                    <a href="/prototype/buyer/orders" class="btn btn-outline-secondary w-100">View All Orders</a>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Recommended Products</h5>
                    <p class="text-muted">Based on your purchase history</p>
                    <a href="/prototype/marketplace/products" class="btn btn-outline-primary">View Recommendations</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

