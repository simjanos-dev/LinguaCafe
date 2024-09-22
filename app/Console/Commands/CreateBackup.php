<?php

namespace App\Console\Commands;

use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use App\Services\BackupService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class CreateBackup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:create-backup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates a backup of the database into the storage folder.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $exitCode = (new BackupService())->createBackup();
        
        return $exitCode;
    }
}
