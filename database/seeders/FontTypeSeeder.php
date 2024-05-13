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

        $fontType = FontType
            ::where('filename', 'DefaultNotoSansJP.otf')
            ->first();

        if ($fontType) {
            $fontType->languages = json_encode($notoSansJpLanguages);
            $fontType->save();
        } else {
            $fontType = new FontType();
            $fontType->name = 'NotoSans JP';
            $fontType->filename = 'DefaultNotoSansJP.otf';
            $fontType->languages = json_encode($notoSansJpLanguages);
            $fontType->default = true;
            $fontType->save();
        }

        // Noto sans ZH
        $notoSansZhLanguages = [
            'Chinese'
        ];

        $fontType = FontType
            ::where('filename', 'DefaultNotoSansSC.ttf')
            ->first();

        if ($fontType) {
            $fontType->languages = json_encode($notoSansZhLanguages);
            $fontType->save();
        } else {
            $fontType = new FontType();
            $fontType->name = 'NotoSans ZH';
            $fontType->filename = 'DefaultNotoSansSC.ttf';
            $fontType->languages = json_encode($notoSansZhLanguages);
            $fontType->default = true;
            $fontType->save();
        }

        // OpenSans
        $poltawskiNowLanguages = [
            'Polish',
        ];

        $fontType = FontType
            ::where('filename', 'DefaultOpenSans.ttf')
            ->first();

        if ($fontType) {
            $fontType->languages = json_encode($poltawskiNowLanguages);
            $fontType->save();
        } else {
            $fontType = new FontType();
            $fontType->name = 'OpenSans';
            $fontType->filename = 'DefaultOpenSans.ttf';
            $fontType->languages = json_encode($poltawskiNowLanguages);
            $fontType->default = true;
            $fontType->save();
        }
    }
}
