<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'api_key')]
class ApiKey
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    #[ORM\Column]
    private string $organizationId;

    #[ORM\Column]
    private string $name;

    #[ORM\Column]
    private string $keyHash;

    #[ORM\Column]
    private string $keyPrefix; // 8 premiers caractères visibles, pour identifier la clé sans exposer le secret

    #[ORM\Column(type: 'json')]
    private array $permissions = [];

    #[ORM\Column(length: 20)]
    private string $status = 'active'; // active|revoked

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $lastUsedAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $revokedAt = null;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    public function __construct(string $organizationId, string $name, string $keyHash, string $keyPrefix, array $permissions = [])
    {
        $this->organizationId = $organizationId;
        $this->name = $name;
        $this->keyHash = $keyHash;
        $this->keyPrefix = $keyPrefix;
        $this->permissions = $permissions;
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): int { return $this->id; }
    public function getOrganizationId(): string { return $this->organizationId; }
    public function getName(): string { return $this->name; }
    public function setName(string $name): self { $this->name = $name; return $this; }

    public function getKeyHash(): string { return $this->keyHash; }
    public function setKeyHash(string $hash): self { $this->keyHash = $hash; return $this; }

    public function getKeyPrefix(): string { return $this->keyPrefix; }
    public function setKeyPrefix(string $prefix): self { $this->keyPrefix = $prefix; return $this; }

    public function getPermissions(): array { return $this->permissions; }
    public function setPermissions(array $permissions): self { $this->permissions = $permissions; return $this; }

    public function getStatus(): string { return $this->status; }
    public function revoke(): self { $this->status = 'revoked'; $this->revokedAt = new \DateTimeImmutable(); return $this; }
    public function isActive(): bool { return $this->status === 'active'; }

    public function getLastUsedAt(): ?\DateTimeImmutable { return $this->lastUsedAt; }
    public function markUsed(): self { $this->lastUsedAt = new \DateTimeImmutable(); return $this; }

    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
    public function getRevokedAt(): ?\DateTimeImmutable { return $this->revokedAt; }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'organization_id' => $this->organizationId,
            'name' => $this->name,
            'key_prefix' => $this->keyPrefix . '...',
            'permissions' => $this->permissions,
            'status' => $this->status,
            'last_used_at' => $this->lastUsedAt?->format(DATE_ATOM),
            'created_at' => $this->createdAt->format(DATE_ATOM),
            'revoked_at' => $this->revokedAt?->format(DATE_ATOM),
        ];
    }
}