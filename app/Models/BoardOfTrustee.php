<?php

namespace App\Models;

class BoardOfTrustee extends User
{
    protected $table = 'users';

    public function getMorphClass()
    {
        return User::class;
    }
}
