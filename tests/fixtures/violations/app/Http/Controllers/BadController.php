<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class BadController
{
    public function index(): void
    {
        DB::table('users')->get();
        User::query()->where('active', true)->get();
    }
}
