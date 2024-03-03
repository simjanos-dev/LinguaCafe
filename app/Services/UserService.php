<?php

namespace App\Services;

use Carbon\Carbon;
use App\Models\User;

class UserService {
    
    public function __construct() {
    }

    public function getUsers() {
        $users = User
            ::select(['id', 'name', 'email', 'is_admin', 'password_changed', 'created_at'])
            ->get();

        foreach ($users as $user) {
            $user->created_at_text = Carbon::parse($user->created_at)->format('Y-m-d');
        }

        return $users;
    }
}