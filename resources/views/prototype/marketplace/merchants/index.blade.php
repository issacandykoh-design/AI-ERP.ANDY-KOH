@extends('prototype.layouts.app')

@section('title', 'Merchants - B2B Marketplace')

@section('content')
<div class="container my-5">
    <h1 class="h2 fw-bold mb-4">Merchants Directory</h1>

    <!-- Search & Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <input type="text" class="form-control" placeholder="Search merchants...">
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
                        <option>Sort by: Rating</option>
                        <option>Sort by: Products</option>
                        <option>Sort by: Name</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- Merchants Grid -->
    <div class="row g-4">
        <div class="col-md-4">
            <div class="card">
                <div class="card-body text-center">
                    <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 100px; height: 100px;">
                        <i class="fas fa-store fa-3x text-primary"></i>
                    </div>
                    <h5 class="fw-bold">Fresh Farm Co.</h5>
                    <div class="mb-2">
                        <span class="badge bg-success">Verified</span>
                        <span class="badge bg-info">Premium</span>
                    </div>
                    <div class="mb-2">
                        <i class="fas fa-star text-warning"></i>
                        <i class="fas fa-star text-warning"></i>
                        <i class="fas fa-star text-warning"></i>
                        <i class="fas fa-star text-warning"></i>
                        <i class="fas fa-star text-warning"></i>
                        <span class="ms-2">4.8</span>
                    </div>
                    <p class="text-muted small mb-3">1,234 products</p>
                    <a href="/prototype/marketplace/merchants/1" class="btn btn-primary btn-sm">View Store</a>
                </div>
            </div>
        </div>
        <!-- Add more merchant cards -->
    </div>
</div>
@endsection

