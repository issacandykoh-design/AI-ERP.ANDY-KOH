@extends('prototype.layouts.app')

@section('title', 'Merchant Dashboard - B2B Marketplace')

@section('content')
<div class="container my-5">
    <h1 class="h2 fw-bold mb-4">Merchant Dashboard</h1>
    
    <!-- Stats Cards -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="text-muted">Total Sales</h6>
                    <h2 class="fw-bold">$45,230</h2>
                    <small class="text-success">↑ 15% from last month</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="text-muted">Orders</h6>
                    <h2 class="fw-bold">156</h2>
                    <small class="text-info">This month</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="text-muted">Products</h6>
                    <h2 class="fw-bold">234</h2>
                    <small class="text-warning">12 low stock</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="text-muted">Rating</h6>
                    <h2 class="fw-bold">4.8</h2>
                    <small class="text-success">1,234 reviews</small>
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
                            <th>Customer</th>
                            <th>Date</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>#ORD-2024-001234</td>
                            <td>ABC Restaurant</td>
                            <td>Jan 15, 2024</td>
                            <td>$232.00</td>
                            <td><span class="badge bg-success">Delivered</span></td>
                            <td><a href="/prototype/merchant/orders/1" class="btn btn-sm btn-outline-primary">View</a></td>
                        </tr>
                        <tr>
                            <td>#ORD-2024-001235</td>
                            <td>XYZ Cafe</td>
                            <td>Jan 18, 2024</td>
                            <td>$187.50</td>
                            <td><span class="badge bg-info">Processing</span></td>
                            <td><a href="/prototype/merchant/orders/2" class="btn btn-sm btn-outline-primary">View</a></td>
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
                    <a href="/prototype/merchant/products/create" class="btn btn-primary mb-2 w-100">Add New Product</a>
                    <a href="/prototype/merchant/products" class="btn btn-outline-primary mb-2 w-100">Manage Products</a>
                    <a href="/prototype/merchant/orders" class="btn btn-outline-secondary w-100">View All Orders</a>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Low Stock Alerts</h5>
                    <p class="text-muted">12 products need restocking</p>
                    <a href="/prototype/merchant/products" class="btn btn-outline-warning">View Products</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

