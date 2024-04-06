<?php

namespace App\Http\Controllers;

// services
use App\Services\SettingsService;

// request classes
use App\Http\Requests\Settings\GetSettingsByNameRequest;
use App\Http\Requests\Settings\UpdateSettingsRequest;

class SettingsController extends Controller
{
    private $settingsService;

    public function __construct(SettingsService $settingsService) {
        $this->middleware('auth');
        $this->settingsService = $settingsService;
    }

    // returns an array of settings
    public function getSettingsByName(GetSettingsByNameRequest $request) {
        $settingNames = $request->post('settingNames');

        try {
            $settings = $this->settingsService->getGlobalSettingsByName($settingNames);
        } catch (\Exception $e) {
            abort(500, $e->getMessage());
        }
        
        return response()->json($settings, 200);
    }

    // saves an array of settings
    public function updateSettings(UpdateSettingsRequest $request) {
        $settings = $request->post('settings');

        try {
            $settings = $this->settingsService->updateGlobalSettings($settings);
        } catch (\Exception $e) {
            abort(500, $e->getMessage());
        }
        
        return response()->json('Settings have been updated successfully.', 200);
    }
}
