<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;
use App\Models\Setting;

class SettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // dictionaries
        $dictionary = DB::table('dictionaries')->where('name', 'JMDict')->first();
        if (!$dictionary) {
            DB::table('dictionaries')->insert([
                'name' => 'JMDict',
                'database_table_name' => 'dict_jp_jmdict',
                'language' => 'japanese',
                'color' => '#74E39A',
                'imported' => false,
                'enabled' => false
            ]);
        }

        $dictionary = DB::table('dictionaries')->where('name', 'DeepL JP')->first();
        if (!$dictionary) {
            DB::table('dictionaries')->insert([
                'name' => 'DeepL JP',
                'database_table_name' => 'API',
                'language' => 'japanese',
                'color' => '#92B9E2',
                'imported' => false,
                'enabled' => false
            ]);
        }

        $dictionary = DB::table('dictionaries')->where('name', 'DeepL NO')->first();
        if (!$dictionary) {
            DB::table('dictionaries')->insert([
                'name' => 'DeepL NO',
                'database_table_name' => 'API',
                'language' => 'norwegian',
                'color' => '#92B9E2',
                'imported' => false,
                'enabled' => false
            ]);
        }

        $dictionary = DB::table('dictionaries')->where('name', 'DeepL DE')->first();
        if (!$dictionary) {
            DB::table('dictionaries')->insert([
                'name' => 'DeepL DE',
                'database_table_name' => 'API',
                'language' => 'german',
                'color' => '#92B9E2',
                'imported' => false,
                'enabled' => false
            ]);
        }

        // deepl api settings
        $setting = Setting::where('name', 'deeplApiKey')->first();
        if (!$setting) {
            DB::table('settings')->insert([
                'name' => 'deeplApiKey',
                'value' => json_encode('00000000-aaaa-aaaa-aaaa-000aaaa000aa:00')
            ]);
        }

        // jellyfin api settings
        $setting = Setting::where('name', 'jellyfinApiKey')->first();
        if (!$setting) {
            DB::table('settings')->insert([
                'name' => 'jellyfinApiKey',
                'value' => json_encode('00a0a000aaa00000a00aaaaa00a00a0a')
            ]);
        }

        $setting = Setting::where('name', 'jellyfinHost')->first();
        if (!$setting) {
            DB::table('settings')->insert([
                'name' => 'jellyfinHost',
                'value' => json_encode('http://jellyfin:8096')
            ]);
        }

        // anki api settings
        $setting = Setting::where('name', 'ankiConnectHost')->first();
        if (!$setting) {
            DB::table('settings')->insert([
                'name' => 'ankiConnectHost',
                'value' => json_encode('http://host.docker.internal:8765')
            ]);
        }

        $setting = Setting::where('name', 'ankiAutoAddCards')->first();
        if (!$setting) {
            DB::table('settings')->insert([
                'name' => 'ankiAutoAddCards',
                'value' => json_encode(false)
            ]);
        }

        $setting = Setting::where('name', 'ankiUpdateCards')->first();
        if (!$setting) {
            DB::table('settings')->insert([
                'name' => 'ankiUpdateCards',
                'value' => json_encode(true)
            ]);
        }

        $setting = Setting::where('name', 'ankiShowNotifications')->first();
        if (!$setting) {
            DB::table('settings')->insert([
                'name' => 'ankiShowNotifications',
                'value' => json_encode(true)
            ]);
        }

        // review srs settings
        $setting = Setting::where('name', 'reviewIntervals')->first();
        if (!$setting) {
            DB::table('settings')->insert([
                'name' => 'reviewIntervals',
                'value' => json_encode([
                    "-7" => [0],
                    "-6" => [1],
                    "-5" => [2, 3],
                    "-4" => [6, 7, 8],
                    "-3" => [15, 16, 17, 18],
                    "-2" => [37, 38, 39, 40, 41, 42],
                    "-1" => [94, 95, 96, 97, 98, 99, 100, 101],
                ])
            ]);
        }
    }
}
