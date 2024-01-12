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
        'english' => 'EN-US',
        'chinese' => 'ZH',
        'dutch' => 'NL',
        'finnish' => 'FI',
        'french' => 'FR',
        'italian' => 'IT',
        'korean' => 'KO',
        'swedish' => 'SV',
        'ukrainian' => 'UK',
        'russian' => 'RU',

        'Japanese' => 'JA',
        'Norwegian' => 'NB',
        'German' => 'DE',
        'English' => 'EN-US',
        'Chinese' => 'ZH',
        'Dutch' => 'NL',
        'Finnish' => 'FI',
        'French' => 'FR',
        'Italian' => 'IT',
        'Korean' => 'KO',
        'Swedish' => 'SV',
        'Ukrainian' => 'UK',
        'Russian' => 'RU',
    ],

    'wordsToSkip' => ['。', '、', ':', '？', '！', '＜', '＞', '：', ' ', '「', '」', '（', '）', '｛', '｝', '≪', '≫', '〈', '〉',
        '《', '》','【', '】', '『', '』', '〔', '〕', '［', '］', '・', '?', '(', ')', ' ', ' NEWLINE ', '.', '%', '-',
        '«', '»', "'", '’', '–', 'NEWLINE', 'newline', ' ', "\r", "\n", "\r\n", '	', "\r\n　"],

    'tokensWithNoSpaceBefore' => ['.', ',', '?', '!', '\'', '"', '‘', '’'],
    'tokensWithNoSpaceAfter' => ['‘', '’'],
];
