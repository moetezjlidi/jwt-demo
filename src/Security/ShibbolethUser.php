<?php

declare(strict_types=1);

namespace App\Security;

use Symfony\Component\Security\Core\User\UserInterface;

final class ShibbolethUser implements UserInterface
{
    private string $identifier;
    private array $roles;
    private ?string $email;
    private ?string $displayName;

    public function __construct(
        string $identifier,
        array $roles = ['ROLE_USER'],
        ?string $email = null,
        ?string $displayName = null
    ) {
        $this->identifier = $identifier;
        $this->roles = $roles;
        $this->email = $email;
        $this->displayName = $displayName;
    }

    /**
     * Returns the user identifier (username, email, ID, etc).
     * JWT will use this as the 'sub' claim.
     */
    public function getUserIdentifier(): string
    {
        return $this->identifier;
    }

    /**
     * Returns the roles assigned to this user.
     */
    public function getRoles(): array
    {
        return $this->roles;
    }

    /**
     * Required by UserInterface. Erases credentials from memory.
     * Since we're stateless, this is mostly for security hygiene.
     */
    public function eraseCredentials(): void
    {
        // No password stored in memory to erase
    }

    // Optional helper methods (not part of UserInterface)
    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function getDisplayName(): ?string
    {
        return $this->displayName;
    }
}