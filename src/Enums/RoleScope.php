<?php

namespace App\Enums;

enum RoleScope: string
{
    case SELF = 'self';
    case PATROL = 'patrol';
    case TROOP = 'troop';
}