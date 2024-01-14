<?php

return [

    // it is the same as config.js, which should be
    // deleted and it should request this config file
    // instead
    'database_name_language_codes' => [
        'japanese' => 'jp',
        'norwegian' => 'no',
        'german' => 'de',
        'spanish' => 'es',
        'chinese' => 'zh',
        'dutch' => 'nl',
        'finnish' => 'fi',
        'french' => 'fr',
        'italian' => 'it',
        'korean' => 'ko',
        'swedish' => 'sv',
        'ukrainian' => 'ua',
        'russian' => 'ru',

        'Japanese' => 'jp',
        'Norwegian' => 'no',
        'German' => 'de',
        'Spanish' => 'es',
        'Chinese' => 'zh',
        'Dutch' => 'nl',
        'Finnish' => 'fi',
        'French' => 'fr',
        'Italian' => 'it',
        'Korean' => 'ko',
        'Swedish' => 'sv',
        'Ukrainian' => 'ua',
        'Russian' => 'ru'
    ],

    /*
        These are needed to recognize dict cc dictionary
        files inside the dictionaries directory. These 
        language codes can be found in the first line of 
        dict cc dictionary files.
    */
    'dict_cc_language_codes' => [
        'NO' => 'norwegian',
        'DE' => 'german',
        'NL' => 'dutch',
        'ES' => 'spanish',
        'FI' => 'finnish',
        'FR' => 'french',
        'IT' => 'italian',
        'SV' => 'swedish',
        'RU' => 'russian',
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
        'spanish' => 'ES',
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
        'spanish' => 'ES',
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
