<?php

namespace App\Http\Controllers;

use App\Helper\Reply;
use Illuminate\Http\Request;
use App\Models\KnowledgeBase;

class KnowledgeBaseAutoProcessController extends AccountBaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'app.menu.knowledgebase';
        $this->middleware(function ($request, $next) {
            abort_403(!in_array('knowledgebase', $this->user->modules));
            return $next($request);
        });
    }

    public function index()
    {
        return view('knowledge-base.auto-process.index', $this->data);
    }

    public function process(Request $request)
    {
        // Auto-processing logic would go here
        return Reply::success(__('messages.recordSaved'));
    }
}