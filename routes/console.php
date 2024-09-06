<?php

use Illuminate\Support\Facades\Schedule;


Schedule::command('app:create-backup')->everyFourMinutes();