<?php

declare(strict_types=1);

namespace App\Controller;

use App\Enum\IetfHealthCheckStatus;
use App\Enum\JavaScriptMockType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
final class HomeController extends AbstractController
{
    #[Route('/')]
    public function __invoke(): Response
    {
        return $this->render('home.html.twig', [
            'status_codes' => Response::$statusTexts,
            'javascript_mock_types' => JavaScriptMockType::cases(),
            'ietf_health_check_status' => IetfHealthCheckStatus::cases(),
            'ietf_health_check_status_with_checks' => [
                [
                    'total_pass_checks' => 3,
                ],
                [
                    'total_fail_checks' => 3,
                ],
                [
                    'total_warn_checks' => 3,
                ],
                [
                    'total_pass_checks' => 2,
                    'total_fail_checks' => 1,
                ],
                [
                    'total_pass_checks' => 2,
                    'total_warn_checks' => 1,
                ],
                [
                    'total_fail_checks' => 1,
                    'total_warn_checks' => 2,
                ],
                [
                    'total_pass_checks' => 3,
                    'total_fail_checks' => 1,
                    'total_warn_checks' => 2,
                ],
            ],
        ]);
    }
}
