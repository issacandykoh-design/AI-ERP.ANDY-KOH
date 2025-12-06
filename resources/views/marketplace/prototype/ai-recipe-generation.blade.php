@extends('prototype.layouts.app')

@section('content')
<!-- AI RECIPE GENERATION UI PROTOTYPE - HARDCODED DATA -->
<div class="content-wrapper">
    
    <div class="mb-4">
        <h1 class="f-28 font-weight-bold text-darkest-grey mb-2">
            <i class="fa fa-robot"></i> AI Recipe Generator
        </h1>
        <p class="text-dark-grey">Generate professional recipes using AI</p>
    </div>

    <div class="row">
        <!-- Left: Recipe Generator Form -->
        <div class="col-lg-8">
            <div class="bg-white rounded b-shadow-4 p-4 mb-4">
                <h3 class="f-18 font-weight-bold mb-3">Generate Recipe</h3>

                <!-- Step 1: Select Products/Ingredients -->
                <div class="mb-4">
                    <h5 class="f-16 font-weight-bold mb-3">Step 1: Select Products/Ingredients</h5>
                    <div class="input-group mb-3">
                        <input type="text" class="form-control" placeholder="Search products..." id="product-search">
                        <div class="input-group-append">
                            <button class="btn btn-outline-secondary" type="button">
                                <i class="fa fa-search"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Selected Products -->
                    <div class="selected-products mb-3">
                        <div class="d-flex align-items-center bg-light rounded p-2 mb-2">
                            <img src="https://via.placeholder.com/50x50?text=Tomato" class="rounded mr-2" alt="Product">
                            <div class="flex-grow-1">
                                <strong>Premium Organic Tomatoes (1kg)</strong>
                                <br><small class="text-muted">500g</small>
                            </div>
                            <button class="btn btn-sm btn-outline-danger">
                                <i class="fa fa-times"></i>
                            </button>
                        </div>
                        <div class="d-flex align-items-center bg-light rounded p-2 mb-2">
                            <img src="https://via.placeholder.com/50x50?text=Onion" class="rounded mr-2" alt="Product">
                            <div class="flex-grow-1">
                                <strong>Fresh Onions (500g)</strong>
                                <br><small class="text-muted">200g</small>
                            </div>
                            <button class="btn btn-sm btn-outline-danger">
                                <i class="fa fa-times"></i>
                            </button>
                        </div>
                        <div class="d-flex align-items-center bg-light rounded p-2">
                            <img src="https://via.placeholder.com/50x50?text=Garlic" class="rounded mr-2" alt="Product">
                            <div class="flex-grow-1">
                                <strong>Garlic Cloves (100g)</strong>
                                <br><small class="text-muted">3 cloves</small>
                            </div>
                            <button class="btn btn-sm btn-outline-danger">
                                <i class="fa fa-times"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Step 2: Recipe Options -->
                <div class="mb-4">
                    <h5 class="f-16 font-weight-bold mb-3">Step 2: Recipe Options</h5>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="f-14 font-weight-bold mb-2">Cuisine Type</label>
                            <select class="form-control">
                                <option>Italian</option>
                                <option>Asian</option>
                                <option>Western</option>
                                <option>Mediterranean</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="f-14 font-weight-bold mb-2">Difficulty Level</label>
                            <select class="form-control">
                                <option>Easy</option>
                                <option selected>Medium</option>
                                <option>Hard</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="f-14 font-weight-bold mb-2">Serving Size</label>
                            <select class="form-control">
                                <option>1-2 people</option>
                                <option selected>2-3 people</option>
                                <option>4-6 people</option>
                                <option>6+ people</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="f-14 font-weight-bold mb-2">Dietary Preferences</label>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="diet1">
                                <label class="form-check-label" for="diet1">Vegetarian</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="diet2">
                                <label class="form-check-label" for="diet2">Vegan</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="diet3" checked>
                                <label class="form-check-label" for="diet3">Gluten-Free</label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 3: Generate Options -->
                <div class="mb-4">
                    <h5 class="f-16 font-weight-bold mb-3">Step 3: What to Generate</h5>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" id="gen1" checked>
                        <label class="form-check-label" for="gen1">
                            <strong>Recipe</strong> (5 credits)
                        </label>
                    </div>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" id="gen2" checked>
                        <label class="form-check-label" for="gen2">
                            <strong>Nutrition Facts</strong> (3 credits)
                        </label>
                    </div>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" id="gen3">
                        <label class="form-check-label" for="gen3">
                            <strong>Cooking Instruction Images</strong> (6 credits - 3 images)
                        </label>
                    </div>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" id="gen4">
                        <label class="form-check-label" for="gen4">
                            <strong>Step-by-Step Videos</strong> (30 credits - full video)
                        </label>
                    </div>
                </div>

                <!-- Cost Estimate -->
                <div class="alert alert-info mb-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <strong>Estimated Cost:</strong> 8 credits ($0.80)
                            <br><small>Recipe (5) + Nutrition Facts (3)</small>
                        </div>
                        <div>
                            <strong>Your Credits:</strong> 250 remaining
                        </div>
                    </div>
                </div>

                <!-- Generate Button -->
                <button class="btn btn-primary btn-lg btn-block">
                    <i class="fa fa-magic"></i> Generate Recipe
                </button>
            </div>

            <!-- Generated Recipe Display (Hidden until generated) -->
            <div class="bg-white rounded b-shadow-4 p-4 mb-4" id="generated-recipe" style="display: none;">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h3 class="f-18 font-weight-bold mb-0">Generated Recipe</h3>
                    <div>
                        <button class="btn btn-sm btn-success mr-2">
                            <i class="fa fa-check"></i> Approve & Use
                        </button>
                        <button class="btn btn-sm btn-outline-primary">
                            <i class="fa fa-redo"></i> Regenerate
                        </button>
                    </div>
                </div>

                <!-- Recipe Content -->
                <div class="mb-4">
                    <h2 class="f-24 font-weight-bold mb-2">Fresh Tomato Pasta</h2>
                    <p class="text-dark-grey mb-3">
                        A delicious Italian pasta dish featuring fresh tomatoes, onions, and garlic. 
                        Perfect for a quick and healthy meal.
                    </p>

                    <!-- Recipe Info -->
                    <div class="row mb-4">
                        <div class="col-md-3 text-center">
                            <i class="fa fa-clock fa-2x text-primary mb-2"></i>
                            <p class="mb-0"><strong>Prep Time:</strong></p>
                            <p class="mb-0">15 minutes</p>
                        </div>
                        <div class="col-md-3 text-center">
                            <i class="fa fa-fire fa-2x text-danger mb-2"></i>
                            <p class="mb-0"><strong>Cook Time:</strong></p>
                            <p class="mb-0">20 minutes</p>
                        </div>
                        <div class="col-md-3 text-center">
                            <i class="fa fa-users fa-2x text-success mb-2"></i>
                            <p class="mb-0"><strong>Serves:</strong></p>
                            <p class="mb-0">2-3 people</p>
                        </div>
                        <div class="col-md-3 text-center">
                            <i class="fa fa-signal fa-2x text-warning mb-2"></i>
                            <p class="mb-0"><strong>Difficulty:</strong></p>
                            <p class="mb-0">Medium</p>
                        </div>
                    </div>

                    <!-- Ingredients -->
                    <div class="mb-4">
                        <h4 class="f-18 font-weight-bold mb-3">Ingredients</h4>
                        <ul class="list-unstyled">
                            <li class="mb-2">
                                <i class="fa fa-check text-success mr-2"></i>
                                500g Premium Organic Tomatoes, diced
                            </li>
                            <li class="mb-2">
                                <i class="fa fa-check text-success mr-2"></i>
                                200g Fresh Onions, chopped
                            </li>
                            <li class="mb-2">
                                <i class="fa fa-check text-success mr-2"></i>
                                3 cloves Garlic, minced
                            </li>
                            <li class="mb-2">
                                <i class="fa fa-check text-success mr-2"></i>
                                300g Pasta (your choice)
                            </li>
                            <li class="mb-2">
                                <i class="fa fa-check text-success mr-2"></i>
                                2 tbsp Olive Oil
                            </li>
                            <li class="mb-2">
                                <i class="fa fa-check text-success mr-2"></i>
                                Salt and pepper to taste
                            </li>
                        </ul>
                    </div>

                    <!-- Instructions -->
                    <div class="mb-4">
                        <h4 class="f-18 font-weight-bold mb-3">Instructions</h4>
                        <div class="mb-3">
                            <div class="d-flex">
                                <div class="mr-3">
                                    <span class="badge badge-primary" style="width: 30px; height: 30px; display: inline-flex; align-items: center; justify-content: center;">1</span>
                                </div>
                                <div class="flex-grow-1">
                                    <p class="mb-2"><strong>Prepare Ingredients</strong></p>
                                    <p class="text-dark-grey mb-2">
                                        Dice the tomatoes, chop the onions, and mince the garlic. Set aside.
                                    </p>
                                    <img src="https://via.placeholder.com/400x300?text=Step+1+Image" class="img-fluid rounded mb-2" alt="Step 1">
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="d-flex">
                                <div class="mr-3">
                                    <span class="badge badge-primary" style="width: 30px; height: 30px; display: inline-flex; align-items: center; justify-content: center;">2</span>
                                </div>
                                <div class="flex-grow-1">
                                    <p class="mb-2"><strong>Cook Pasta</strong></p>
                                    <p class="text-dark-grey mb-2">
                                        Boil water in a large pot, add salt, and cook pasta according to package instructions.
                                    </p>
                                    <img src="https://via.placeholder.com/400x300?text=Step+2+Image" class="img-fluid rounded mb-2" alt="Step 2">
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="d-flex">
                                <div class="mr-3">
                                    <span class="badge badge-primary" style="width: 30px; height: 30px; display: inline-flex; align-items: center; justify-content: center;">3</span>
                                </div>
                                <div class="flex-grow-1">
                                    <p class="mb-2"><strong>Make Sauce</strong></p>
                                    <p class="text-dark-grey mb-2">
                                        Heat olive oil in a pan, sauté onions and garlic until fragrant. Add tomatoes and cook until soft.
                                    </p>
                                    <img src="https://via.placeholder.com/400x300?text=Step+3+Image" class="img-fluid rounded mb-2" alt="Step 3">
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="d-flex">
                                <div class="mr-3">
                                    <span class="badge badge-primary" style="width: 30px; height: 30px; display: inline-flex; align-items: center; justify-content: center;">4</span>
                                </div>
                                <div class="flex-grow-1">
                                    <p class="mb-2"><strong>Combine & Serve</strong></p>
                                    <p class="text-dark-grey mb-2">
                                        Mix cooked pasta with sauce, season with salt and pepper. Serve hot.
                                    </p>
                                    <img src="https://via.placeholder.com/400x300?text=Final+Dish" class="img-fluid rounded mb-2" alt="Final">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Nutrition Facts -->
                    <div class="mb-4">
                        <h4 class="f-18 font-weight-bold mb-3">Nutrition Facts</h4>
                        <div class="bg-light rounded p-4">
                            <div class="text-center mb-3">
                                <h5>Nutrition Facts</h5>
                                <p class="mb-0">Serving Size: 1 serving (250g)</p>
                                <p class="mb-0">Servings per recipe: 2-3</p>
                            </div>
                            <hr>
                            <table class="table table-sm">
                                <tbody>
                                    <tr>
                                        <td><strong>Calories</strong></td>
                                        <td class="text-right">285</td>
                                    </tr>
                                    <tr>
                                        <td>Total Fat</td>
                                        <td class="text-right">8g</td>
                                    </tr>
                                    <tr>
                                        <td>Total Carbohydrate</td>
                                        <td class="text-right">42g</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Protein</strong></td>
                                        <td class="text-right">9g</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Tips -->
                    <div class="mb-4">
                        <h4 class="f-18 font-weight-bold mb-3">Tips & Variations</h4>
                        <ul>
                            <li>Add fresh basil for extra flavor</li>
                            <li>Top with parmesan cheese before serving</li>
                            <li>For a spicier version, add red pepper flakes</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right: AI Credits & History -->
        <div class="col-lg-4">
            <!-- AI Credits Card -->
            <div class="bg-white rounded b-shadow-4 p-4 mb-4">
                <h4 class="f-18 font-weight-bold mb-3">AI Credits</h4>
                <div class="text-center mb-3">
                    <div class="f-48 font-weight-bold text-primary">250</div>
                    <p class="text-muted mb-0">Credits Remaining</p>
                </div>
                <div class="mb-3">
                    <div class="progress mb-2">
                        <div class="progress-bar" role="progressbar" style="width: 25%;" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100">
                            25%
                        </div>
                    </div>
                    <small class="text-muted">250 of 1,000 credits used this month</small>
                </div>
                <button class="btn btn-primary btn-block">
                    <i class="fa fa-plus"></i> Buy More Credits
                </button>
            </div>

            <!-- Recent Recipes -->
            <div class="bg-white rounded b-shadow-4 p-4">
                <h4 class="f-18 font-weight-bold mb-3">Recent Recipes</h4>
                <div class="list-group">
                    <a href="#" class="list-group-item list-group-item-action">
                        <div class="d-flex justify-content-between">
                            <div>
                                <strong>Tomato Pasta</strong>
                                <br><small class="text-muted">Generated 2 hours ago</small>
                            </div>
                            <span class="badge badge-success">Approved</span>
                        </div>
                    </a>
                    <a href="#" class="list-group-item list-group-item-action">
                        <div class="d-flex justify-content-between">
                            <div>
                                <strong>Vegetable Stir Fry</strong>
                                <br><small class="text-muted">Generated yesterday</small>
                            </div>
                            <span class="badge badge-warning">Pending</span>
                        </div>
                    </a>
                    <a href="#" class="list-group-item list-group-item-action">
                        <div class="d-flex justify-content-between">
                            <div>
                                <strong>Fresh Salad Bowl</strong>
                                <br><small class="text-muted">Generated 3 days ago</small>
                            </div>
                            <span class="badge badge-success">Approved</span>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

