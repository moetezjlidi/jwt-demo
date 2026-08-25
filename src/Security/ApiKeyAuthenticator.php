<?php

declare(strict_types=1);

namespace App\Security;

use App\Repository\ApiKeyRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

/**
 * Authenticates machine-to-machine requests via the "X-Api-Key" header.
 * API keys are read-only: any non-GET/HEAD request is rejected outright.
 */
final class ApiKeyAuthenticator extends AbstractAuthenticator
{
    public function __construct(
        private ApiKeyRepository $apiKeyRepo,
        private EntityManagerInterface $em,
    ) {}

    public function supports(Request $request): ?bool
    {
        return $request->headers->has('X-Api-Key');
    }

    public function authenticate(Request $request): Passport
    {
        $rawKey = (string) $request->headers->get('X-Api-Key');

        if ($rawKey === '') {
            throw new CustomUserMessageAuthenticationException('Missing API key.');
        }

        if (!in_array($request->getMethod(), ['GET', 'HEAD'], true)) {
            throw new CustomUserMessageAuthenticationException('API keys are read-only.');
        }

        $keyHash = hash('sha256', $rawKey);

        return new SelfValidatingPassport(
            new UserBadge($keyHash, function (string $hash): ApiKeyUser {
                $apiKey = $this->apiKeyRepo->findOneActiveByHash($hash);
                if (!$apiKey) {
                    throw new CustomUserMessageAuthenticationException('Invalid or revoked API key.');
                }

                $apiKey->markUsed();
                $this->em->flush();

                return new ApiKeyUser($apiKey);
            })
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        return new JsonResponse(['error' => $exception->getMessage()], 401);
    }
}
