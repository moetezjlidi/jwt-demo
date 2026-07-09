<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'training')]
class Training
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    #[ORM\Column]
    private string $title;

    #[ORM\Column]
    private string $organizationId;

    #[ORM\Column]
    private string $createdByEmail;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    public function __construct(string $title, string $organizationId, string $createdByEmail)
    {
        $this->title = $title;
        $this->organizationId = $organizationId;
        $this->createdByEmail = $createdByEmail;
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): int { return $this->id; }
    public function getTitle(): string { return $this->title; }
    public function getOrganizationId(): string { return $this->organizationId; }
    public function getCreatedByEmail(): string { return $this->createdByEmail; }
    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
}
