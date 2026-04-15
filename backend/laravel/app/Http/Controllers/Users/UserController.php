<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Http\Requests\Users\StoreUserRequest;
use App\Http\Requests\Users\UpdatePasswordRequest;
use App\Http\Requests\Users\UpdateUserRequest;
use App\Http\Resources\User\UserResource;
use App\Http\Resources\User\UserResourceCollection;
use App\Models\User;
use App\Services\LanguageService;
use App\Services\SettingsService;
use App\Services\UserService;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function __construct(
        private UserService $userService,
        private LanguageService $languageService,
        private SettingsService $settingsService
    ) {
        //
    }

    public function index()
    {
        $user = Auth::user();

        $users = $this->userService->getUsers($user);

        return new UserResourceCollection($users);
    }

    public function update(UpdateUserRequest $request, User $user)
    {
        $name = $request->validated('name');
        $email = $request->validated('email');
        $isAdmin = $request->validated('isAdmin');

        $this->userService->updateUser($user, $name, $email, $isAdmin);

        return response()->noContent();
    }

    public function updatePassword(UpdatePasswordRequest $request)
    {
        $user = Auth::user();
        $password = $request->validated('password');

        $this->userService->updatePassword($user, $password);

        return response()->noContent();
    }

    public function store(StoreUserRequest $request)
    {
        $userCount = User::count();
        $name = $request->validated('name');
        $email = $request->validated('email');
        $password = $request->validated('password');
        $isAdmin = $request->validated('isAdmin');
        $passwordChanged = $userCount === 0;

        if (!Auth::check() && $userCount !== 0) {
            throw new \Exception('Not authorized to create a user.');
        }

        $this->userService->createUser($name, $email, $password, $isAdmin, $passwordChanged);

        return response()->noContent();
    }

    public function appUserData()
    {
        $userCount = User::count();

        if (!Auth::check()) {
            return response()->json([
                'userCount' => $userCount,
            ]);
        }

        $selectedLanguage = Auth::user()->selected_language;
        $userName = Auth::user()->name;
        $userEmail = Auth::user()->email;
        $isAdmin = (bool) Auth::user()->is_admin;
        $theme = $_COOKIE['theme'] ?? 'dark';
        $user = User::query()
            ->select([
                'id',
                'is_admin',
                'name',
                'email',
                'selected_language',
                'password_changed',
            ])
            ->where('id', '=', Auth::user()->id)
            ->first();

        $themeSettingNames = collect(['textStyling', 'vuetifyThemes']);

        $themeSettings = $this->settingsService->getUserSettingsByName(
            Auth::user(),
            $themeSettingNames,
        );

        return response()->json([
            'language' => $selectedLanguage,
            'userCount' => $userCount,
            'userName' => $userName,
            'userEmail' => $userEmail,
            'isAdmin' => $isAdmin,
            'theme' => $theme,
            'themeSettings' => $themeSettings,
            'userUuid' => Auth::user()->uuid,
            'user' => new UserResource($user),
        ]);
    }

    public function passwordChanged()
    {
        return response()->json([
            'data' => (bool) Auth::user()->password_changed,
        ]);
    }
}
