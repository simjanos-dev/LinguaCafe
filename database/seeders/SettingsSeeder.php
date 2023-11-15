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
        $setting = Setting::where('name', 'deeplApiKey')->first();
        if (!$setting) {
            DB::table('settings')->insert([
                'name' => 'deeplApiKey',
                'value' => json_encode('00000000-aaaa-aaaa-aaaa-000aaaa000aa:00')
            ]);
        }

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
