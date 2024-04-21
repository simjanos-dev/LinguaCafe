<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

// services
use App\Services\LanguageService;
use App\Services\GoalService;

// request classes
use App\Http\Requests\Languages\InstallLanguageRequest;
use App\Http\Requests\Languages\ChangeLanguageRequest;

class LanguageController extends Controller {
    private $languageService;
    private $goalService;

    function __construct(LanguageService $languageService, GoalService $goalService) {
        $this->languageService = $languageService;
        $this->goalService = $goalService;
    }

    public function getLanguageSelectionDialogData() {
        $supportedSourceLanguages = config('linguacafe.languages.supported_languages');
        $supportedSourceLanguagesWithRequiredInstall = config('linguacafe.languages.supported_languages_with_required_install');

        try {
            $languageData = $this->languageService->getLanguageSelectionDialogData($supportedSourceLanguages, $supportedSourceLanguagesWithRequiredInstall);
        } catch (\Exception $e) {
            return response()->json($e->getMessage(), 500);
        }

        return response()->json($languageData, 200);
    }

    public function selectLanguage($language, ChangeLanguageRequest $request) {
        $user = Auth::user();
        $user->selected_language = strtolower($language);
        $user->save();

        $this->goalService->createGoalsForLanguage($user->id, $language);

        return response()->json('Language has been changed successfully.', 200);
    }

    public function getInstalledLanguages() {
        try {
            $installedLanguages = $this->languageService->getInstalledLanguages();
        } catch (\Exception $e) {
            return response()->json($e->getMessage(), 500);
        }

        return response()->json($installedLanguages, 200);
    }


    public function installLanguage(InstallLanguageRequest $request) {
        $language = $request->post('language');

        try {
            $installResult = $this->languageService->installLanguage($language);
        } catch (\Exception $e) {
            return response()->json($e->getMessage(), 500);
        }

        
        if ($installResult->getStatusCode() === 200) {

        } else {
            echo($installResult->getBody());exit;
        }
        

        return response()->json('Language has been installed successfully.', 200);
    }

    public function deleteInstalledLanguages() {
        try {
            $this->languageService->deleteInstalledLanguages();
        } catch (\Exception $e) {
            return response()->json($e->getMessage(), 500);
        }

        return response()->json('Installed languages has been deleted successfully.', 200);        
    }
}
