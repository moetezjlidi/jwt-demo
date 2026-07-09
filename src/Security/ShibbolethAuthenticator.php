<?php

declare(strict_types=1);

namespace App\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

final class ShibbolethAuthenticator extends AbstractAuthenticator
{
    public function __construct(
        private ShibbolethUserProvider $userProvider
    ) {}

    /**
     * Does this request have a Shibboleth REMOTE_USER header?
     * Return true to try authentication, false to skip this authenticator.
     */
    public function supports(Request $request): ?bool
    {
        // Only authenticate if REMOTE_USER is present
        return $request->server->has('REMOTE_USER');
    }

    /**
     * Authenticate the request by reading REMOTE_USER and loading the user.
     */
    public function authenticate(Request $request): Passport
    {
        $remoteUser = $request->server->get('REMOTE_USER');

        if (!$remoteUser) {
            throw new CustomUserMessageAuthenticationException('No Shibboleth session found.');
        }

        // Load the user via the provider
        // UserBadge will call $userProvider->loadUserByIdentifier($remoteUser)
        return new SelfValidatingPassport(
            new UserBadge($remoteUser, fn(string $id) => $this->userProvider->loadUserByIdentifier($id))
        );
    }

    /**
     * Called on successful authentication.
     * Return null to let the controller handle the response.
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return null; // Let the controller handle JWT issuance
    }

    /**
     * Called on authentication failure.
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        throw $exception;
    }
}