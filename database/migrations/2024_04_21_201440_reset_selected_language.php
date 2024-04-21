<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use App\Services\GoalService;
use App\Models\User;

class ResetSelectedLanguage extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $goalService = new GoalService();
        $users = User::get();

        foreach ($users as $user) {
            $user->selected_language = 'spanish';
            $user->save();

            $goalService->createGoalsForLanguage($user->id, 'spanish');
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
