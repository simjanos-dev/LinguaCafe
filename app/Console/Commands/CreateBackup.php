<?php

namespace App\Console\Commands;

use Illuminate\Support\Carbon;
use Illuminate\Console\Command;

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
        $host = ' -h ' . env('DB_HOST');
        $port = ' -P ' . env('DB_PORT');
        $username = ' -u ' . env('DB_USERNAME');
        $password = ' -p' . env('DB_PASSWORD');
        $database = ' ' . env('DB_DATABASE');
        
        $timestamp = Carbon::now()->format('Y_m_d_h_i_s');
        
        $path = '/var/www/html/storage/backup/';
        $fileName = 'linguacafe_' . $timestamp . '.sql';
        $fullFilePath = $path . $fileName;

        $exitCode = null;
        exec(
            command: 'mysqldump --no-tablespaces' . $host . $port . $username . $password . $database . ' > ' . $fullFilePath, 
            result_code: $exitCode
        );

        return $exitCode;
    }
}
