<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\VocabularyJmdict;

class VocabularyJmdictReading extends Model
{
    use HasFactory;
    
    protected $table = 'dictionary_ja_jmdict_readings';

    protected $fillable = [

    ];

    public function VocabularyJmdict() {
        return $this->belongsTo(VocabularyJmdict::class, 'dictionary_ja_jmdict_id', 'id');
    }
}
