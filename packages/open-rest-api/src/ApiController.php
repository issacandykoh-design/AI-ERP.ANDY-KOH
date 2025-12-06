<?php

namespace Open\RestAPI;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Open\RestAPI\ApiResponse;

class ApiController extends Controller
{
    use AuthorizesRequests, ValidatesRequests;

    protected $model;
    protected $indexRequest;
    protected $storeRequest;
    protected $updateRequest;
    protected $showRequest;
    protected $deleteRequest;

    public function __construct()
    {
        // Set default guard to api for sanctum
        config(['auth.defaults.guard' => 'api']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($this->indexRequest) {
            $request = app($this->indexRequest);
        }

        $query = $this->model::query();
        
        // Apply filters if available
        if (method_exists($this, 'applyFilters')) {
            $query = $this->applyFilters($query, $request);
        }

        // Apply pagination
        $perPage = $request->get('per_page', 15);
        $data = $query->paginate($perPage);

        return ApiResponse::make($data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if ($this->storeRequest) {
            $request = app($this->storeRequest);
        }

        $data = $this->model::create($request->validated() ?? $request->all());

        return ApiResponse::make($data, [], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id, Request $request = null)
    {
        if ($this->showRequest && $request) {
            $request = app($this->showRequest);
        }

        $data = $this->model::findOrFail($id);

        return ApiResponse::make($data);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update($id, Request $request)
    {
        if ($this->updateRequest) {
            $request = app($this->updateRequest);
        }

        $data = $this->model::findOrFail($id);
        $data->update($request->validated() ?? $request->all());

        return ApiResponse::make($data);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id, Request $request = null)
    {
        if ($this->deleteRequest && $request) {
            $request = app($this->deleteRequest);
        }

        $data = $this->model::findOrFail($id);
        $data->delete();

        return ApiResponse::make(null, ['message' => 'Resource deleted successfully']);
    }
}
