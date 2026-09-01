<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'trainer')]
class Trainer
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    #[ORM\Column]
    private string $firstName;

    #[ORM\Column]
    private string $lastName;

    #[ORM\Column]
    private string $email;

    #[ORM\Column]
    private string $organizationId;

    #[ORM\Column(nullable: true)]
    private ?bool $isArchived = false;

    #[ORM\Column(nullable: true)]
    private ?bool $isAllowSendEmail = false;

    #[ORM\Column(nullable: true)]
    private ?bool $isOrganization = false;

    #[ORM\Column]
    private bool $isPublic = false;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $comments = null;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    public function __construct(string $firstName, string $lastName, string $email, string $organizationId)
    {
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->email = $email;
        $this->organizationId = $organizationId;
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): int { return $this->id; }
    public function getFirstName(): string { return $this->firstName; }
    public function getLastName(): string { return $this->lastName; }
    public function getEmail(): string { return $this->email; }
    public function getOrganizationId(): string { return $this->organizationId; }
    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }

    public function isArchived(): ?bool { return $this->isArchived; }
    public function setIsArchived(?bool $isArchived): self { $this->isArchived = $isArchived; return $this; }

    public function isAllowSendEmail(): ?bool { return $this->isAllowSendEmail; }
    public function setIsAllowSendEmail(?bool $isAllowSendEmail): self { $this->isAllowSendEmail = $isAllowSendEmail; return $this; }

    public function isOrganization(): ?bool { return $this->isOrganization; }
    public function setIsOrganization(?bool $isOrganization): self { $this->isOrganization = $isOrganization; return $this; }

    public function isPublic(): bool { return $this->isPublic; }
    public function setIsPublic(bool $isPublic): self { $this->isPublic = $isPublic; return $this; }

    public function getComments(): ?string { return $this->comments; }
    public function setComments(?string $comments): self { $this->comments = $comments; return $this; }
}
