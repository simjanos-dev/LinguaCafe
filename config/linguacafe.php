<?php

return [

    'languages' => [
        /*
            These are language codes that Jellyfin uses for subtitles. You can find out what
            a jellyfin language code is by going to the media player, and starting a video
            with a new language subtitle. If that new language is not added here, you will see
            a javascript log about the unsupported languages code.
        */
        'jellyfin_language_codes' => [
            'jpn' => 'japanese',
            'eng' => 'english',
            'nor' => 'norwegian',
            'ger' => 'german',
            'spa' => 'spanish',
            'chi' => 'chinese',
            'dut' => 'dutch',
            'fin' => 'finnish',
            'fre' => 'french',
            'ita' => 'italian',
            'kor' => 'korean',
            'swe' => 'swedish',
            'ukr' => 'ukrainian',
            'rus' => 'russian'
        ],

        /*
            These language codes are used to create 
            database tables for dictionaries.
        */
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
    ],
    'words_to_skip' => ['。', '、', ':', '？', '！', '＜', '＞', '：', ' ', '「', '」', '（', '）', '｛', '｝', '≪', '≫', '〈', '〉',
        '《', '》','【', '】', '『', '』', '〔', '〕', '［', '］', '・', '?', '(', ')', ' ', ' NEWLINE ', '.', '%', '-',
        '«', '»', "'", '’', '–', 'NEWLINE', 'newline', ' ', "\r", "\n", "\r\n", '	', "\r\n　"],

    'tokens_with_no_space_before' => ['.', ',', '?', '!', '\'', '"', '‘', '’'],
    'tokens_with_no_space_after' => ['‘', '’'],
];
