<?php

namespace App\Services;

use App\Models\User;

class ExpenseService
{
    public function create(array $data, User $user)
    {
        return $user->expenses()->create($data);
    }
}
