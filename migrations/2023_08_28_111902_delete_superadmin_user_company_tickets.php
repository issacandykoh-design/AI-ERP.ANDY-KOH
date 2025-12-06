<?php

use App\Models\Ticket;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{

    public function up(): void
    {
        Ticket::withoutGlobalScopes()->withTrashed()->whereHas('requester', function ($q) {
            $q->withoutGlobalScopes();
            if (Schema::hasColumn('users', 'is_superadmin')) {
                $q->where('is_superadmin', 1);
            } else {
                $q->withRole('superadmin');
            }
        })->forceDelete();
    }

};
