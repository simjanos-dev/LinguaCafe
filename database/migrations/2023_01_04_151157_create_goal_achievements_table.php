<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGoalAchievementsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('goal_achievements', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->string('language');
            $table->integer('goal_id');
            $table->integer('achieved_quantity');
            $table->integer('goal_quantity');
            $table->date('day');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('goal_achievements');
    }
}
