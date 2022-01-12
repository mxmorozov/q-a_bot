<?php
/**
 * Created by PhpStorm.
 * User: Maksim Morozov <maxpower656@gmail.com>
 * Date: 11.01.2022
 * Time: 23:26
 */

namespace App\Models\Enums;

enum VoteValue: int
{
    case THUMBS_UP = 1;
    case THUMBS_DOWN = -1;
}
