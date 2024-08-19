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
            'Danish',
            'Dutch',
            'English',
            'Finnish',
            'French',
            'German',
            'Italian',
            'Japanese',
            'Korean',
            'Latin',
            'Norwegian',
            'Portuguese',
            'Romanian',
            'Russian',
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
        $openSansLanguages = [
            'Croatian',
            'Czech',
            'Greek',
            'Macedonian',
            'Polish',
            'Slovenian',
        ];

        $fontType = FontType
            ::where('filename', 'DefaultOpenSans.ttf')
            ->first();

        if ($fontType) {
            $fontType->languages = json_encode($openSansLanguages);
            $fontType->save();
        } else {
            $fontType = new FontType();
            $fontType->name = 'OpenSans';
            $fontType->filename = 'DefaultOpenSans.ttf';
            $fontType->languages = json_encode($openSansLanguages);
            $fontType->default = true;
            $fontType->save();
        }
    }
}
