<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class LanguageService {
    // stores the python service container's name
    private $pythonService;

    function __construct() {
        $this->pythonService = env('PYTHON_CONTAINER_NAME', 'linguacafe-python-service');
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