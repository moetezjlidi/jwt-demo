<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'session')]
class Session
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    #[ORM\ManyToOne(targetEntity: Training::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Training $training;

    #[ORM\Column]
    private string $name;

    #[ORM\Column]
    private string $organizationId;

    #[ORM\Column]
    private string $inscriptions;

    #[ORM\Column]
    private bool $afficherEnLigne;

    #[ORM\Column]
    private string $statut;

    #[ORM\Column]
    private \DateTimeImmutable $dateDebut;

    #[ORM\Column]
    private \DateTimeImmutable $dateFin;

    #[ORM\Column(nullable: true)]
    private ?int $participantsMax = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $dateLimite = null;

    #[ORM\Column(nullable: true)]
    private ?float $nombreHeures = null;

    #[ORM\Column(nullable: true)]
    private ?float $nombreJours = null;

    public function __construct(
        Training $training,
        string $name,
        \DateTimeImmutable $dateDebut,
        \DateTimeImmutable $dateFin,
        string $inscriptions = 'Publiques',
        bool $afficherEnLigne = true,
        string $statut = 'Ouverte'
    ) {
        $this->training = $training;
        $this->organizationId = $training->getOrganizationId();
        $this->name = $name;
        $this->dateDebut = $dateDebut;
        $this->dateFin = $dateFin;
        $this->inscriptions = $inscriptions;
        $this->afficherEnLigne = $afficherEnLigne;
        $this->statut = $statut;
    }

    public function getId(): int { return $this->id; }
    public function getTraining(): Training { return $this->training; }
    public function getOrganizationId(): string { return $this->organizationId; }
    public function getName(): string { return $this->name; }
    public function getInscriptions(): string { return $this->inscriptions; }
    public function isAfficherEnLigne(): bool { return $this->afficherEnLigne; }
    public function getStatut(): string { return $this->statut; }
    public function getDateDebut(): \DateTimeImmutable { return $this->dateDebut; }
    public function getDateFin(): \DateTimeImmutable { return $this->dateFin; }

    public function getParticipantsMax(): ?int { return $this->participantsMax; }
    public function setParticipantsMax(?int $n): self { $this->participantsMax = $n; return $this; }

    public function getDateLimite(): ?\DateTimeImmutable { return $this->dateLimite; }
    public function setDateLimite(?\DateTimeImmutable $d): self { $this->dateLimite = $d; return $this; }

    public function getNombreHeures(): ?float { return $this->nombreHeures; }
    public function setNombreHeures(?float $h): self { $this->nombreHeures = $h; return $this; }

    public function getNombreJours(): ?float { return $this->nombreJours; }
    public function setNombreJours(?float $j): self { $this->nombreJours = $j; return $this; }
}