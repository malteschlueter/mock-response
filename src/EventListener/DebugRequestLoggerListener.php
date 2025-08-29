<?php

declare(strict_types=1);

namespace App\EventListener;

use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\RequestEvent;

#[AsEventListener(event: RequestEvent::class)]
final readonly class DebugRequestLoggerListener
{
    public function __construct(
        private LoggerInterface $debugRequestLogger,
        #[Autowire(param: 'app.debug_secret')]
        private ?string $debugSecret,
    ) {
    }

    public function __invoke(RequestEvent $event): void
    {
        $request = $event->getRequest();

        $headerDebugSecret = $request->headers->get('X-Debug-Secret');

        if (
            $headerDebugSecret === null
            || $this->debugSecret !== $headerDebugSecret
        ) {
            return;
        }

        $this->debugRequestLogger->info('Matched route "{route}".', [
            'route' => $request->attributes->get('_route') ?? 'n/a',
            'request_uri' => $request->getUri(),
            'method' => $request->getMethod(),
            'cookies' => $request->cookies->all(),
        ]);
    }
}
