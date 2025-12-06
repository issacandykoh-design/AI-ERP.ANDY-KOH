<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>@yield('title', 'B2B Marketplace - Prototype')</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Prototype CSS -->
    <link rel="stylesheet" href="{{ asset('css/panel-redesign.css') }}">
    
    @stack('styles')
</head>
<body>
    <!-- Header -->
    <header class="marketplace-header">
        <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
            <div class="container">
                <a class="navbar-brand" href="/prototype/marketplace">
                    <strong class="text-primary">Craveva</strong> <span class="text-muted">Marketplace</span>
                </a>
                
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                    <span class="navbar-toggler-icon"></span>
                </button>
                
                <div class="collapse navbar-collapse" id="navbarNav">
                    <!-- Search Bar -->
                    <div class="mx-auto" style="max-width: 500px; width: 100%;">
                        <form class="d-flex">
                            <div class="input-group">
                                <input type="text" class="form-control" placeholder="Search products, merchants..." value="Fresh chicken">
                                <button class="btn btn-primary" type="submit">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </form>
                    </div>
                    
                    <!-- Right Menu -->
                    <ul class="navbar-nav ms-auto">
                        <li class="nav-item">
                            <a class="nav-link" href="/prototype/marketplace/merchants">
                                <i class="fas fa-store"></i> Merchants
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link position-relative" href="/prototype/marketplace/cart">
                                <i class="fas fa-shopping-cart"></i> Cart
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">3</span>
                            </a>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user-circle"></i> John Doe
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="/prototype/buyer/dashboard"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                                <li><a class="dropdown-item" href="/prototype/buyer/orders"><i class="fas fa-shopping-bag"></i> My Orders</a></li>
                                <li><a class="dropdown-item" href="/prototype/buyer/account"><i class="fas fa-cog"></i> Account Settings</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="#"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                            </ul>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
    </header>

    <!-- Main Content -->
    <main class="marketplace-main">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="marketplace-footer bg-dark text-white mt-5">
        <div class="container py-5">
            <div class="row">
                <div class="col-md-3 mb-4">
                    <h5>Craveva Marketplace</h5>
                    <p class="text-muted">Singapore's #1 B2B F&B Marketplace</p>
                </div>
                <div class="col-md-3 mb-4">
                    <h6>Quick Links</h6>
                    <ul class="list-unstyled">
                        <li><a href="#" class="text-muted text-decoration-none">About Us</a></li>
                        <li><a href="#" class="text-muted text-decoration-none">Become a Merchant</a></li>
                        <li><a href="#" class="text-muted text-decoration-none">Contact Us</a></li>
                    </ul>
                </div>
                <div class="col-md-3 mb-4">
                    <h6>Support</h6>
                    <ul class="list-unstyled">
                        <li><a href="#" class="text-muted text-decoration-none">Help Center</a></li>
                        <li><a href="#" class="text-muted text-decoration-none">FAQs</a></li>
                        <li><a href="#" class="text-muted text-decoration-none">Shipping Info</a></li>
                    </ul>
                </div>
                <div class="col-md-3 mb-4">
                    <h6>Connect</h6>
                    <div class="d-flex gap-3">
                        <a href="#" class="text-white"><i class="fab fa-facebook fa-lg"></i></a>
                        <a href="#" class="text-white"><i class="fab fa-instagram fa-lg"></i></a>
                        <a href="#" class="text-white"><i class="fab fa-linkedin fa-lg"></i></a>
                    </div>
                </div>
            </div>
            <hr class="bg-secondary">
            <div class="row">
                <div class="col-md-12 text-center text-muted">
                    <p>&copy; 2024 Craveva Marketplace. All rights reserved.</p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    @stack('scripts')
</body>
</html>

