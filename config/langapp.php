<?php

return [
    'wordsToSkip' => ['。', '、', ':', '？', '！', '＜', '＞', '：', ' ', '「', '」', '（', '）', '｛', '｝', '≪', '≫', '〈', '〉',
        '《', '》','【', '】', '『', '』', '〔', '〕', '［', '］', '・', '?', '(', ')', ' ', ' NEWLINE ', '.', '%', '-',
        '«', '»', "'", '’', '–', 'NEWLINE', 'newline', ' ', "\r", "\n", "\r\n", '	', "\r\n　"],

    // next_review = today + reviewIntervals[stage]
    // the date with the least amount of reviews
    // will be selected for the next review. 
    'reviewIntervals' => [
        -7 => [1],
        -6 => [2],
        -5 => [4, 5],
        -4 => [8, 9, 10],
        -3 => [16, 15, 17, 18],
        -2 => [32, 30, 31, 33, 34],
        -1 => [62, 61, 60, 59, 63, 64, 65],
    ],
];
