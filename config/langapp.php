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
        'japanese' => 'JA',
        'norwegian' => 'NB',
        'german' => 'DE',
        'english' => 'EN-GB',
        'chinese' => 'ZH',
        'dutch' => 'NL',
        'finnish' => 'FI',
        'french' => 'FR',
        'italian' => 'IT',
        'korean' => 'KO',

        'Japanese' => 'JA',
        'Norwegian' => 'NB',
        'German' => 'DE',
        'English' => 'EN-GB',
        'Chinese' => 'ZH',
        'Dutch' => 'NL',
        'Finnish' => 'FI',
        'French' => 'FR',
        'Italian' => 'IT',
        'Korean' => 'KO',
    ],

    'wordsToSkip' => ['。', '、', ':', '？', '！', '＜', '＞', '：', ' ', '「', '」', '（', '）', '｛', '｝', '≪', '≫', '〈', '〉',
        '《', '》','【', '】', '『', '』', '〔', '〕', '［', '］', '・', '?', '(', ')', ' ', ' NEWLINE ', '.', '%', '-',
        '«', '»', "'", '’', '–', 'NEWLINE', 'newline', ' ', "\r", "\n", "\r\n", '	', "\r\n　"],

    'tokensWithNoSpaceBefore' => ['.', ',', '?', '!', '\'', '"', '‘', '’'],
    'tokensWithNoSpaceAfter' => ['‘', '’'],
];
