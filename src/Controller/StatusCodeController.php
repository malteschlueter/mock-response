<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Clock\ClockAwareTrait;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
final class StatusCodeController extends AbstractController
{
    use ClockAwareTrait;

    #[Route('/status/{code}', name: 'mock_status_code_always')]
    public function always(int $code, Request $request): Response
    {
        $setCookie = $request->query->getBoolean('set-cookie');

        $response = new Response(content: 'Response status code: ' . $code, status: $code);

        if ($setCookie) {
            $response->headers->setCookie(Cookie::create(name: 'mock_status_code', value: (string) $code));
        }

        return $response;
    }

    #[Route('/status/{code}/random', name: 'mock_status_code_randomly')]
    public function randomly(int $code): Response
    {
        if ((bool) random_int(0, 1)) {
            $code = 200;
        }

        $content = $code . ' - ' . (Response::$statusTexts[$code] ?? 'Unknown status code');

        return new Response(content: $content, status: $code);
    }

    #[Route('/status/{code}/interval/{interval}/{time}', name: 'mock_status_code_interval')]
    public function interval(int $code, string $interval, int $time): Response
    {
        $now = $this->clock->now();

        $timestamp = $now->getTimestamp();

        $intervalTime = match ($interval) {
            'second' => $time,
            'minute' => $time * 60,
            'hour' => $time * 60 * 60,
            'day' => $time * 60 * 60 * 24,
            default => throw new \RuntimeException('Invalid interval'),
        };

        if ($timestamp % ($intervalTime * 2) > $intervalTime) {
            $code = 200;
        }

        $content = $code . ' - ' . (Response::$statusTexts[$code] ?? 'Unknown status code');

        return new Response(content: $content, status: $code);
    }
}
