<?php

namespace App\Enums;

enum SeriesFormat: string
{
    case Television = 'television';
    case Streaming = 'streaming';
    case Web = 'web';
    case Audio = 'audio';
    case Other = 'other';
}
