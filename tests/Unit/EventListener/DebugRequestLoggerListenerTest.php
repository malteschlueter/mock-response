<?php

declare(strict_types=1);

namespace App\Tests\Unit\EventListener;

use App\EventListener\DebugRequestLoggerListener;
use App\Kernel;
use App\Tests\Unit\Fake\TestLogger;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

final class DebugRequestLoggerListenerTest extends TestCase
{
    /**
     * @dataProvider provideNotLoggingExpectations
     */
    public function test_foo(?string $debugSecret): void
    {
        $listener = new DebugRequestLoggerListener($logger = new TestLogger(), $debugSecret);

        $listener->__invoke(
            new RequestEvent(
                new Kernel('test', true),
                new Request(),
                HttpKernelInterface::MAIN_REQUEST
            )
        );

        self::assertFalse($logger->hasInfoMessage());
    }

    public function provideNotLoggingExpectations(): iterable
    {
        yield 'No debug secret' => [null];
        yield 'False debug secret' => ['some-false-secret'];
    }

    public function test_bar(): void
    {
        $listener = new DebugRequestLoggerListener($logger = new TestLogger(), 'correct-secret');

        $listener->__invoke(
            new RequestEvent(
                new Kernel('test', true),
                new Request(server: [
                    'HTTP_X_DEBUG_SECRET' => 'correct-secret',
                ]),
                HttpKernelInterface::MAIN_REQUEST
            )
        );

        self::assertTrue($logger->hasInfoMessage());
    }
}
