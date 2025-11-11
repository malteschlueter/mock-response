<?php

declare(strict_types=1);

namespace App\Controller;

use App\Enum\SizeMockType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
final class SizeController extends AbstractController
{
    #[Route('/size/{size}', name: 'mock_size_always')]
    public function always(SizeMockType $size): Response
    {
        $content = match ($size) {
            SizeMockType::s100KB => str_repeat('A', 102400),
            SizeMockType::s1MB => str_repeat('A', 1048576),
            default => throw new \RuntimeException('Unexpected size type'),
        };

        return new Response(content: $content);
    }
}
