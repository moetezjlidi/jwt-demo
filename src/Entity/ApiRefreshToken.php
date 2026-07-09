<?php
// src/Entity/ApiRefreshToken.php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'api_refresh_token')]
class ApiRefreshToken
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    #[ORM\Column]
    private string $tokenHash; // hash du token (jamais en clair)

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ApiUser $apiUser;

    #[ORM\Column]
    private \DateTimeImmutable $expiresAt;

    #[ORM\Column]
    private bool $revoked = false;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $usedAt = null;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    public function __construct(ApiUser $apiUser, string $tokenHash, int $expirationDays = 60)
    {
        $this->apiUser = $apiUser;
        $this->tokenHash = $tokenHash;
        $this->expiresAt = new \DateTimeImmutable("+{$expirationDays} days");
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): int { return $this->id; }
    public function getApiUser(): ApiUser { return $this->apiUser; }
    public function getTokenHash(): string { return $this->tokenHash; }
    public function getExpiresAt(): \DateTimeImmutable { return $this->expiresAt; }

    public function isValid(): bool
    {
        return !$this->revoked && $this->expiresAt > new \DateTimeImmutable();
    }

    public function markAsUsed(): self { $this->usedAt = new \DateTimeImmutable(); return $this; }
    public function revoke(): self { $this->revoked = true; return $this; }

    public function isReused(): bool { return $this->usedAt !== null; }
}