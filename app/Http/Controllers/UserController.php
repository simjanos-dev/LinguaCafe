<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use App\Models\User;
use App\Services\GoalService;
use App\Services\UserService;

// request classes
use App\Http\Requests\User\UpdatePasswordRequest;

class UserController extends Controller {
    private $userService;

    public function __construct(UserService $userService) {
        $this->userService = $userService;

    }

    public function isUserPasswordChanged() {
        $passwordChanged = Auth::user()->password_changed;
        return $passwordChanged;
    }

    public function getUsers() {
        try {
            $users = $this->userService->getUsers();
        } catch(\Exception $e) {
            abort(500, $e->getMessage());
        }

        return response()->json($users, 200);
    }

    public function updatePassword(UpdatePasswordRequest $request) {
        $user = Auth::user();
        $password = $request->post('password');
        
        try {
            $this->userService->updatePassword($user, $password);
        } catch(\Exception $e) {
            abort(500, $e->getMessage());
        }
                
        return response()->json('Password has been updated successfully.', 200);
    }

    // updates user info, or creates a new user
    public function updateOrCreateUser(Request $request) {
        $userCount = User::count();
        if (!Auth::check() && $userCount !== 0) {
            return 'Unauthenticated.';
        }

        // check for missing post data
        if (!$request->has('userId') || !$request->has('name') ||
            !$request->has('email') || !$request->has('isAdmin')) {
            return 'Missing parameter.';
        }

        // check for missing post data for new user
        if ($request->post('userId') === -1 && (!$request->has('password') || !$request->has('passwordConfirmation'))) {
            return 'Missing parameter.';
        }

        $userId = $request->post('userId');
        $name = $request->post('name');
        $email = $request->post('email');
        $password = $request->post('password');
        $passwordConfirmation = $request->post('passwordConfirmation');
        $isAdmin = $request->post('isAdmin');

        // validate name
        if (mb_strlen($name) < 5 || mb_strlen($name) > 24) {
            return 'Name must be between 5 and 24 characters.';
        }
        
        // validate email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return 'E-mail address is invalid.';
        }
        
        // validate password
        if ($userId === -1 && (mb_strlen($password) < 8 || mb_strlen($password) > 32)) {
            return 'Password must be between 8 and 32 characters.';
        }

        if ($userId === -1 && $password !== $passwordConfirmation) {
            return 'Password confirmation does not match the password.';
        }

        // validate duplicated email
        $duplicatedEmail = User::where('email', $email)->where('id', '<>', $userId)->first();
        if ($duplicatedEmail) {
            return 'There is already a user with this e-mail address.';
        }
        
        // create or retrieve user
        if ($userId == -1) {
            $user = new User();
        } else {
            $user = User::where('id', $userId)->first();
            if (!$user) {
                return 'User ID does not exist.';
            }
        }

        // set user data
        $user->name = $name;
        $user->email = $email;
        $user->is_admin = $isAdmin;
        $user->password_changed = $userCount === 0;

        if ($userId == -1) {
            $user->password = Hash::make($password);
        }
        
        // save user
        $user->save();

        if ($userId == -1) {
            (new GoalService())->createGoalsForLanguage($user->id, 'japanese');
        }
        
        return 'success';
    }
}
