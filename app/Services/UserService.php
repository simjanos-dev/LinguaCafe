<?php

namespace App\Services;

use Illuminate\Support\Facades\Hash;

use Carbon\Carbon;
use App\Models\User;
use App\Services\GoalService;

class UserService {
    
    public function __construct() {
    }

    public function getUsers($userId) {
        $users = User
            ::select(['id', 'name', 'email', 'is_admin', 'password_changed', 'created_at'])
            ->get();

        foreach ($users as $user) {
            $user->created_at_text = Carbon::parse($user->created_at)->format('Y-m-d');
            $user->is_current_user = $user->id === $userId;
        }

        return $users;
    }

    public function updatePassword($user, $password) {
        $user->password = Hash::make($password);
        $user->password_changed = true;
        $user->save();
    }

    public function createUser($name, $email, $password, $isAdmin, $passwordChanged) {
        // check for duplicated e-email address
        $user = User
            ::where('email', $email)
            ->first();

        if ($user) {
            throw new \Exception('An other already exists with this email address.');
        }

        // create user
        $user = new User();
        $user->name = $name;
        $user->email = $email;
        $user->is_admin = $isAdmin;
        $user->password_changed = $passwordChanged;
        $user->password = Hash::make($password);
        $user->save();

        (new GoalService())->createGoalsForLanguage($user->id, 'spanish');

        return true;
    }

    public function updateUser($userId, $name, $email, $isAdmin) {
        // check for duplicated e-email address
        $user = User
            ::where('email', $email)
            ->where('id', '<>', $userId)
            ->first();

        if ($user) {
            throw new \Exception('An other user already exists with this email address.');
        }

        // check if user can be set to not admin
        if (!$isAdmin) {
            $adminCount = User::where('is_admin', true)->count();
            if ($adminCount < 2) {
                throw new \Exception('You cannot remove admin rights from the last admin user.');
            }
        }

        // retrieve user
        $user = User
            ::where('id', $userId)
            ->first();

        if (!$user) {
            throw new \Exception('This user does not exist.');
        }
        
        // update user
        $user->name = $name;
        $user->email = $email;
        $user->is_admin = $isAdmin;
        $user->save();

        return true;
    }
}