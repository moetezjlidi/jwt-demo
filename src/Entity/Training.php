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

    #[ORM\Column(nullable: true)]
    private ?int $numero = null;

    #[ORM\Column]
    private string $title;

    #[ORM\Column(nullable: true)]
    private ?string $thematique = null;

    #[ORM\Column(nullable: true)]
    private ?int $nombreParticipants = null;

    #[ORM\Column(nullable: true)]
    private ?int $nombreInscriptions = null;

    #[ORM\Column(nullable: true)]
    private ?float $totalHeures = null;

    #[ORM\Column(nullable: true)]
    private ?float $totalJours = null;

    #[ORM\Column(nullable: true)]
    private ?string $superviseur = null;

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

    public function getNumero(): ?int { return $this->numero; }
    public function setNumero(?int $numero): self { $this->numero = $numero; return $this; }

    public function getThematique(): ?string { return $this->thematique; }
    public function setThematique(?string $thematique): self { $this->thematique = $thematique; return $this; }

    public function getNombreParticipants(): ?int { return $this->nombreParticipants; }
    public function setNombreParticipants(?int $n): self { $this->nombreParticipants = $n; return $this; }

    public function getNombreInscriptions(): ?int { return $this->nombreInscriptions; }
    public function setNombreInscriptions(?int $n): self { $this->nombreInscriptions = $n; return $this; }

    public function getTotalHeures(): ?float { return $this->totalHeures; }
    public function setTotalHeures(?float $h): self { $this->totalHeures = $h; return $this; }

    public function getTotalJours(): ?float { return $this->totalJours; }
    public function setTotalJours(?float $j): self { $this->totalJours = $j; return $this; }

    public function getSuperviseur(): ?string { return $this->superviseur; }
    public function setSuperviseur(?string $s): self { $this->superviseur = $s; return $this; }
}