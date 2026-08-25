<?php

declare(strict_types=1);

namespace App\Security;

use App\Entity\ApiKey;
use Symfony\Component\Security\Core\User\UserInterface;

final class ApiKeyUser implements UserInterface, OrganizationScopedUser
{
    public function __construct(
        private ApiKey $apiKey,
    ) {}

    public function getApiKey(): ApiKey
    {
        return $this->apiKey;
    }

    public function getOrganizationIds(): array
    {
        return $this->apiKey->getOrganizationIds();
    }

    public function hasPermission(string $permission): bool
    {
        return in_array($permission, $this->apiKey->getPermissions(), true);
    }

    public function getRoles(): array
    {
        return ['ROLE_API_KEY'];
    }

    public function getUserIdentifier(): string
    {
        return 'api-key:' . $this->apiKey->getId();
    }

    public function eraseCredentials(): void {}
}
