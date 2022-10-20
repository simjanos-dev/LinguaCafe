<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\VocabularyJmdictWord;
use App\Models\VocabularyJmdictReading;

class VocabularyJmdict extends Model
{
    use HasFactory;
    
    protected $table = 'dictionary_ja_jmdict';

    protected $fillable = [

    ];

    public function words() {
        return $this->hasMany(VocabularyJmdictWord::class, 'dictionary_ja_jmdict_id');
    }

    public function readings() {
        return $this->hasMany(VocabularyJmdictReading::class, 'dictionary_ja_jmdict_id');
    }
    
}
