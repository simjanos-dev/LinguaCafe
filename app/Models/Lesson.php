<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/*
!!!!!!!!!!!!!!!!!!!!!!!!

This is only here for old database migrations. This should not be used anymore.

!!!!!!!!!!!!!!!!!!!!!!!!
*/

class Lesson extends Model
{
    use HasFactory;
    
    protected $table = 'lessons';
}
