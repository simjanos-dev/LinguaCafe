<?php

namespace App\Services;

use App\Models\Book;
use App\Models\Setting;

class SettingsService {
    
    public function __construct() {
    }

    public function getSettingsByName($settingNames) {
        $settings = Setting
            ::select('value', 'name')
            ->whereIn('name', $settingNames)
            ->get()
            ->keyBy('name')
            ->map(function ($item, $key) {
                return json_decode($item->value);
            });

        if ($settings->isEmpty()) {
            throw new \Exception('No setting were found in the database');
        }

        return $settings;
    }

    public function updateSettings($settings) {
        foreach ($settings as $settingName => $settingValue) {
            $setting = Setting
                ::where('name', $settingName)
                ->first();

            if ($setting) {
                $setting->value = json_encode($settingValue);
                $setting->save();
            }
        }
    }
}