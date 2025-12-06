@extends('prototype.layouts.app')

@section('title', 'Order Details - Merchant Dashboard')

@section('content')
<div class="container my-5">
    <h1 class="h2 fw-bold mb-4">Order #ORD-2024-001234</h1>
    
    <div class="card mb-4">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Order Information</h5>
                <span class="badge bg-success">Delivered</span>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <p><strong>Customer:</strong> ABC Restaurant</p>
                    <p><strong>Order Date:</strong> Jan 15, 2024</p>
                    <p><strong>Status:</strong> Delivered</p>
                </div>
                <div class="col-md-6">
                    <p><strong>Delivery Address:</strong></p>
                    <p>123 Main Street<br>Singapore 123456</p>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Order Items</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Quantity</th>
                            <th>Price</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Premium Organic Tomatoes (1kg)</td>
                            <td>15</td>
                            <td>$12.50</td>
                            <td>$187.50</td>
                        </tr>
                        <tr>
                            <td>Organic Carrots (500g)</td>
                            <td>5</td>
                            <td>$8.90</td>
                            <td>$44.50</td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3" class="text-end"><strong>Total:</strong></td>
                            <td><strong>$232.00</strong></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <div class="d-flex gap-2">
        <a href="/prototype/merchant/orders" class="btn btn-outline-secondary">Back to Orders</a>
        <button class="btn btn-primary">Print Invoice</button>
    </div>
</div>
@endsection

