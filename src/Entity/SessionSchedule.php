<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'session_schedule')]
class SessionSchedule
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    #[ORM\ManyToOne(targetEntity: Session::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Session $session;

    #[ORM\Column]
    private \DateTimeImmutable $dateDebut;

    #[ORM\Column]
    private \DateTimeImmutable $dateFin;

    #[ORM\Column(nullable: true)]
    private ?string $horairesMatin = null;

    #[ORM\Column(nullable: true)]
    private ?float $nombreHeuresMatin = null;

    #[ORM\Column(nullable: true)]
    private ?string $horairesApresMidi = null;

    #[ORM\Column(nullable: true)]
    private ?float $nombreHeuresApresMidi = null;

    #[ORM\Column(nullable: true)]
    private ?string $lieu = null;

    public function __construct(
        Session $session,
        \DateTimeImmutable $dateDebut,
        \DateTimeImmutable $dateFin
    ) {
        $this->session = $session;
        $this->dateDebut = $dateDebut;
        $this->dateFin = $dateFin;
    }

    public function getId(): int { return $this->id; }
    public function getSession(): Session { return $this->session; }
    public function getDateDebut(): \DateTimeImmutable { return $this->dateDebut; }
    public function getDateFin(): \DateTimeImmutable { return $this->dateFin; }

    public function getHorairesMatin(): ?string { return $this->horairesMatin; }
    public function setHorairesMatin(?string $h): self { $this->horairesMatin = $h; return $this; }

    public function getNombreHeuresMatin(): ?float { return $this->nombreHeuresMatin; }
    public function setNombreHeuresMatin(?float $h): self { $this->nombreHeuresMatin = $h; return $this; }

    public function getHorairesApresMidi(): ?string { return $this->horairesApresMidi; }
    public function setHorairesApresMidi(?string $h): self { $this->horairesApresMidi = $h; return $this; }

    public function getNombreHeuresApresMidi(): ?float { return $this->nombreHeuresApresMidi; }
    public function setNombreHeuresApresMidi(?float $h): self { $this->nombreHeuresApresMidi = $h; return $this; }

    public function getLieu(): ?string { return $this->lieu; }
    public function setLieu(?string $l): self { $this->lieu = $l; return $this; }
}