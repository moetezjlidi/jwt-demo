<?php

declare(strict_types=1);

namespace App\Security;

use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

final class ShibbolethUserProvider implements UserProviderInterface
{
    /**
     * Load a user by their identifier (from REMOTE_USER).
     * 
     * In a real system, you'd query LDAP here based on the identifier.
     * For now, we're creating users on-the-fly with basic roles.
     */
    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        // Mock: in production, lookup in LDAP
        // Example:
        //   $ldapUser = $this->ldap->findUser($identifier);
        //   if (!$ldapUser) throw new UserNotFoundException();
        //   return new ShibbolethUser($identifier, $this->mapRoles($ldapUser));
        
        return new ShibbolethUser(
            $identifier,
            ['ROLE_USER'],
            "$identifier@example.com",
            "User $identifier"
        );
    }

    /**
     * Refresh a user from the provider.
     * Since we're stateless, just return the user as-is.
     */
    public function refreshUser(UserInterface $user): UserInterface
    {
        return $user;
    }

    /**
     * Whether this provider supports the given user class.
     */
    public function supportsClass(string $class): bool
    {
        return $class === ShibbolethUser::class;
    }
}