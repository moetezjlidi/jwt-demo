<?php
// src/Entity/ApiUser.php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

#[ORM\Entity]
#[ORM\Table(name: 'api_user')]
#[ORM\UniqueConstraint(columns: ['email'])]
class ApiUser implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    #[ORM\Column(unique: true)]
    private string $email;

    #[ORM\Column]
    private string $passwordHash; // argon2id

    /**
     * Lien vers User existant
     * ApiUser.user.organization_id = tenant boundary
     */
    #[ORM\OneToOne(inversedBy: 'apiUser', cascade: ['persist'])]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private User $user;

    #[ORM\Column(type: 'json')]
    private array $roles = ['ROLE_API_RH'];

    #[ORM\Column(length: 20)]
    private string $status = 'active'; // pending|active|suspended|locked

    #[ORM\Column]
    private int $failedLoginAttempts = 0;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $lockedUntil = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $lastLoginAt = null;

    #[ORM\Column(nullable: true)]
    private ?string $mfaSecret = null; // TOTP (base32)

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    // Accessors
    public function getId(): int { return $this->id; }
    public function getEmail(): string { return $this->email; }
    public function setEmail(string $email): self { $this->email = $email; return $this; }

    public function getPasswordHash(): string { return $this->passwordHash; }
    public function setPasswordHash(string $hash): self { $this->passwordHash = $hash; return $this; }

    /**
     * Getter: organization_id via relation User
     */
    public function getOrganizationId(): string
    {
        return $this->user->getOrganizationId();
    }

    public function getUser(): User { return $this->user; }
    public function setUser(User $user): self { $this->user = $user; return $this; }

    public function getRoles(): array { return $this->roles; }
    public function setRoles(array $roles): self { $this->roles = $roles; return $this; }

    public function getStatus(): string { return $this->status; }
    public function setStatus(string $status): self { $this->status = $status; return $this; }

    public function getMfaSecret(): ?string { return $this->mfaSecret; }
    public function setMfaSecret(?string $secret): self { $this->mfaSecret = $secret; return $this; }

    public function getLastLoginAt(): ?\DateTimeImmutable { return $this->lastLoginAt; }

    // UserInterface
    public function getUserIdentifier(): string { return $this->email; }
    public function getPassword(): ?string 
    { 
        return $this->passwordHash; 
    }
    public function eraseCredentials(): void {}

    // Account lock
    public function isLocked(): bool
    {
        return $this->lockedUntil && $this->lockedUntil > new \DateTimeImmutable();
    }

    public function incrementFailedLogins(): self
    {
        $this->failedLoginAttempts++;
        if ($this->failedLoginAttempts >= 5) {
            $this->lockedUntil = new \DateTimeImmutable('+15 minutes');
        }
        return $this;
    }

    public function resetFailedLogins(): self
    {
        $this->failedLoginAttempts = 0;
        $this->lockedUntil = null;
        $this->lastLoginAt = new \DateTimeImmutable();
        return $this;
    }
}