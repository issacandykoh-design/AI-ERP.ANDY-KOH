@extends('prototype.layouts.app')

@section('content')
<!-- PRODUCT DETAIL PAGE PROTOTYPE - HARDCODED DATA -->
<div class="content-wrapper">
    
    <!-- Breadcrumbs -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="#">Home</a></li>
            <li class="breadcrumb-item"><a href="#">Fresh Produce</a></li>
            <li class="breadcrumb-item"><a href="#">Vegetables</a></li>
            <li class="breadcrumb-item active">Premium Organic Tomatoes</li>
        </ol>
    </nav>

    <div class="row">
        <!-- Product Images -->
        <div class="col-lg-5">
            <div class="bg-white rounded b-shadow-4 p-4 mb-4">
                <!-- Main Image -->
                <div class="mb-3">
                    <img src="https://via.placeholder.com/500x500?text=Main+Product+Image" 
                         class="img-fluid rounded" 
                         id="main-product-image"
                         alt="Premium Organic Tomatoes">
                </div>
                
                <!-- Thumbnail Gallery -->
                <div class="row">
                    <div class="col-3 mb-2">
                        <img src="https://via.placeholder.com/100x100?text=Thumb1" 
                             class="img-thumbnail cursor-pointer" 
                             onclick="changeImage(this.src)"
                             alt="Thumbnail 1">
                    </div>
                    <div class="col-3 mb-2">
                        <img src="https://via.placeholder.com/100x100?text=Thumb2" 
                             class="img-thumbnail cursor-pointer" 
                             onclick="changeImage(this.src)"
                             alt="Thumbnail 2">
                    </div>
                    <div class="col-3 mb-2">
                        <img src="https://via.placeholder.com/100x100?text=Thumb3" 
                             class="img-thumbnail cursor-pointer" 
                             onclick="changeImage(this.src)"
                             alt="Thumbnail 3">
                    </div>
                    <div class="col-3 mb-2">
                        <img src="https://via.placeholder.com/100x100?text=Thumb4" 
                             class="img-thumbnail cursor-pointer" 
                             onclick="changeImage(this.src)"
                             alt="Thumbnail 4">
                    </div>
                </div>

                <!-- Video (if available) -->
                <div class="mt-3">
                    <button class="btn btn-outline-primary btn-sm">
                        <i class="fa fa-play"></i> Watch Product Video
                    </button>
                </div>
            </div>
        </div>

        <!-- Product Info -->
        <div class="col-lg-7">
            <div class="bg-white rounded b-shadow-4 p-4 mb-4">
                <!-- Badges -->
                <div class="mb-3">
                    <span class="badge badge-success mr-2">Bestseller</span>
                    <span class="badge badge-warning mr-2">-15% OFF</span>
                    <span class="badge badge-info mr-2">New</span>
                    <span class="badge badge-primary">Organic</span>
                </div>

                <!-- Product Name -->
                <h1 class="f-28 font-weight-bold text-darkest-grey mb-2">Premium Organic Tomatoes (1kg)</h1>
                
                <!-- Merchant Info -->
                <div class="mb-3">
                    <span class="text-dark-grey">Sold by:</span>
                    <a href="#" class="text-primary font-weight-bold">Fresh Farm Co.</a>
                    <span class="badge badge-success ml-2">Verified</span>
                    <span class="badge badge-info ml-1">Premium Merchant</span>
                </div>

                <!-- Rating -->
                <div class="mb-3">
                    <div class="d-flex align-items-center">
                        <div class="text-warning mr-2">
                            <i class="fa fa-star"></i>
                            <i class="fa fa-star"></i>
                            <i class="fa fa-star"></i>
                            <i class="fa fa-star"></i>
                            <i class="fa fa-star-half-alt"></i>
                        </div>
                        <span class="text-dark-grey mr-2">4.5</span>
                        <a href="#reviews" class="text-primary">(125 reviews)</a>
                        <span class="text-muted ml-2">|</span>
                        <span class="text-muted ml-2">234 sold</span>
                    </div>
                </div>

                <hr>

                <!-- Pricing Section -->
                <div class="mb-4">
                    <h3 class="f-21 font-weight-bold text-darkest-grey mb-3">Pricing</h3>
                    
                    <!-- Your Price -->
                    <div class="bg-light rounded p-3 mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <span class="text-dark-grey f-14">Your Price:</span>
                                <div class="mt-1">
                                    <span class="f-32 font-weight-bold text-darkest-grey">$12.50</span>
                                    <span class="text-muted text-decoration-line-through ml-2 f-18">$14.75</span>
                                </div>
                                <div class="mt-2">
                                    <span class="badge badge-success">
                                        <i class="fa fa-tag"></i> You Save $2.25 (15%)
                                    </span>
                                </div>
                            </div>
                            <div class="text-right">
                                <span class="badge badge-info p-2">B2B Pricing</span>
                                <p class="text-muted small mb-0 mt-2">Enterprise Tier</p>
                            </div>
                        </div>
                    </div>

                    <!-- Volume Pricing -->
                    <div class="mb-3">
                        <h5 class="f-16 font-weight-bold mb-2">Volume Discounts:</h5>
                        <ul class="list-unstyled">
                            <li class="mb-2">
                                <i class="fa fa-check text-success mr-2"></i>
                                <span>10-49 units: <strong>$11.50</strong> each (8% off)</span>
                            </li>
                            <li class="mb-2">
                                <i class="fa fa-check text-success mr-2"></i>
                                <span>50-99 units: <strong>$10.75</strong> each (14% off)</span>
                            </li>
                            <li class="mb-2">
                                <i class="fa fa-check text-success mr-2"></i>
                                <span>100+ units: <strong>$10.00</strong> each (20% off)</span>
                            </li>
                        </ul>
                    </div>

                    <!-- Price Comparison -->
                    <div class="alert alert-info">
                        <div class="d-flex justify-content-between">
                            <div>
                                <strong>Public Price:</strong> $14.75
                            </div>
                            <div>
                                <strong>Your Savings:</strong> <span class="text-success">$2.25 (15%)</span>
                            </div>
                        </div>
                    </div>
                </div>

                <hr>

                <!-- Product Options -->
                <div class="mb-4">
                    <h5 class="f-16 font-weight-bold mb-3">Select Options:</h5>
                    
                    <!-- Size/Variation -->
                    <div class="mb-3">
                        <label class="f-14 font-weight-bold mb-2">Size:</label>
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-outline-primary active">500g</button>
                            <button type="button" class="btn btn-outline-primary">1kg</button>
                            <button type="button" class="btn btn-outline-primary">2kg</button>
                        </div>
                    </div>

                    <!-- Quantity -->
                    <div class="mb-3">
                        <label class="f-14 font-weight-bold mb-2">Quantity:</label>
                        <div class="input-group" style="width: 150px;">
                            <button class="btn btn-outline-secondary" type="button">-</button>
                            <input type="number" class="form-control text-center" value="1" min="1">
                            <button class="btn btn-outline-secondary" type="button">+</button>
                        </div>
                        <small class="text-muted">Minimum order: 10 units</small>
                    </div>
                </div>

                <!-- Stock Status -->
                <div class="mb-4">
                    <div class="alert alert-success">
                        <i class="fa fa-check-circle"></i> 
                        <strong>In Stock</strong> - 234 units available
                    </div>
                    <small class="text-dark-grey">
                        <i class="fa fa-info-circle"></i> 
                        Usually ships within 1-2 business days
                    </small>
                </div>

                <!-- Actions -->
                <div class="mb-4">
                    <button class="btn btn-primary btn-lg mr-2">
                        <i class="fa fa-cart-plus"></i> Add to Cart
                    </button>
                    <button class="btn btn-outline-primary btn-lg mr-2">
                        <i class="fa fa-bolt"></i> Buy Now
                    </button>
                    <button class="btn btn-outline-secondary btn-lg">
                        <i class="fa fa-heart"></i> Save for Later
                    </button>
                </div>

                <!-- Delivery Info -->
                <div class="border-top pt-3">
                    <h5 class="f-16 font-weight-bold mb-2">Delivery Options:</h5>
                    <div class="mb-2">
                        <i class="fa fa-truck text-primary"></i>
                        <span class="ml-2">Standard Delivery: <strong>$5.00</strong> (3-5 days)</span>
                    </div>
                    <div class="mb-2">
                        <i class="fa fa-shipping-fast text-success"></i>
                        <span class="ml-2">Express Delivery: <strong>$12.00</strong> (1-2 days)</span>
                    </div>
                    <div class="mb-2">
                        <i class="fa fa-gift text-warning"></i>
                        <span class="ml-2">Free delivery on orders over <strong>$50</strong></span>
                    </div>
                    <a href="#" class="text-primary" data-toggle="modal" data-target="#deliveryModal">
                        <i class="fa fa-calculator"></i> Calculate Shipping
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Product Details Tabs -->
    <div class="bg-white rounded b-shadow-4 mb-4">
        <ul class="nav nav-tabs" id="productTabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" id="description-tab" data-toggle="tab" href="#description" role="tab">
                    Description
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="specifications-tab" data-toggle="tab" href="#specifications" role="tab">
                    Specifications
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="nutrition-tab" data-toggle="tab" href="#nutrition" role="tab">
                    Nutrition Facts
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="reviews-tab" data-toggle="tab" href="#reviews" role="tab">
                    Reviews (125)
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="faq-tab" data-toggle="tab" href="#faq" role="tab">
                    FAQ
                </a>
            </li>
        </ul>

        <div class="tab-content p-4" id="productTabsContent">
            <!-- Description Tab -->
            <div class="tab-pane fade show active" id="description" role="tabpanel">
                <h4 class="mb-3">Product Description</h4>
                <p class="text-dark-grey">
                    Our Premium Organic Tomatoes are hand-picked from certified organic farms, ensuring the highest quality and freshness. 
                    These tomatoes are perfect for salads, cooking, and canning. Grown without pesticides or synthetic fertilizers.
                </p>
                
                <h5 class="mt-4 mb-2">Key Features:</h5>
                <ul>
                    <li>100% Certified Organic</li>
                    <li>Freshly harvested</li>
                    <li>Rich in vitamins and antioxidants</li>
                    <li>Perfect for cooking and salads</li>
                    <li>Long shelf life when stored properly</li>
                </ul>

                <h5 class="mt-4 mb-2">Storage Instructions:</h5>
                <p class="text-dark-grey">
                    Store in a cool, dry place. Keep away from direct sunlight. Best consumed within 7 days of purchase.
                </p>
            </div>

            <!-- Specifications Tab -->
            <div class="tab-pane fade" id="specifications" role="tabpanel">
                <table class="table table-bordered">
                    <tbody>
                        <tr>
                            <td class="font-weight-bold" style="width: 30%;">Weight</td>
                            <td>1kg</td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">Origin</td>
                            <td>Singapore</td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">Certification</td>
                            <td>
                                <span class="badge badge-success">Organic</span>
                                <span class="badge badge-primary">Halal</span>
                                <span class="badge badge-info">Non-GMO</span>
                            </td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">Shelf Life</td>
                            <td>7-10 days</td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">Storage Temperature</td>
                            <td>10-15°C</td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">Packaging</td>
                            <td>Eco-friendly packaging</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Nutrition Facts Tab -->
            <div class="tab-pane fade" id="nutrition" role="tabpanel">
                <h4 class="mb-3">Nutrition Facts</h4>
                <div class="bg-light rounded p-4">
                    <div class="text-center mb-3">
                        <h5>Nutrition Facts</h5>
                        <p class="mb-0">Serving Size: 100g</p>
                        <p class="mb-0">Servings per container: 10</p>
                    </div>
                    <hr>
                    <table class="table table-sm">
                        <tbody>
                            <tr>
                                <td><strong>Calories</strong></td>
                                <td class="text-right">18</td>
                            </tr>
                            <tr>
                                <td>Total Fat</td>
                                <td class="text-right">0.2g</td>
                            </tr>
                            <tr>
                                <td>&nbsp;&nbsp;Saturated Fat</td>
                                <td class="text-right">0g</td>
                            </tr>
                            <tr>
                                <td>Cholesterol</td>
                                <td class="text-right">0mg</td>
                            </tr>
                            <tr>
                                <td>Sodium</td>
                                <td class="text-right">5mg</td>
                            </tr>
                            <tr>
                                <td>Total Carbohydrate</td>
                                <td class="text-right">3.9g</td>
                            </tr>
                            <tr>
                                <td>&nbsp;&nbsp;Dietary Fiber</td>
                                <td class="text-right">1.2g</td>
                            </tr>
                            <tr>
                                <td>&nbsp;&nbsp;Sugars</td>
                                <td class="text-right">2.6g</td>
                            </tr>
                            <tr>
                                <td><strong>Protein</strong></td>
                                <td class="text-right">0.9g</td>
                            </tr>
                        </tbody>
                    </table>
                    <hr>
                    <div class="mb-2">
                        <strong>Allergens:</strong> None
                    </div>
                    <div>
                        <strong>Vitamins & Minerals:</strong> Vitamin C (14% DV), Vitamin K (10% DV), Potassium (5% DV)
                    </div>
                </div>
            </div>

            <!-- Reviews Tab -->
            <div class="tab-pane fade" id="reviews" role="tabpanel">
                <div class="mb-4">
                    <h4 class="mb-3">Customer Reviews</h4>
                    <div class="row">
                        <div class="col-md-4 text-center">
                            <div class="f-48 font-weight-bold text-darkest-grey">4.5</div>
                            <div class="text-warning mb-2">
                                <i class="fa fa-star"></i>
                                <i class="fa fa-star"></i>
                                <i class="fa fa-star"></i>
                                <i class="fa fa-star"></i>
                                <i class="fa fa-star-half-alt"></i>
                            </div>
                            <p class="text-muted">Based on 125 reviews</p>
                        </div>
                        <div class="col-md-8">
                            <div class="mb-2">
                                <span class="mr-2">5 stars</span>
                                <div class="progress" style="height: 10px; width: 60%;">
                                    <div class="progress-bar" style="width: 60%"></div>
                                </div>
                                <span class="ml-2">60%</span>
                            </div>
                            <div class="mb-2">
                                <span class="mr-2">4 stars</span>
                                <div class="progress" style="height: 10px; width: 25%;">
                                    <div class="progress-bar" style="width: 25%"></div>
                                </div>
                                <span class="ml-2">25%</span>
                            </div>
                            <div class="mb-2">
                                <span class="mr-2">3 stars</span>
                                <div class="progress" style="height: 10px; width: 10%;">
                                    <div class="progress-bar" style="width: 10%"></div>
                                </div>
                                <span class="ml-2">10%</span>
                            </div>
                            <div class="mb-2">
                                <span class="mr-2">2 stars</span>
                                <div class="progress" style="height: 10px; width: 3%;">
                                    <div class="progress-bar" style="width: 3%"></div>
                                </div>
                                <span class="ml-2">3%</span>
                            </div>
                            <div class="mb-2">
                                <span class="mr-2">1 star</span>
                                <div class="progress" style="height: 10px; width: 2%;">
                                    <div class="progress-bar" style="width: 2%"></div>
                                </div>
                                <span class="ml-2">2%</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Review List -->
                <div class="border-top pt-4">
                    <!-- Review 1 -->
                    <div class="mb-4">
                        <div class="d-flex justify-content-between mb-2">
                            <div>
                                <strong>John Doe</strong>
                                <span class="badge badge-success ml-2">Verified Purchase</span>
                            </div>
                            <div class="text-warning">
                                <i class="fa fa-star"></i>
                                <i class="fa fa-star"></i>
                                <i class="fa fa-star"></i>
                                <i class="fa fa-star"></i>
                                <i class="fa fa-star"></i>
                            </div>
                        </div>
                        <p class="text-muted small mb-2">Reviewed on Jan 15, 2024</p>
                        <p class="text-dark-grey">
                            Excellent quality tomatoes! Very fresh and flavorful. Great value for money. Will definitely order again.
                        </p>
                        <div class="mt-2">
                            <span class="text-muted small">Helpful?</span>
                            <button class="btn btn-sm btn-link p-0 ml-2">Yes (12)</button>
                            <button class="btn btn-sm btn-link p-0 ml-2">No (0)</button>
                        </div>
                    </div>

                    <!-- Review 2 -->
                    <div class="mb-4">
                        <div class="d-flex justify-content-between mb-2">
                            <div>
                                <strong>Jane Smith</strong>
                                <span class="badge badge-success ml-2">Verified Purchase</span>
                            </div>
                            <div class="text-warning">
                                <i class="fa fa-star"></i>
                                <i class="fa fa-star"></i>
                                <i class="fa fa-star"></i>
                                <i class="fa fa-star"></i>
                                <i class="far fa-star"></i>
                            </div>
                        </div>
                        <p class="text-muted small mb-2">Reviewed on Jan 10, 2024</p>
                        <p class="text-dark-grey">
                            Good quality, but some tomatoes were a bit soft. Overall satisfied with the purchase.
                        </p>
                        <div class="mt-2">
                            <span class="text-muted small">Helpful?</span>
                            <button class="btn btn-sm btn-link p-0 ml-2">Yes (8)</button>
                            <button class="btn btn-sm btn-link p-0 ml-2">No (1)</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- FAQ Tab -->
            <div class="tab-pane fade" id="faq" role="tabpanel">
                <h4 class="mb-3">Frequently Asked Questions</h4>
                <div class="accordion" id="faqAccordion">
                    <div class="card mb-2">
                        <div class="card-header" id="faq1">
                            <h5 class="mb-0">
                                <button class="btn btn-link" type="button" data-toggle="collapse" data-target="#faq1Collapse">
                                    How fresh are these tomatoes?
                                </button>
                            </h5>
                        </div>
                        <div id="faq1Collapse" class="collapse show" data-parent="#faqAccordion">
                            <div class="card-body">
                                Our tomatoes are harvested fresh daily and delivered within 24-48 hours of harvest to ensure maximum freshness.
                            </div>
                        </div>
                    </div>
                    <div class="card mb-2">
                        <div class="card-header" id="faq2">
                            <h5 class="mb-0">
                                <button class="btn btn-link collapsed" type="button" data-toggle="collapse" data-target="#faq2Collapse">
                                    What is the minimum order quantity?
                                </button>
                            </h5>
                        </div>
                        <div id="faq2Collapse" class="collapse" data-parent="#faqAccordion">
                            <div class="card-body">
                                The minimum order quantity is 10 units. However, you can order as few as 1 unit for testing purposes.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Related Products -->
    <div class="bg-white rounded b-shadow-4 mb-4">
        <div class="p-4 border-bottom-grey">
            <h3 class="f-21 font-weight-bold text-darkest-grey mb-0">You May Also Like</h3>
        </div>
        <div class="row p-4">
            <!-- Related Product Cards (similar to listing page) -->
            <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                <div class="card border-0 product-card">
                    <img src="https://via.placeholder.com/300x300?text=Related" class="card-img-top" alt="Product">
                    <div class="card-body">
                        <h5 class="card-title f-16">Organic Carrots (500g)</h5>
                        <p class="text-darkest-grey font-weight-bold mb-0">$8.90</p>
                        <button class="btn btn-primary btn-sm mt-2 w-100">Add to Cart</button>
                    </div>
                </div>
            </div>
            <!-- More related products... -->
        </div>
    </div>
</div>

<script>
function changeImage(src) {
    document.getElementById('main-product-image').src = src;
}
</script>

<style>
.cursor-pointer {
    cursor: pointer;
}
.product-card {
    transition: transform 0.2s;
}
.product-card:hover {
    transform: translateY(-5px);
}
</style>
@endsection

