<?php

namespace App\Enums;

enum ChapterProcessingStatusEnum: string
{
    case UNPROCESSED = 'unprocessed';
    case PROCESSED = 'processed';
    case FAILED = 'failed';
}