<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\User;

class IllegalModelController
{
    public function index(User $user): void
    {
        //
    }
}
