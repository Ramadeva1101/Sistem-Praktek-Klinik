<?php

namespace App\Models;

use Filament\Panel;
use Illuminate\Auth\Authenticatable;
use Filament\Models\Contracts\FilamentUser;

class User extends Authenticatable implements FilamentUser
{
    // ...

    public function canAccessPanel(Panel $panel): bool
    {
        return true; // atau sesuaikan dengan logic Anda
    }
}
