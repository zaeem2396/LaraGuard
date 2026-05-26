<?php

declare(strict_types=1);

namespace App\Http\Controllers\Legacy;

use Illuminate\Support\Facades\DB;

class SkippedController
{
    public function index(): void
    {
        DB::table('users')->get();
    }
}
