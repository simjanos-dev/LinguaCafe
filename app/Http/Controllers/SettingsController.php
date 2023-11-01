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
            ::select('value', 'name')
            ->whereIn('name', $settingNames)
            ->get()
            ->keyBy('name')
            ->map(function ($item, $key) {
                return json_decode($item->value);
            });

        return json_encode($settings);
    }

    public function saveSettings(Request $request) {
        $settings = $request->post('settings');

        foreach ($settings as $settingName => $settingValue) {
            $setting = Setting
                ::where('name', $settingName)
                ->first();

            if ($setting) {
                $setting->value = json_encode($settingValue);
                $setting->save();
            }
        }

        return 'success';
    }
}
