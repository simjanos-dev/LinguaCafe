<?php

return [
    'wordsToSkip' => ['。', '、', ':', '？', '！', '＜', '＞', '：', ' ', '「', '」', '（', '）', '｛', '｝', '≪', '≫', '〈', '〉',
        '《', '》','【', '】', '『', '』', '〔', '〕', '［', '］', '・', '?', '(', ')', ' ', ' NEWLINE ', '.', '%', '-',
        '«', '»', "'", '’', '–', 'NEWLINE', 'newline', ' ', "\r", "\n", "\r\n", '	', "\r\n　"],

    'tokensWithNoSpaceBefore' => ['.', ',', '?', '!', '\'', '"', '‘', '’'],
    'tokensWithNoSpaceAfter' => ['‘', '’'],

    // next_review = today + reviewIntervals[stage]
    // the date with the least amount of reviews
    // will be selected for the next review. 
    'reviewIntervals' => [
        -7 => [0],
        -6 => [1],
        -5 => [2, 3],
        -4 => [5, 6, 7, 8],
        -3 => [14, 15, 16, 18],
        -2 => [25, 26, 27, 28, 29],
        -1 => [48, 49, 50, 51, 52, 53],
    ],
];
