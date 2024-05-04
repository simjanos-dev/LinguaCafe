<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;
use App\Models\FontType;

class FontTypeSeeder extends Seeder
{
    public function run()
    {
        // Noto sans JP
        $notoSansJpLanguages = [
            'Croatian',
            'Danish',
            'Czech',
            'Dutch',
            'English',
            'Finnish',
            'French',
            'German',
            'Greek',
            'Italian',
            'Japanese',
            'Korean',
            'Latin',
            'Macedonian',
            'Norwegian',
            'Polish',
            'Portuguese',
            'Romanian',
            'Russian',
            'Slovenian',
            'Spanish',
            'Swedish',
            'Thai',
            'Turkish',
            'Ukrainian',
            'Welsh',
        ];

        $fontType = DB
            ::table('font_types')
            ->where('filename', 'NotoSansJP-Regular.otf')
            ->first();

        if ($fontType) {
            $fontType->languages = json_encode($notoSansJpLanguages);
            $fontType->save();
        } else {
            $fontType = new FontType();
            $fontType->name = 'NotoSans JP';
            $fontType->filename = 'NotoSansJP-Regular.otf';
            $fontType->languages = json_encode($notoSansJpLanguages);
            $fontType->default = true;
            $fontType->save();
        }

        // Noto sans ZH
        $notoSansZhLanguages = [
            'Chinese'
        ];

        $fontType = DB
            ::table('font_types')
            ->where('filename', 'NotoSansSC-Regular.ttf')
            ->first();

        if ($fontType) {
            $fontType->languages = json_encode($notoSansZhLanguages);
            $fontType->save();
        } else {
            $fontType = new FontType();
            $fontType->name = 'NotoSans ZH';
            $fontType->filename = 'NotoSansSC-Regular.ttf';
            $fontType->languages = json_encode($notoSansZhLanguages);
            $fontType->default = true;
            $fontType->save();
        }
    }
}
