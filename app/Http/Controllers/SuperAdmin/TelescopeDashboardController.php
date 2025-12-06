<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\AccountBaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TelescopeDashboardController extends AccountBaseController
{
    public function index(Request $request)
    {
        abort_unless(user()->is_superadmin, 403);

        $this->pageTitle = 'Telescope';

        $type = $request->string('type')->toString();
        $search = $request->string('search')->toString();
        $from = $request->date('from');
        $to = $request->date('to');

        $query = DB::table('telescope_entries');

        if ($type) {
            $query->where('type', $type);
        }

        if ($from) {
            $query->where('created_at', '>=', $from->startOfDay());
        }

        if ($to) {
            $query->where('created_at', '<=', $to->endOfDay());
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('uuid', 'like', "%$search%")
                  ->orWhere('batch_id', 'like', "%$search%")
                  ->orWhere('family_hash', 'like', "%$search%")
                  ->orWhere('content', 'like', "%$search%");
            });
        }

        $entries = $query->orderByDesc('created_at')->paginate(20)->withQueryString();

        $this->entries = $entries;
        $this->type = $type;
        $this->search = $search;
        $this->from = $from;
        $this->to = $to;

        return view('super-admin.telescope.index', $this->data);
    }
}
