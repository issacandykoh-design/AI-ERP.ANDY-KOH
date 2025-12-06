# Open REST API

An open-source Laravel REST API package that provides a clean and standardized way to build REST APIs.

## Features

- **ApiController**: Base controller class with CRUD operations
- **ApiModel**: Enhanced Eloquent model with API-specific features
- **ApiResponse**: Standardized JSON response format
- **ApiRoute**: Convenient routing facade for API endpoints
- **Exception Handling**: Comprehensive API exception handling
- **Authentication**: Built-in Sanctum authentication support

## Installation

1. Add the package to your Laravel project:

```bash
composer require open/rest-api
```

2. Register the service provider in `config/app.php`:

```php
'providers' => [
    // ...
    Open\RestAPI\Providers\ApiServiceProvider::class,
],

'aliases' => [
    // ...
    'ApiRoute' => Open\RestAPI\Facades\ApiRoute::class,
],
```

## Usage

### Creating API Controllers

```php
<?php

namespace App\Http\Controllers\API;

use Open\RestAPI\ApiController;
use App\Models\User;

class UserController extends ApiController
{
    protected $model = User::class;
}
```

### Using API Routes

```php
<?php

use Open\RestAPI\Facades\ApiRoute;

ApiRoute::group(['middleware' => ['auth:sanctum', 'api.auth']], function () {
    ApiRoute::resource('users', UserController::class);
});
```

### API Responses

```php
use Open\RestAPI\ApiResponse;

// Success response
return ApiResponse::success($data);

// Error response
return ApiResponse::error('Something went wrong', [], 400);

// Validation error
return ApiResponse::validationError($errors);
```

### Models

```php
<?php

namespace App\Models;

use Open\RestAPI\ApiModel;

class User extends ApiModel
{
    protected $fillable = ['name', 'email'];
}
```

## License

This package is open-sourced software licensed under the [MIT license](LICENSE).