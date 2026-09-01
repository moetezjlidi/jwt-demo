<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'inscription')]
#[ORM\UniqueConstraint(name: 'traineesession_idx', columns: ['trainee_id', 'session_id'])]
class Inscription
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    #[ORM\ManyToOne(targetEntity: Trainee::class)]
    #[ORM\JoinColumn(name: 'trainee_id', nullable: false, onDelete: 'CASCADE')]
    private Trainee $trainee;

    #[ORM\ManyToOne(targetEntity: Session::class)]
    #[ORM\JoinColumn(name: 'session_id', nullable: false, onDelete: 'CASCADE')]
    private Session $session;

    #[ORM\Column]
    private string $organizationId;

    #[ORM\Column]
    private string $inscriptionStatus = 'En attente';

    #[ORM\Column(nullable: true)]
    private ?string $presenceStatus = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $motivation = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $message = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $refuse = null;

    #[ORM\Column]
    private bool $dif = false;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    public function __construct(Trainee $trainee, Session $session)
    {
        $this->trainee = $trainee;
        $this->session = $session;
        $this->organizationId = $trainee->getOrganizationId();
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): int { return $this->id; }
    public function getTrainee(): Trainee { return $this->trainee; }
    public function getSession(): Session { return $this->session; }
    public function getOrganizationId(): string { return $this->organizationId; }
    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }

    public function getInscriptionStatus(): string { return $this->inscriptionStatus; }
    public function setInscriptionStatus(string $inscriptionStatus): self { $this->inscriptionStatus = $inscriptionStatus; return $this; }

    public function getPresenceStatus(): ?string { return $this->presenceStatus; }
    public function setPresenceStatus(?string $presenceStatus): self { $this->presenceStatus = $presenceStatus; return $this; }

    public function getMotivation(): ?string { return $this->motivation; }
    public function setMotivation(?string $motivation): self { $this->motivation = $motivation; return $this; }

    public function getMessage(): ?string { return $this->message; }
    public function setMessage(?string $message): self { $this->message = $message; return $this; }

    public function getRefuse(): ?string { return $this->refuse; }
    public function setRefuse(?string $refuse): self { $this->refuse = $refuse; return $this; }

    public function isDif(): bool { return $this->dif; }
    public function setDif(bool $dif): self { $this->dif = $dif; return $this; }
}
