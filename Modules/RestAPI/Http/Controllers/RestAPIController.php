<?php

namespace Modules\RestAPI\Http\Controllers;

use Illuminate\Http\Request;

class RestAPIController extends ApiBaseController
{
    public function index(Request $request)
    {
        return true;
    }
}
