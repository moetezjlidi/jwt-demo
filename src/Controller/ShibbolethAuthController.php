<?php

declare(strict_types=1);

namespace App\Controller;

use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\User\UserInterface;

final class ShibbolethAuthController extends AbstractController
{
    /**
     * Shibboleth JWT issuance endpoint.
     * 
     * Request flow:
     * 1. Browser visits /mock-shibboleth (simulates Shibboleth SSO)
     * 2. /mock-shibboleth sets REMOTE_USER and redirects to this route
     * 3. ShibbolethAuthenticator verifies REMOTE_USER
     * 4. This controller issues a JWT
     * 
     * @param UserInterface $user Injected by Symfony (loaded by ShibbolethAuthenticator)
     * @param JWTTokenManagerInterface $jwtManager Injected by the container
     */
    #[Route('/auth/shibboleth', name: 'auth_shibboleth', methods: ['GET'])]
    public function shibbolethAuth(
        UserInterface $user,
        JWTTokenManagerInterface $jwtManager
    ): JsonResponse {
        // Create a JWT for the authenticated user
        $token = $jwtManager->create($user);

        return $this->json(['token' => $token]);
    }
}