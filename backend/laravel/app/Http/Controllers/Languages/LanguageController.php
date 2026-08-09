<?php

namespace App\Http\Controllers\Languages;

use App\Helpers\Language\LanguageConfig;
use App\Http\Requests\Languages\ChangeLanguageRequest;
use App\Http\Requests\Languages\InstallLanguageRequest;
use App\Services\GoalService;
use App\Services\LanguageService;
use Carbon\Language;
use Illuminate\Support\Facades\Auth;
use Laravel\Horizon\Http\Controllers\Controller;

class LanguageController extends Controller
{
    public function __construct(
        private LanguageService $languageService,
        private GoalService $goalService
    ) {
        //
    }

    public function index()
    {
        $config = LanguageConfig::all();

        return response()->json($config);
    }

    public function indexForDialog()
    {
        $supportedSourceLanguages = LanguageConfig::all()->where('linguacafeSupport', '=', true)->pluck('name');
        $installableLanguages = LanguageConfig::all()->where('installRequired', '=', true)->pluck('name');

        $languageData = $this->languageService->getLanguageSelectionDialogData($supportedSourceLanguages, $installableLanguages);

        return response()->json([
            'data' => $languageData,
        ]);
    }

    public function indexInstallRequired()
    {
        $languages = LanguageConfig::all()->where('installRequired', '=', true);

        return response()->json([
            'data' => $languages->values()->toArray(),
        ]);
    }

    public function indexInstalled()
    {
        $languages = $this->languageService->getInstalledLanguages();
        $languages = $languages->map(function(string $language) {
            return LanguageConfig::load($language);
        });

        return response()->json([
            'data' => $languages->values()->toArray(),
        ]);
    }

    public function select($language, ChangeLanguageRequest $request)
    {
        $user = Auth::user();
        $language = LanguageConfig::load($language);

        $this->languageService->selectLanguage($user, $language);
        $this->goalService->createGoalsForLanguage($user, $language);

        return response()->noContent();
    }

    public function install(InstallLanguageRequest $request)
    {
        $language = LanguageConfig::load($request->validated('language'));

        $this->languageService->installLanguage($language);

        return response()->noContent();
    }

    public function uninstall()
    {
        $installableLanguages = LanguageConfig::all()->where('installRequired', '=', true)->pluck('name');
        $user = Auth::user();

        $this->languageService->deleteInstalledLanguages($user, $installableLanguages);

        return response()->noContent();
    }

    public function destroyData($language)
    {
        $user = Auth::user();
        $language = LanguageConfig::load($language);

        $this->languageService->deleteUserLanguageData($user, $language);

        return response()->noContent();
    }
}
