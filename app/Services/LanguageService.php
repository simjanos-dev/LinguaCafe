<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class LanguageService {
    // stores the python service container's name
    private $pythonService;

    function __construct() {
        $this->pythonService = env('PYTHON_CONTAINER_NAME', 'linguacafe-python-service');
    }

    public function getLanguageSelectionDialogData($supportedSourceLanguages, $installableLanguages) {
        $installedLanguages = $this->getInstalledLanguages();
        
        // select installed languages only
        $languages = [];
        $notInstalledLanguages = 0;
        foreach ($supportedSourceLanguages as $supportedLanguage) {
            // if it is a language that must be installed, and it is not installed currently
            if (in_array($supportedLanguage, $installableLanguages, true)
                && !in_array($supportedLanguage, $installedLanguages)) {
                $notInstalledLanguages ++;
                continue;
            }

            $languages[] = $supportedLanguage;
        }

        $responseData = new \stdClass();
        $responseData->languages = $languages;
        $responseData->notInstalledLanguages = $notInstalledLanguages;

        return $responseData;
    }
    
    public function getInstalledLanguages() {
        $installedLanguages = Http::get($this->pythonService . ':8678/models/list');
        $installedLanguages = json_decode($installedLanguages);
        
        return $installedLanguages;
    }

    public function installLanguage($language, $installableLanguages) {
        if (!in_array($language, $installableLanguages, true)) {
            throw new \Exception('This language does not require install.');
        }

        $installResult = Http::post($this->pythonService . ':8678/models/install', [
            'language' => $language,
        ]);

        return $installResult;
    }

    public function deleteInstalledLanguages() {
        Http::delete($this->pythonService . ':8678/models/remove');

        return true;
    }
}