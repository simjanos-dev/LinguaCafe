<?php

use Illuminate\Support\Facades\Schedule;


Schedule::command('app:create-backup')->cron(env('BACKUP_INTERVAL'));