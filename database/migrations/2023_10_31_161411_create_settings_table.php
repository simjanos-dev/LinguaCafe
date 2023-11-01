<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id')->default(-1);
            $table->string('name', 256);
            $table->text('value');
            $table->timestamps();
        });

        DB::table('settings')->insert([
            'name' => 'deeplApiKey',
            'value' => json_encode('00000000-aaaa-aaaa-aaaa-000aaaa000aa:00')
        ]);


        DB::table('settings')->insert([
            'name' => 'jellyfinHost',
            'value' => json_encode('http://jellyfin:8096')
        ]);

        DB::table('settings')->insert([
            'name' => 'jellyfinApiKey',
            'value' => json_encode('00a0a000aaa00000a00aaaaa00a00a0a')
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('settings');
    }
}
