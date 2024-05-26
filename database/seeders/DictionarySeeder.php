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
                'enabled' => false
            ]);
        }
    }
}
