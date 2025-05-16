<?php

declare(strict_types=1);

namespace App\Controller;

use App\DataTransferObject\IetfHealthCheckCheck;
use App\DataTransferObject\IetfHealthCheckResponse;
use App\Enum\IetfHealthCheckStatus;
use App\Factory\IetfHealthCheckResponseFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Clock\ClockAwareTrait;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
final class IetfHealthCheckController extends AbstractController
{
    use ClockAwareTrait;

    #[Route('/ietf-health-check/{status}', name: 'mock_ietf_health_check_status_always')]
    public function always(IetfHealthCheckStatus $status): Response
    {
        return new JsonResponse(
            IetfHealthCheckResponseFactory::new()
                ->withStatus($status)
                ->create()
        );
    }

    #[Route('/ietf-health-check/{status}/random', name: 'mock_ietf_health_check_status_randomly')]
    public function randomly(IetfHealthCheckStatus $status): Response
    {
        if ((bool) random_int(0, 1)) {
            $status = IetfHealthCheckStatus::Pass;
        }

        return new JsonResponse(
            IetfHealthCheckResponseFactory::new()
                ->withStatus($status)
                ->create()
        );
    }

    #[Route('/ietf-health-check/{status}/interval/{interval}/{time}', name: 'mock_ietf_health_check_status_interval')]
    public function interval(IetfHealthCheckStatus $status, string $interval, int $time): Response
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
            $status = IetfHealthCheckStatus::Pass;
        }

        return new JsonResponse(
            IetfHealthCheckResponseFactory::new()
                ->withStatus($status)
                ->create()
        );
    }

    #[Route('/ietf-health-check/with-checks', name: 'mock_ietf_health_check_status_with_checks', priority: 1)]
    public function withChecks(Request $request): Response
    {
        $totalPassChecks = $request->query->getInt('total_pass_checks');
        $totalFailChecks = $request->query->getInt('total_fail_checks');
        $totalWarnChecks = $request->query->getInt('total_warn_checks');

        if ($totalPassChecks < 1 && $totalFailChecks < 1 && $totalWarnChecks < 1) {
            throw new \RuntimeException('At least one check is required');
        }

        return new JsonResponse(
            IetfHealthCheckResponseFactory::new()
                ->withOutput($totalPassChecks . ' pass checks, ' . $totalFailChecks . ' fail checks, ' . $totalWarnChecks . ' warn checks')
                ->withChecks(IetfHealthCheckStatus::Pass, $totalPassChecks)
                ->withChecks(IetfHealthCheckStatus::Fail, $totalFailChecks)
                ->withChecks(IetfHealthCheckStatus::Warn, $totalWarnChecks)
                ->create()
        );
    }

    /**
     * @TODO UUID should be a dynamic in the future and the checks configurable
     */
    #[Route('/ietf-health-check/custom-check/9b8feb48-3799-40ae-92f1-1ac56e9d3cb1', name: 'mock_ietf_health_check_status_custom_checks')]
    public function customChecks(): Response
    {
        $response = new IetfHealthCheckResponse(
            status: IetfHealthCheckStatus::Warn,
            output: 'Some individual output can be given here',
            checks: [
                'server.space.used' => new IetfHealthCheckCheck(
                    observedValue: (string) (random_int(50, 75) / 100),
                    observedUnit: 'percent',
                    status: IetfHealthCheckStatus::Pass,
                    output: 'Currently is 75% of the disk space used.',
                    customFields: [
                        'metricType' => 'time_series_percent',
                        'limitType' => 'max',
                        'limit' => '0.9',
                        'description' => 'Some description is visible.',
                    ]
                ),
                'server.ram.used' => new IetfHealthCheckCheck(
                    observedValue: (string) (random_int(80, 94) / 100),
                    observedUnit: 'percent',
                    status: IetfHealthCheckStatus::Warn,
                    output: 'The RAM usage reached nearly the limit. This is just a warning and should be checked.',
                    customFields: [
                        'metricType' => 'time_series_percent',
                        'limitType' => 'max',
                        'limit' => '0.95',
                    ]
                ),
                'server.updates.system' => new IetfHealthCheckCheck(
                    observedValue: random_int(0, 35),
                    observedUnit: 'updates',
                    status: IetfHealthCheckStatus::Pass,
                    output: 'Currently 35 updates are available.',
                    customFields: [
                        'metricType' => 'time_series_numeric',
                        'limitType' => 'max',
                        'limit' => 50,
                    ]
                ),
                'server.updates.system_critical' => new IetfHealthCheckCheck(
                    observedValue: random_int(1, 3),
                    observedUnit: 'updates',
                    status: IetfHealthCheckStatus::Fail,
                    output: 'Currently 3 critical updates are available.',
                    customFields: [
                        'metricType' => 'time_series_numeric',
                        'limitType' => 'max',
                        'limit' => 1,
                        'description' => 'This is a critical update and should be installed as soon as possible.',
                    ]
                ),
                'server.info' => new IetfHealthCheckCheck(
                    observedValue: 'test value',
                    observedUnit: 'without unit',
                    status: IetfHealthCheckStatus::Pass,
                    output: 'This is just a test without a graph.',
                    customFields: [
                        'description' => 'This is a additional text.',
                    ]
                ),
            ]
        );

        return new JsonResponse($response);
    }
}
