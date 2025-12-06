<?php

namespace Modules\RestAPI\Http\Controllers;

use Open\RestAPI\ApiResponse;

class CompanyController extends ApiBaseController
{
    public function company()
    {
        $company = api_user()->company;
        $company->makeHidden('card_last_four', 'stripe_id', 'card_brand', 'trial_ends_at');

        return ApiResponse::make('Application data fetched successfully', $company->toArray());
    }
}
