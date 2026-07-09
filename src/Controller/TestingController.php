<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class TestingController extends AbstractController
{
    #[Route('/loginPage', name: 'testing_interface')]
    public function testingInterface(): Response
    {
        return $this->render('testing/interface.html.twig');
    }
} 