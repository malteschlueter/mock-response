<?php

declare(strict_types=1);

namespace App\Controller;

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
}
