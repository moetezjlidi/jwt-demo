<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'trainee')]
class Trainee
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
    private ?string $institution = null;

    #[ORM\Column(nullable: true)]
    private ?string $birthDate = null;

    #[ORM\Column(nullable: true)]
    private ?string $amuStatut = null;

    #[ORM\Column(nullable: true)]
    private ?string $bap = null;

    #[ORM\Column(nullable: true)]
    private ?string $corps = null;

    #[ORM\Column(nullable: true)]
    private ?string $category = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $campus = null;

    #[ORM\Column(nullable: true)]
    private ?string $firstNameSup = null;

    #[ORM\Column(nullable: true)]
    private ?string $lastNameSup = null;

    #[ORM\Column(nullable: true)]
    private ?string $emailSup = null;

    #[ORM\Column(nullable: true)]
    private ?string $firstNameCorr = null;

    #[ORM\Column(nullable: true)]
    private ?string $lastNameCorr = null;

    #[ORM\Column(nullable: true)]
    private ?string $emailCorr = null;

    #[ORM\Column(nullable: true)]
    private ?string $fonction = null;

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

    public function getInstitution(): ?string { return $this->institution; }
    public function setInstitution(?string $institution): self { $this->institution = $institution; return $this; }

    public function getBirthDate(): ?string { return $this->birthDate; }
    public function setBirthDate(?string $birthDate): self { $this->birthDate = $birthDate; return $this; }

    public function getAmuStatut(): ?string { return $this->amuStatut; }
    public function setAmuStatut(?string $amuStatut): self { $this->amuStatut = $amuStatut; return $this; }

    public function getBap(): ?string { return $this->bap; }
    public function setBap(?string $bap): self { $this->bap = $bap; return $this; }

    public function getCorps(): ?string { return $this->corps; }
    public function setCorps(?string $corps): self { $this->corps = $corps; return $this; }

    public function getCategory(): ?string { return $this->category; }
    public function setCategory(?string $category): self { $this->category = $category; return $this; }

    public function getCampus(): ?string { return $this->campus; }
    public function setCampus(?string $campus): self { $this->campus = $campus; return $this; }

    public function getFirstNameSup(): ?string { return $this->firstNameSup; }
    public function setFirstNameSup(?string $firstNameSup): self { $this->firstNameSup = $firstNameSup; return $this; }

    public function getLastNameSup(): ?string { return $this->lastNameSup; }
    public function setLastNameSup(?string $lastNameSup): self { $this->lastNameSup = $lastNameSup; return $this; }

    public function getEmailSup(): ?string { return $this->emailSup; }
    public function setEmailSup(?string $emailSup): self { $this->emailSup = $emailSup; return $this; }

    public function getFirstNameCorr(): ?string { return $this->firstNameCorr; }
    public function setFirstNameCorr(?string $firstNameCorr): self { $this->firstNameCorr = $firstNameCorr; return $this; }

    public function getLastNameCorr(): ?string { return $this->lastNameCorr; }
    public function setLastNameCorr(?string $lastNameCorr): self { $this->lastNameCorr = $lastNameCorr; return $this; }

    public function getEmailCorr(): ?string { return $this->emailCorr; }
    public function setEmailCorr(?string $emailCorr): self { $this->emailCorr = $emailCorr; return $this; }

    public function getFonction(): ?string { return $this->fonction; }
    public function setFonction(?string $fonction): self { $this->fonction = $fonction; return $this; }
}
