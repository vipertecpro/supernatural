<?php

namespace App\Enums;

enum RelationshipDirection: string
{
    case Directed = 'directed';
    case Undirected = 'undirected';
}
