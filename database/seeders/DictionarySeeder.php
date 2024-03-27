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
                'source_language' => 'japanese',
                'target_language' => 'english',
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
                'source_language' => 'japanese',
                'target_language' => 'english',
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
                'source_language' => 'norwegian',
                'target_language' => 'english',
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
                'source_language' => 'german',
                'target_language' => 'english',
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
                'source_language' => 'spanish',
                'target_language' => 'english',
                'color' => '#92B9E2',
                'imported' => false,
                'enabled' => false
            ]);
        }

        $dictionary = DB::table('dictionaries')->where('name', 'DeepL ZH')->first();
        if (!$dictionary) {
            DB::table('dictionaries')->insert([
                'name' => 'DeepL ZH',
                'database_table_name' => 'API',
                'source_language' => 'chinese',
                'target_language' => 'english',
                'color' => '#92B9E2',
                'imported' => false,
                'enabled' => false
            ]);
        }

        $dictionary = DB::table('dictionaries')->where('name', 'DeepL NL')->first();
        if (!$dictionary) {
            DB::table('dictionaries')->insert([
                'name' => 'DeepL NL',
                'database_table_name' => 'API',
                'source_language' => 'dutch',
                'target_language' => 'english',
                'color' => '#92B9E2',
                'imported' => false,
                'enabled' => false
            ]);
        }

        $dictionary = DB::table('dictionaries')->where('name', 'DeepL FI')->first();
        if (!$dictionary) {
            DB::table('dictionaries')->insert([
                'name' => 'DeepL FI',
                'database_table_name' => 'API',
                'source_language' => 'finnish',
                'target_language' => 'english',
                'color' => '#92B9E2',
                'imported' => false,
                'enabled' => false
            ]);
        }

        $dictionary = DB::table('dictionaries')->where('name', 'DeepL FR')->first();
        if (!$dictionary) {
            DB::table('dictionaries')->insert([
                'name' => 'DeepL FR',
                'database_table_name' => 'API',
                'source_language' => 'french',
                'target_language' => 'english',
                'color' => '#92B9E2',
                'imported' => false,
                'enabled' => false
            ]);
        }

        $dictionary = DB::table('dictionaries')->where('name', 'DeepL IT')->first();
        if (!$dictionary) {
            DB::table('dictionaries')->insert([
                'name' => 'DeepL IT',
                'database_table_name' => 'API',
                'source_language' => 'italian',
                'target_language' => 'english',
                'color' => '#92B9E2',
                'imported' => false,
                'enabled' => false
            ]);
        }

        $dictionary = DB::table('dictionaries')->where('name', 'DeepL KO')->first();
        if (!$dictionary) {
            DB::table('dictionaries')->insert([
                'name' => 'DeepL KO',
                'database_table_name' => 'API',
                'source_language' => 'korean',
                'target_language' => 'english',
                'color' => '#92B9E2',
                'imported' => false,
                'enabled' => false
            ]);
        }

        $dictionary = DB::table('dictionaries')->where('name', 'DeepL RU')->first();
        if (!$dictionary) {
            DB::table('dictionaries')->insert([
                'name' => 'DeepL RU',
                'database_table_name' => 'API',
                'source_language' => 'russian',
                'target_language' => 'english',
                'color' => '#92B9E2',
                'imported' => false,
                'enabled' => false
            ]);
        }

        $dictionary = DB::table('dictionaries')->where('name', 'DeepL SV')->first();
        if (!$dictionary) {
            DB::table('dictionaries')->insert([
                'name' => 'DeepL SV',
                'database_table_name' => 'API',
                'source_language' => 'swedish',
                'target_language' => 'english',
                'color' => '#92B9E2',
                'imported' => false,
                'enabled' => false
            ]);
        }

        $dictionary = DB::table('dictionaries')->where('name', 'DeepL UA')->first();
        if (!$dictionary) {
            DB::table('dictionaries')->insert([
                'name' => 'DeepL UA',
                'database_table_name' => 'API',
                'source_language' => 'ukrainian',
                'target_language' => 'english',
                'color' => '#92B9E2',
                'imported' => false,
                'enabled' => false
            ]);
        }

        $dictionary = DB::table('dictionaries')->where('name', 'DeepL CS')->first();
        if (!$dictionary) {
            DB::table('dictionaries')->insert([
                'name' => 'DeepL CS',
                'database_table_name' => 'API',
                'source_language' => 'czech',
                'target_language' => 'english',
                'color' => '#92B9E2',
                'imported' => false,
                'enabled' => false
            ]);
        }

        $dictionary = DB::table('dictionaries')->where('name', 'DeepL CS')->first();
        if (!$dictionary) {
            DB::table('dictionaries')->insert([
                'name' => 'DeepL CS',
                'database_table_name' => 'API',
                'source_language' => 'czech',
                'target_language' => 'english',
                'color' => '#92B9E2',
                'imported' => false,
                'enabled' => false
            ]);
        }

        $dictionary = DB::table('dictionaries')->where('name', 'DeepL EL')->first();
        if (!$dictionary) {
            DB::table('dictionaries')->insert([
                'name' => 'DeepL EL',
                'database_table_name' => 'API',
                'source_language' => 'greek',
                'target_language' => 'english',
                'color' => '#92B9E2',
                'imported' => false,
                'enabled' => false
            ]);
        }

        $dictionary = DB::table('dictionaries')->where('name', 'DeepL EN')->first();
        if (!$dictionary) {
            DB::table('dictionaries')->insert([
                'name' => 'DeepL EN',
                'database_table_name' => 'API',
                'source_language' => 'english',
                'target_language' => 'spanish',
                'color' => '#92B9E2',
                'imported' => false,
                'enabled' => false
            ]);
        }
    }
}
