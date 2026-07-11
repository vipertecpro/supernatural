<?php

namespace App\Enums;

enum WorkType: string
{
    case Series = 'series';
    case Film = 'film';
    case Book = 'book';
    case Comic = 'comic';
    case Game = 'game';
    case Special = 'special';
    case Other = 'other';
}
