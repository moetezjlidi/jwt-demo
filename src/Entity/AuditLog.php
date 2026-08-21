<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'audit_log')]
class AuditLog
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    #[ORM\Column]
    private string $organizationId;

    #[ORM\Column]
    private string $actorEmail; // qui a fait l'action

    #[ORM\Column]
    private string $action; // ex: 'api_key.created', 'api_key.revoked', 'api_key.rotated'

    #[ORM\Column(nullable: true)]
    private ?string $targetType = null; // ex: 'ApiKey'

    #[ORM\Column(nullable: true)]
    private ?int $targetId = null;

    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $metadata = null;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    public function __construct(string $organizationId, string $actorEmail, string $action, ?string $targetType = null, ?int $targetId = null, ?array $metadata = null)
    {
        $this->organizationId = $organizationId;
        $this->actorEmail = $actorEmail;
        $this->action = $action;
        $this->targetType = $targetType;
        $this->targetId = $targetId;
        $this->metadata = $metadata;
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): int { return $this->id; }
    public function getOrganizationId(): string { return $this->organizationId; }
    public function getActorEmail(): string { return $this->actorEmail; }
    public function getAction(): string { return $this->action; }
    public function getTargetType(): ?string { return $this->targetType; }
    public function getTargetId(): ?int { return $this->targetId; }
    public function getMetadata(): ?array { return $this->metadata; }
    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'organization_id' => $this->organizationId,
            'actor_email' => $this->actorEmail,
            'action' => $this->action,
            'target_type' => $this->targetType,
            'target_id' => $this->targetId,
            'metadata' => $this->metadata,
            'created_at' => $this->createdAt->format(DATE_ATOM),
        ];
    }
}