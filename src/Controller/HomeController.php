<?php

declare(strict_types=1);

namespace App\Controller;

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
        return $this->render('home.html.twig', ['status_codes' => Response::$statusTexts]);
    }
}
