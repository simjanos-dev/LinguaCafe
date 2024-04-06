<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Services\GoalService;
use App\Services\StatisticsService;

// request classes
use App\Http\Requests\Home\ChangeLanguageRequest;
use App\Http\Requests\Home\GetConfigRequest;

class HomeController extends Controller {
    
    private $statisticsService;
    private $goalService;

    public function __construct(StatisticsService $statisticsService, GoalService $goalService) {
        $this->middleware('auth');

        $this->statisticsService = $statisticsService;
        $this->goalService = $goalService;
    }

    public function index() {
        $selectedLanguage = Auth::user()->selected_language;
        $userCount = User::count();
        $userName = Auth::user()->name;
        $theme = $_COOKIE['theme'] ?? 'light';
        
        return view('home', [
            'language' => $selectedLanguage,
            'userCount' => $userCount,
            'userName' => $userName,
            'theme' => $theme
        ]);
    }

    public function getStatistics() {
        $userId = Auth::user()->id;
        $language = Auth::user()->selected_language;

        try {
            $statistics = $this->statisticsService->getStatistics($userId, $language);
        } catch (\Exception $e) {
            abort(500, $e->getMessage());
        }

        return response()->json($statistics, 200);
    }

    public function getLanguage() {
        $language = Auth::user()->selected_language;
        return response()->json($language, 200);
    }

    public function changeLanguage($language, ChangeLanguageRequest $request) {
        $user = Auth::user();
        $user->selected_language = strtolower($language);
        $user->save();

        $this->goalService->createGoalsForLanguage($user->id, $language);

        return response()->json('Language has been changed successfully.', 200);
    }

    public function getConfig($configPath, GetConfigRequest $request) {
        if (!config()->has($configPath)) {
            abort(500, 'Requested config value does not exist.');
        }

        $config = config($configPath);
        return response()->json($config, 200);
    }
}
