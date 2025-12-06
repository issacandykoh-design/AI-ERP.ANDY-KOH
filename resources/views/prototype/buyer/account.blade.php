@extends('prototype.layouts.app')

@section('title', 'Account Settings - B2B Marketplace')

@section('content')
<div class="container my-5">
    <h1 class="h2 fw-bold mb-4">Account Settings</h1>
    
    <div class="row">
        <div class="col-md-3">
            <div class="list-group">
                <a href="#" class="list-group-item list-group-item-action active">Profile</a>
                <a href="#" class="list-group-item list-group-item-action">Payment Methods</a>
                <a href="#" class="list-group-item list-group-item-action">Addresses</a>
                <a href="#" class="list-group-item list-group-item-action">Credit Terms</a>
            </div>
        </div>
        <div class="col-md-9">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Profile Information</h5>
                </div>
                <div class="card-body">
                    <form>
                        <div class="mb-3">
                            <label class="form-label">Company Name</label>
                            <input type="text" class="form-control" value="ABC Restaurant">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Contact Person</label>
                            <input type="text" class="form-control" value="John Doe">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" value="john@abcrestaurant.com">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Phone</label>
                            <input type="text" class="form-control" value="+65 1234 5678">
                        </div>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

