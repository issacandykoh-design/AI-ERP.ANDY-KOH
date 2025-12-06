@extends('prototype.layouts.app')

@section('title', 'Products - B2B Marketplace')

@push('styles')
<style>
    .product-card {
        border: none;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        transition: transform 0.3s;
        height: 100%;
    }
    .product-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }
    .product-image {
        height: 200px;
        object-fit: cover;
        width: 100%;
        background: #f3f4f6;
        display: flex;
        align-items: center;
        justify-content: center;
    }
</style>
@endpush

@section('content')
@include('marketplace.prototype.product-listing')
@endsection

