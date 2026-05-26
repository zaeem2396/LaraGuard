<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class ScannedDbController
{
    public function index(): void
    {
        DB::table('users')->get();
    }
}
