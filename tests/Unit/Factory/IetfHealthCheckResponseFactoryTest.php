<?php

declare(strict_types=1);

namespace App\Tests\Unit\Factory;

use App\DataTransferObject\IetfHealthCheckResponse;
use App\Enum\IetfHealthCheckStatus;
use App\Factory\IetfHealthCheckResponseFactory;
use Assert\InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class IetfHealthCheckResponseFactoryTest extends TestCase
{
    public function test_that_missing_status_throws_exception(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Status is required');

        IetfHealthCheckResponseFactory::new()
            ->create()
        ;
    }

    /**
     * @dataProvider provideOnlyStatus
     */
    public function test_simple(IetfHealthCheckStatus $expectedStatus): void
    {
        $response = IetfHealthCheckResponseFactory::new()
            ->withStatus($expectedStatus)
            ->create()
        ;

        $this->assertSame($expectedStatus, $response->status);
    }

    public function provideOnlyStatus(): iterable
    {
        yield 'pass' => [IetfHealthCheckStatus::Pass];
        yield 'fail' => [IetfHealthCheckStatus::Fail];
        yield 'warn' => [IetfHealthCheckStatus::Warn];
    }

    /**
     * @dataProvider provideStatusWithChecks
     */
    public function test_with_checks(IetfHealthCheckStatus $expectedStatus, IetfHealthCheckResponse $response): void
    {
        $this->assertEquals($expectedStatus, $response->status);
    }

    public function provideStatusWithChecks(): iterable
    {
        yield 'Pass with 1 pass check' => [
            IetfHealthCheckStatus::Pass,
            IetfHealthCheckResponseFactory::new()
                ->withChecks(IetfHealthCheckStatus::Pass, 1)
                ->create(),
        ];

        yield 'Fail with 1 fail check' => [
            IetfHealthCheckStatus::Fail,
            IetfHealthCheckResponseFactory::new()
                ->withChecks(IetfHealthCheckStatus::Fail, 1)
                ->create(),
        ];

        yield 'Fail with 1 pass check, 1 fail check' => [
            IetfHealthCheckStatus::Fail,
            IetfHealthCheckResponseFactory::new()
                ->withChecks(IetfHealthCheckStatus::Pass, 1)
                ->withChecks(IetfHealthCheckStatus::Fail, 1)
                ->create(),
        ];

        yield 'Fail with 1 fail check, 1 warn check' => [
            IetfHealthCheckStatus::Fail,
            IetfHealthCheckResponseFactory::new()
                ->withChecks(IetfHealthCheckStatus::Fail, 1)
                ->withChecks(IetfHealthCheckStatus::Warn, 1)
                ->create(),
        ];

        yield 'Fail with 1 pass check, 1 fail check, 1 warn check' => [
            IetfHealthCheckStatus::Fail,
            IetfHealthCheckResponseFactory::new()
                ->withChecks(IetfHealthCheckStatus::Pass, 1)
                ->withChecks(IetfHealthCheckStatus::Fail, 1)
                ->withChecks(IetfHealthCheckStatus::Warn, 1)
                ->create(),
        ];

        yield 'Fail with 1 pass check, 1 fail check, 1 warn check as last check' => [
            IetfHealthCheckStatus::Fail,
            IetfHealthCheckResponseFactory::new()
                ->withChecks(IetfHealthCheckStatus::Pass, 1)
                ->withChecks(IetfHealthCheckStatus::Warn, 1)
                ->withChecks(IetfHealthCheckStatus::Fail, 1)
                ->create(),
        ];

        yield 'Fail with 1 pass check, 1 fail check, 1 warn check as first check' => [
            IetfHealthCheckStatus::Fail,
            IetfHealthCheckResponseFactory::new()
                ->withChecks(IetfHealthCheckStatus::Fail, 1)
                ->withChecks(IetfHealthCheckStatus::Warn, 1)
                ->withChecks(IetfHealthCheckStatus::Pass, 1)
                ->create(),
        ];

        yield 'Warn with 1 warn check' => [
            IetfHealthCheckStatus::Warn,
            IetfHealthCheckResponseFactory::new()
                ->withChecks(IetfHealthCheckStatus::Warn, 1)
                ->create(),
        ];

        yield 'Warn with 1 pass check, 1 warn check' => [
            IetfHealthCheckStatus::Warn,
            IetfHealthCheckResponseFactory::new()
                ->withChecks(IetfHealthCheckStatus::Pass, 1)
                ->withChecks(IetfHealthCheckStatus::Warn, 1)
                ->create(),
        ];

        yield 'Warn with 1 pass check, 1 warn check as first check' => [
            IetfHealthCheckStatus::Warn,
            IetfHealthCheckResponseFactory::new()
                ->withChecks(IetfHealthCheckStatus::Warn, 1)
                ->withChecks(IetfHealthCheckStatus::Pass, 1)
                ->create(),
        ];
    }
}
