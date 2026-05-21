<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use Illuminate\Support\Facades\DB;

class LegacyDbController
{
    public function index(): void
    {
        DB::table('legacy')->get();
    }
}
