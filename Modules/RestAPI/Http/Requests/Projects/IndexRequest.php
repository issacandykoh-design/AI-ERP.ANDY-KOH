<?php

namespace Modules\RestAPI\Http\Requests\Projects;

use Modules\RestAPI\Http\Requests\BaseRequest;

class IndexRequest extends BaseRequest
{
    /**
     * @return bool
     *
     * @throws \Open\RestAPI\Exceptions\UnauthorizedException
     */
    public function authorize()
    {
        $user = api_user();

        // Either user has role admin or has permission view_projects
        // Plus he needs to have projects module enabled from settings

        return in_array('projects', $user->modules);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [

        ];
    }
}
