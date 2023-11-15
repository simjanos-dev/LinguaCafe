<?php

return [
    'languages' => [
        'japanese',
        'norwegian',
        'german'
    ],

    'language_short_forms' => [
        'japanese' => 'jp',
        'norwegian' => 'no',
        'german' => 'de',

        'Japanese' => 'jp',
        'Norwegian' => 'no',
        'German' => 'de'
    ],


    // ISO_639-1 codes, with a few exceptions (en-GB)
    'deepl_language_codes' => [
        'japanese' => 'ja',
        'norwegian' => 'nb',
        'german' => 'de',
        'english' => 'en-GB',

        'Japanese' => 'ja',
        'Norwegian' => 'nb',
        'German' => 'de',
        'english' => 'en-GB'
    ],

    'wordsToSkip' => ['。', '、', ':', '？', '！', '＜', '＞', '：', ' ', '「', '」', '（', '）', '｛', '｝', '≪', '≫', '〈', '〉',
        '《', '》','【', '】', '『', '』', '〔', '〕', '［', '］', '・', '?', '(', ')', ' ', ' NEWLINE ', '.', '%', '-',
        '«', '»', "'", '’', '–', 'NEWLINE', 'newline', ' ', "\r", "\n", "\r\n", '	', "\r\n　"],

    'tokensWithNoSpaceBefore' => ['.', ',', '?', '!', '\'', '"', '‘', '’'],
    'tokensWithNoSpaceAfter' => ['‘', '’'],
];
