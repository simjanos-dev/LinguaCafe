<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

use App\Models\User;

// services
use App\Services\LanguageService;
use Exception;

// request classes
use App\Http\Requests\Languages\InstallLanguageRequest;

class LanguageController extends Controller {
    private $languageService;

    function __construct(LanguageService $languageService) {
        $this->languageService = $languageService;
    }


    public function getInstalledLanguages() {
        try {
            $installedLanguages = $this->languageService->getInstalledLanguages();
        } catch (Exception $e) {
            return response()->json($e->getMessage(), 500);
        }

        return response()->json($installedLanguages, 200);
    }


    public function installLanguage(InstallLanguageRequest $request) {
        $language = $request->post('language');

        try {
            $installResult = $this->languageService->installLanguage($language);
        } catch (Exception $e) {
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
        } catch (Exception $e) {
            return response()->json($e->getMessage(), 500);
        }

        return response()->json('Installed languages has been deleted successfully.', 200);        
    }
}
