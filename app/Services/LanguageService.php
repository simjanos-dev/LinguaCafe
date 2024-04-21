<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class LanguageService {
    // stores the python service container's name
    private $pythonService;

    function __construct() {
        $this->pythonService = env('PYTHON_CONTAINER_NAME', 'linguacafe-python-service');
    }

    public function getLanguageSelectionDialogData($supportedSourceLanguages, $supportedSourceLanguagesWithRequiredInstall) {
        $installedLanguages = $this->getInstalledLanguages();
        
        // select installed languages only
        $languages = [];
        $everyLanguageInstalled = true;
        foreach ($supportedSourceLanguages as $supportedLanguage) {
            // if it is a language that must be installed, and it is not installed currently
            if (in_array($supportedLanguage, $supportedSourceLanguagesWithRequiredInstall, true)
                && !in_array($supportedLanguage, $installedLanguages)) {
                $everyLanguageInstalled = false;
                continue;
            }

            $languages[] = $supportedLanguage;
        }

        $responseData = new \stdClass();
        $responseData->languages = $languages;
        $responseData->everyLanguageInstalled = $everyLanguageInstalled;

        return $responseData;
    }
    
    public function getInstalledLanguages() {
        $installedLanguages = Http::get($this->pythonService . ':8678/models/list');
        $installedLanguages = json_decode($installedLanguages);
        
        return $installedLanguages;
    }

    public function installLanguage($language) {
        $installResult = Http::post($this->pythonService . ':8678/models/install', [
            'lang' => $language,
        ]);

        return $installResult;
    }

    public function deleteInstalledLanguages() {
        Http::delete($this->pythonService . ':8678/models/remove');

        return true;
    }
}