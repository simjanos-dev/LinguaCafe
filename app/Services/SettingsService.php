<?php

namespace App\Services;

use App\Models\Setting;

class SettingsService {
    
    public function __construct() {
    }

    public function isJellyfinEnabled() {
        $isJellyfinEnabled = Setting
            ::select('value', 'name')
            ->where('user_id', -1)
            ->where('name', 'jellyfinEnabled')
            ->first();

        if (!$isJellyfinEnabled) {
            throw new \Exception('Missing jellyfinEnabled setting. This should never occur.');
        }

        return json_decode($isJellyfinEnabled->value);
    }

    public function getAnkiSettings() {
        $ankiSettings = Setting
            ::select('value', 'name')
            ->where('user_id', -1)
            ->whereIn('name', ['ankiAutoAddCards', 'ankiShowNotifications'])
            ->get()
            ->keyBy('name')
            ->map(function ($item, $key) {
                return json_decode($item->value);
            });

        if ($ankiSettings->isEmpty()) {
            throw new \Exception('Missing anki settings. This should never occur.');
        }

        return $ankiSettings;
    }

    public function getGlobalSettingsByName($settingNames) {
        $settings = Setting
            ::select('value', 'name')
            ->where('user_id', -1)
            ->whereIn('name', $settingNames)
            ->get()
            ->keyBy('name')
            ->map(function ($item, $key) {
                return json_decode($item->value);
            });

        if ($settings->isEmpty()) {
            throw new \Exception('No settings were found in the database.');
        }

        return $settings;
    }

    public function updateGlobalSettings($settings) {
        foreach ($settings as $settingName => $settingValue) {
            $setting = Setting
                ::where('name', $settingName)
                ->where('user_id', -1)
                ->first();

            if ($setting) {
                $setting->value = json_encode($settingValue);
                $setting->save();
            }
        }

        return true;
    }

    public function getUserSettingsByName($userId, $settingNames) {
        $settings = Setting
            ::select('value', 'name')
            ->where('user_id', $userId)
            ->whereIn('name', $settingNames)
            ->get()
            ->keyBy('name')
            ->map(function ($item, $key) {
                return json_decode($item->value);
            });

        if ($settings->isEmpty()) {
            return null;
        }

        return $settings;
    }

    public function updateUserSettings($userId, $settings) {
        foreach ($settings as $settingName => $settingValue) {
            $setting = Setting
                ::where('name', $settingName)
                ->where('user_id', $userId)
                ->first();

            if (!$setting) {
                $setting = new Setting();
                $setting->user_id = $userId;
                $setting->name = $settingName;
            }

            $setting->value = json_encode($settingValue);
            $setting->save();                
        }

        return true;
    }
}