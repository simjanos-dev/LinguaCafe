<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\User;
use App\Services\GoalService;

class UserController extends Controller
{
    public function isUserPasswordChanged() {
        return Auth::user()->password_changed;
    }

    public function getUsers() {
        $users = User
            ::select(['id', 'name', 'email', 'is_admin', 'password_changed', 'created_at'])
            ->get()
            ->each(function ($user) {
                $user->created_at_text = Carbon::parse($user->created_at)->format('Y-m-d');
            });

        return json_encode($users);
    }

    public function changePassword(Request $request) {
        // Validate incoming request
        $request->validate([
            'password' => 'required|string|min:8|max:32',
            'passwordConfirmation' => 'required|same:password',
        ]);

        // Update user password
        $user = Auth::user();
        $user->update([
            'password' => Hash::make($request->password),
            'password_changed' => true,
        ]);

        return 'success';
    }

    public function updateOrCreateUser(Request $request) {
        // Check authentication
        if (!Auth::check() && User::count() !== 0) {
            return 'Unauthenticated.';
        }

        // Validate incoming request
        $request->validate([
            'userId' => 'required|integer',
            'name' => 'required|string|min:5|max:24',
            'email' => 'required|email',
            'isAdmin' => 'required|boolean',
            'password' => 'nullable|string|min:8|max:32|required_if:userId,-1',
            'passwordConfirmation' => 'nullable|required_with:password|same:password',
        ]);

        $userId = $request->post('userId');
        $email = $request->post('email');

        // Check for duplicated email
        if (User::where('email', $email)->where('id', '<>', $userId)->exists()) {
            return 'There is already a user with this e-mail address.';
        }

        // Create or retrieve user
        $user = ($userId == -1) ? new User() : User::find($userId);

        if (!$user && $userId != -1) {
            return 'User ID does not exist.';
        }

        // Set user data
        $user->fill([
            'name' => $request->post('name'),
            'email' => $email,
            'is_admin' => $request->post('isAdmin'),
            'password_changed' => User::count() === 0,
        ]);

        // Set password if it's a new user
        if ($userId == -1) {
            $user->password = Hash::make($request->post('password'));
        }
        
        // save user
        $user->save();

        // Create goals for a new user
        if ($userId == -1) {
            (new GoalService())->createGoalsForLanguage($user->id, 'japanese');
        }
        
        return 'success';
    }
}
