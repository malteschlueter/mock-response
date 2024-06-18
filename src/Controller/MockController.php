<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
final class MockController extends AbstractController
{
    #[Route('/status/{code}', name: 'mock_status_code_always')]
    public function always(int $code): Response
    {
        return new Response(content: 'Response status code: ' . $code, status: $code);
    }

    #[Route('/status/{code}/random', name: 'mock_status_code_randomly')]
    public function randomly(int $code): Response
    {
        if ((bool) random_int(0, 1)) {
            $code = 200;
        }

        $content = $code . ' - ' . Response::$statusTexts[$code] ?? 'Unknown status code';

        return new Response(content: $content, status: $code);
    }
}
