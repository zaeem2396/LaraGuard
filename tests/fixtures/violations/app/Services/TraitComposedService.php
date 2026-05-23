<?php

declare(strict_types=1);

namespace App\Services;

use App\Concerns\LogsActivity;

class TraitComposedService
{
    use LogsActivity;

    public function run(): void
    {
        $this->logActivity('started');
    }
}
