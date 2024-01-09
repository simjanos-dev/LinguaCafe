<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;

class DictionarySeeder extends Seeder
{
    /*
        This seeder adds custom dictionaries to the database. 
        These are dictionaries that do not operate from a regular 
        dictionary database table, but require custom code like 
        JMDict or DeepL API.
    */
    public function run()
    {
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

        $dictionary = DB::table('dictionaries')->where('name', 'DeepL ES')->first();
        if (!$dictionary) {
            DB::table('dictionaries')->insert([
                'name' => 'DeepL ES',
                'database_table_name' => 'API',
                'language' => 'spanish',
                'color' => '#92B9E2',
                'imported' => false,
                'enabled' => false
            ]);
        }
    }
}
