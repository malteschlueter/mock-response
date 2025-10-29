<?php

declare(strict_types=1);

namespace App\Controller;

use App\Enum\JavaScriptMockType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
final class JavaScriptController extends AbstractController
{
    #[Route('/javascript/{type}', name: 'mock_javascript_always')]
    public function always(JavaScriptMockType $type): Response
    {
        return $this->render('javascript/' . $type->value . '.html.twig');
    }
}
