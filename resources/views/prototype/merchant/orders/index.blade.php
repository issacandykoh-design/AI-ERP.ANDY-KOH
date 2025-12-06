@extends('prototype.layouts.app')

@section('title', 'Orders - Merchant Dashboard')

@section('content')
<div class="container my-5">
    <h1 class="h2 fw-bold mb-4">Orders</h1>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <select class="form-control">
                        <option>All Status</option>
                        <option>Pending</option>
                        <option>Processing</option>
                        <option>Shipped</option>
                        <option>Delivered</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <input type="text" class="form-control" placeholder="Search orders...">
                </div>
                <div class="col-md-3">
                    <input type="text" class="form-control" placeholder="Date Range">
                </div>
                <div class="col-md-3">
                    <button class="btn btn-primary w-100">Filter</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Orders Table -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Order #</th>
                            <th>Customer</th>
                            <th>Date</th>
                            <th>Items</th>
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
                            <td>2 items</td>
                            <td>$232.00</td>
                            <td><span class="badge bg-success">Delivered</span></td>
                            <td><a href="/prototype/merchant/orders/1" class="btn btn-sm btn-outline-primary">View</a></td>
                        </tr>
                        <tr>
                            <td>#ORD-2024-001235</td>
                            <td>XYZ Cafe</td>
                            <td>Jan 18, 2024</td>
                            <td>1 item</td>
                            <td>$187.50</td>
                            <td><span class="badge bg-info">Processing</span></td>
                            <td><a href="/prototype/merchant/orders/2" class="btn btn-sm btn-outline-primary">View</a></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

