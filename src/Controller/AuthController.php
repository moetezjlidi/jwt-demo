<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\ApiRefreshToken;
use App\Entity\ApiUser;
use App\Repository\ApiRefreshTokenRepository;
use App\Repository\ApiUserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

final class AuthController extends AbstractController
{
    private const ACCESS_TOKEN_TTL = 3600;
    private const REFRESH_TOKEN_DAYS = 60;

    public function __construct(
        private ApiUserRepository $apiUserRepo,
        private ApiRefreshTokenRepository $refreshTokenRepo,
        private EntityManagerInterface $em,
        private UserPasswordHasherInterface $passwordHasher,
        private JWTTokenManagerInterface $jwtManager,
    ) {
    }

    /**
     * Legacy endpoint, kept for backward compatibility with json_login firewall.
     */
    #[Route('/api/login_check', name: 'api_login_check', methods: ['POST'])]
    public function loginCheck(): JsonResponse
    {
        return $this->json(['error' => 'Invalid credentials'], 401);
    }

    #[Route('/api/auth/login', name: 'api_auth_login', methods: ['POST'])]
    public function login(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true) ?? [];
        $email = $data['email'] ?? null;
        $password = $data['password'] ?? null;

        if (!$email || !$password) {
            return $this->json(['error' => 'email and password are required'], 400);
        }

        $apiUser = $this->apiUserRepo->findOneByEmail($email);
        if (!$apiUser) {
            return $this->json(['error' => 'Invalid credentials'], 401);
        }

        if ($apiUser->isLocked()) {
            return $this->json(['error' => 'Account locked, try again later'], 403);
        }

        if ($apiUser->getStatus() !== 'active') {
            return $this->json(['error' => 'Account is not active'], 403);
        }

        if (!$this->passwordHasher->isPasswordValid($apiUser, $password)) {
            $apiUser->incrementFailedLogins();
            $this->em->flush();

            return $this->json(['error' => 'Invalid credentials'], 401);
        }

        $apiUser->resetFailedLogins();
        $this->em->flush();

        return $this->json($this->buildAuthResponse($apiUser));
    }

    #[Route('/api/auth/me', name: 'api_auth_me', methods: ['GET'])]
    public function me(#[CurrentUser] ?ApiUser $user): JsonResponse
    {
        if (!$user) {
            return $this->json(['error' => 'Not authenticated'], 401);
        }

        return $this->json([
            'email' => $user->getEmail(),
            'organization_id' => $user->getOrganizationId(),
            'roles' => $user->getRoles(),
            'status' => $user->getStatus(),
            'last_login_at' => $user->getLastLoginAt()?->format(DATE_ATOM),
        ]);
    }

    #[Route('/api/auth/refresh', name: 'api_auth_refresh', methods: ['POST'])]
    public function refresh(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true) ?? [];
        $rawToken = $data['refresh_token'] ?? null;

        if (!$rawToken) {
            return $this->json(['error' => 'refresh_token is required'], 400);
        }

        $tokenHash = hash('sha256', $rawToken);
        $refreshToken = $this->refreshTokenRepo->findValidByTokenHash($tokenHash);

        if (!$refreshToken) {
            return $this->json(['error' => 'Invalid or expired refresh token'], 403);
        }

        if ($refreshToken->isReused()) {
            // Token reuse detected: revoke the whole family, force a fresh login.
            $this->refreshTokenRepo->revokeAllForApiUser($refreshToken->getApiUser()->getId());
            $this->em->flush();

            return $this->json(['error' => 'Refresh token reuse detected, all sessions revoked'], 403);
        }

        $refreshToken->markAsUsed();
        $this->em->flush();

        return $this->json($this->buildAuthResponse($refreshToken->getApiUser()));
    }

    private function buildAuthResponse(ApiUser $apiUser): array
    {
        $accessToken = $this->jwtManager->create($apiUser);

        $rawRefreshToken = bin2hex(random_bytes(32));
        $refreshTokenEntity = new ApiRefreshToken(
            $apiUser,
            hash('sha256', $rawRefreshToken),
            self::REFRESH_TOKEN_DAYS,
        );
        $this->em->persist($refreshTokenEntity);
        $this->em->flush();

        return [
            'access_token' => $accessToken,
            'refresh_token' => $rawRefreshToken,
            'expires_in' => self::ACCESS_TOKEN_TTL,
            'user' => [
                'email' => $apiUser->getEmail(),
                'organization_id' => $apiUser->getOrganizationId(),
                'roles' => $apiUser->getRoles(),
            ],
        ];
    }
}
