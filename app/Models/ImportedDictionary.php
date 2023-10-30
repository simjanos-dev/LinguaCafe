<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/*
    This is a custom model for multiple tables which the 
    users have imported. The t
*/
class ImportedDictionary extends Model
{
    use HasFactory;

    public function scopeFromTable($query, $tableName) {
        $query->from($tableName);

        /*
            Example usage:
            ImportedDictionary::fromTable($tableName)->get();
        */

    }

}
