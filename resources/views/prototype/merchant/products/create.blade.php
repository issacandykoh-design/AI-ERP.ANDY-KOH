@extends('prototype.layouts.app')

@section('title', 'Add Product - Merchant Dashboard')

@section('content')
<div class="container my-5">
    <h1 class="h2 fw-bold mb-4">Add New Product</h1>
    
    <div class="card">
        <div class="card-body">
            <form>
                <div class="row">
                    <div class="col-md-8">
                        <div class="mb-3">
                            <label class="form-label">Product Name *</label>
                            <input type="text" class="form-control" placeholder="Enter product name">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" rows="5" placeholder="Product description"></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Category</label>
                                <select class="form-control">
                                    <option>Select category</option>
                                    <option>Fresh Produce</option>
                                    <option>Beverages</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">SKU</label>
                                <input type="text" class="form-control" placeholder="Product SKU">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Price *</label>
                                <input type="number" class="form-control" placeholder="0.00" step="0.01">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Stock Quantity</label>
                                <input type="number" class="form-control" placeholder="0">
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">Product Image</label>
                            <div class="border rounded p-4 text-center">
                                <i class="fas fa-image fa-3x text-muted mb-2"></i>
                                <p class="text-muted">Click to upload</p>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select class="form-control">
                                <option>Published</option>
                                <option>Draft</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">Save Product</button>
                    <button type="button" class="btn btn-outline-secondary">Save as Draft</button>
                    <a href="/prototype/merchant/products" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

