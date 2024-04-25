<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class LanguageService {
    // stores the python service container's name
    private $pythonService;

    function __construct() {
        $this->pythonService = env('PYTHON_CONTAINER_NAME', 'linguacafe-python-service');
    }

    public function selectLanguage($user, $language) {
        $installedLanguages = $this->getInstalledLanguages();
        $installableLanguages = config('linguacafe.languages.supported_languages_with_required_install');

        /*
            This is an extra protection, to avoid switching to not installed
            languages. Since this should never happen in the software, it does not
            throw an exception.
        */
        if (in_array($language, $installableLanguages, true) && !in_array($language, $installedLanguages, true)) {
            return false;
        }

        $user->selected_language = strtolower($language);
        $user->save();
        
        return true;
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

        // Download KanjiVG
        if ($language == 'Japanese') {
            $filePath = Storage::path('temp/kanjivg.zip');
            $extractPath = Storage::path('temp/kanjivg');
            File::delete($filePath);
            Storage::deleteDirectory('temp/kanjivg');
            Storage::deleteDirectory('images/kanjivg');

            $file = file_get_contents("https://github.com/KanjiVG/kanjivg/archive/master.zip");
            file_put_contents($filePath, $file);

            $zip = new \ZipArchive();
            $zipFile = $zip->open($filePath);
            if ($zipFile === TRUE) {
                $zip->extractTo($extractPath);
                $zip->close();

                Storage::move('temp/kanjivg/kanjivg-master/kanji', 'images/kanjivg');
                Storage::deleteDirectory('temp/kanjivg');
                File::delete($filePath);
            } else {
                throw new \Exception('KanjiVG zip file could not be extracted.');
            }
        }

        return $installResult;
    }

    public function deleteInstalledLanguages($user, $installableLanguages) {
        /*
            Reset selected language to the default spanish, 
            so the user won't have a language selected that has been uninstalled.
        */
        if (in_array(ucfirst($user->selected_language), $installableLanguages)) {
            $user->selected_language = 'spanish';
            $user->save();
        }

        // delete KanjiVG files
        Storage::deleteDirectory('images/kanjivg');

        // delete python language models
        $uninstallResult = Http::delete($this->pythonService . ':8678/models/remove');

        return $uninstallResult;
    }
}