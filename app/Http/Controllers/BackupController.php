<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class BackupController extends Controller
{
    public function createBackup() {
        try {
            $exitCode = Artisan::call('app:create-backup');
        } catch(\Exception $e) {
            abort(500, 'An error has occurred while exporting the database.');
        }

        return response()->json([
            'exitCode' =>  $exitCode,
        ], 200);
    }
}
