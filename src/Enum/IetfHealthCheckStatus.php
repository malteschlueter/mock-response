<?php

declare(strict_types=1);

namespace App\Enum;

enum IetfHealthCheckStatus: string
{
    case Pass = 'pass';
    case Fail = 'fail';
    case Warn = 'warn';
}
