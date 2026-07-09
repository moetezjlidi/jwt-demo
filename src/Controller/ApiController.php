<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Core\User\UserInterface;

final class ApiController extends AbstractController
{
    /**
     * Public endpoint (no authentication required).
     */
    #[Route('/api/public', name: 'api_public', methods: ['GET'])]
    public function public(): JsonResponse
    {
        return $this->json([
            'message' => 'This is a public endpoint. No JWT required.',
        ]);
    }

    /**
     * Protected endpoint (JWT required).
     * 
     * @param UserInterface $user Injected by #[CurrentUser] attribute
     *                             Will be null if not authenticated
     */
    #[Route('/api/private', name: 'api_private', methods: ['GET'])]
    public function private(#[CurrentUser] ?UserInterface $user): JsonResponse
    {
        // The access_control rule ensures $user is never null here
        // But we type it as nullable for clarity
        
        return $this->json([
            'message' => 'This is a protected endpoint. JWT required.',
            'authenticated_user' => $user?->getUserIdentifier(),
            'roles' => $user?->getRoles(),
        ]);
    }

    /**
     * Another protected endpoint.
     */
    #[Route('/api/user-info', name: 'api_user_info', methods: ['GET'])]
    public function userInfo(#[CurrentUser] ?UserInterface $user): JsonResponse
    {
        return $this->json([
            'username' => $user?->getUserIdentifier(),
            'roles' => $user?->getRoles(),
        ]);
    }
}