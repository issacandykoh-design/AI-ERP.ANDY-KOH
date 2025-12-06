@extends('prototype.layouts.app')

@section('title', 'Products - Merchant Dashboard')

@section('content')
<div class="container my-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h2 fw-bold mb-0">My Products</h1>
        <a href="/prototype/merchant/products/create" class="btn btn-primary">Add New Product</a>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <input type="text" class="form-control" placeholder="Search products...">
                </div>
                <div class="col-md-3">
                    <select class="form-control">
                        <option>All Categories</option>
                        <option>Fresh Produce</option>
                        <option>Beverages</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select class="form-control">
                        <option>All Status</option>
                        <option>Published</option>
                        <option>Draft</option>
                        <option>Out of Stock</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button class="btn btn-primary w-100">Filter</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Products Table -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>SKU</th>
                            <th>Price</th>
                            <th>Stock</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="bg-light rounded me-2" style="width: 50px; height: 50px;"></div>
                                    <div>
                                        <strong>Premium Organic Tomatoes</strong>
                                        <br><small class="text-muted">Fresh Produce</small>
                                    </div>
                                </div>
                            </td>
                            <td>TOM-001</td>
                            <td>$12.50</td>
                            <td>234</td>
                            <td><span class="badge bg-success">Published</span></td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary">Edit</button>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="bg-light rounded me-2" style="width: 50px; height: 50px;"></div>
                                    <div>
                                        <strong>Organic Carrots</strong>
                                        <br><small class="text-muted">Fresh Produce</small>
                                    </div>
                                </div>
                            </td>
                            <td>CRT-002</td>
                            <td>$8.90</td>
                            <td>567</td>
                            <td><span class="badge bg-success">Published</span></td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary">Edit</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

