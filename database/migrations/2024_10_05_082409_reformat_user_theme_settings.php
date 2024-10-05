<?php

use App\Models\User;
use App\Models\Setting;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    private $themeSettingNames = [
        'lightTheme-background',
        'lightTheme-foreground',
        'lightTheme-navigation',
        'lightTheme-primary',
        'lightTheme-gray',
        'lightTheme-gray2',
        'lightTheme-gray3',
        'lightTheme-customBorder',
        'lightTheme-error',
        'lightTheme-info',
        'lightTheme-success',
        'lightTheme-warning',
        'lightTheme-text',
        'lightTheme-textDark',
        'lightTheme-newWord',
        'lightTheme-highlightedWordLevel1',
        'lightTheme-highlightedWordLevel2',
        'lightTheme-highlightedWordLevel3',
        'lightTheme-highlightedWordLevel4',
        'lightTheme-highlightedWordLevel5',
        'lightTheme-highlightedWordLevel6',
        'lightTheme-highlightedWordLevel7',
        'lightTheme-ignoredWordTextColor',
        'lightTheme-readerWordSelection',
        'lightTheme-highlightedWordText',

        'darkTheme-background',
        'darkTheme-foreground',
        'darkTheme-navigation',
        'darkTheme-primary',
        'darkTheme-gray',
        'darkTheme-gray2',
        'darkTheme-gray3',
        'darkTheme-customBorder',
        'darkTheme-error',
        'darkTheme-info',
        'darkTheme-success',
        'darkTheme-warning',
        'darkTheme-text',
        'darkTheme-textDark',
        'darkTheme-newWord',
        'darkTheme-highlightedWordLevel1',
        'darkTheme-highlightedWordLevel2',
        'darkTheme-highlightedWordLevel3',
        'darkTheme-highlightedWordLevel4',
        'darkTheme-highlightedWordLevel5',
        'darkTheme-highlightedWordLevel6',
        'darkTheme-highlightedWordLevel7',
        'darkTheme-ignoredWordTextColor',
        'darkTheme-readerWordSelection',
        'darkTheme-highlightedWordText',
    ];

    public function up(): void
    {
        DB::transaction(function() {
            $users = User::all();
            
            foreach($users as $user) {
                // format previous settings into a single json object
                $settings = Setting::query()
                    ->where('user_id', $user->id)
                    ->whereIn('name', $this->themeSettingNames)
                    ->pluck('value', 'name');

                if ($settings->isEmpty()) {
                    continue;
                }

                $lightThemeSettings = $settings->filter(function($value, $key) {
                    return str_contains($key, 'lightTheme-');
                })->mapWithKeys(function($value, $key) {
                    return [str_replace('lightTheme-', '', $key) => json_decode($value)];
                });

                $darkThemeSettings = $settings->filter(function($value, $key) {
                    return str_contains($key, 'darkTheme-');
                })->mapWithKeys(function($value, $key) {
                    return [str_replace('darkTheme-', '', $key) => json_decode($value)];
                });

                $newSetting = new Setting();
                $newSetting->user_id = $user->id;
                $newSetting->name = 'vuetifyThemes';
                $newSetting->value = json_encode([
                        'light' => $lightThemeSettings,
                        'dark' => $darkThemeSettings
                    ]);
                $newSetting->save();

                // delete old setting records
                Setting::query()
                    ->where('user_id', $user->id)
                    ->whereIn('name', $this->themeSettingNames)
                    ->delete();
            }
        });
    }

    public function down(): void
    {
        //
    }
};
