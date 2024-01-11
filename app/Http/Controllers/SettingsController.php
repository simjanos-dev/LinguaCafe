<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Setting;

class SettingsController extends Controller
{
    // Returns an array of settings.
    public function getSettingsByName(Request $request) {
        $settingNames = $request->post('settingNames');

        $settings = Setting
            ::whereIn('name', $settingNames)
            ->pluck('value', 'name')
            ->map(function ($value) {
                return json_decode($value);
            });

        return json_encode($settings);
    }

    public function saveSettings(Request $request) {
        $settings = $request->post('settings');

        Setting::whereIn('name', array_keys($settings))
            ->get()
            ->each(function ($setting) use ($settings) {
                $setting->update(['value' => json_encode($settings[$setting->name])]);
            });

        return 'success';
    }
}
