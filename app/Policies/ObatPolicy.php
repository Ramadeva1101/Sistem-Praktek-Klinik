<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Obat;
use Illuminate\Auth\Access\HandlesAuthorization;

class ObatPolicy
{
    use HandlesAuthorization;

    public function create(User $user): bool
    {
        return $user->role === 'admin';
    }

    public function update(User $user, Obat $obat): bool
    {
        return $user->role === 'admin';
    }

    public function delete(User $user, Obat $obat): bool
    {
        return $user->role === 'admin';
    }
} 
