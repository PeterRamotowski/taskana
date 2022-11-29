<?php

namespace App\Service\Enum;

enum RequestSource: string
{
    case BODY = 'body';
    case POST = 'post';
}