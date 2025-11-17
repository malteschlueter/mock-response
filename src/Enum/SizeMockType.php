<?php

declare(strict_types=1);

namespace App\Enum;

enum SizeMockType: string
{
    case s100KB = '100kb';
    case s1MB = '1mb';
    case LARGE_IMAGE_INJECTED = 'large-image-injected';
}
