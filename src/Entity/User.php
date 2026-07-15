<?php
// src/Entity/User.php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity]
#[ORM\Table(name: 'app_user')]
class User implements UserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    public int $id;

    #[ORM\Column]
    public string $organization_id;

    #[ORM\Column]
    public string $username;

    #[ORM\Column]
    public string $email;

    #[ORM\Column]
    public string $password;

    #[ORM\Column(nullable: true)]
    public ?\DateTimeImmutable $last_login = null;

    #[ORM\Column(type: 'text', nullable: true)]
    public ?string $rolesData = null;  // Store as TEXT string, not typed array

    #[ORM\Column(type: 'text', nullable: true)]
    public ?string $access_rights = null;  // Store as TEXT string

    #[ORM\OneToOne(mappedBy: 'user', cascade: ['persist', 'remove'])]
    private ?ApiUser $apiUser = null;

    public function getId(): int { return $this->id; }
    public function getUserIdentifier(): string { return $this->username; }
    public function getPassword(): ?string { return $this->password; }
    public function getOrganizationId(): string
{
    return $this->organization_id;
}
    public function getRoles(): array 
    { 
        if (!$this->rolesData) {
            return ['ROLE_USER'];
        }
        $decoded = json_decode($this->rolesData, true);
        return is_array($decoded) ? $decoded : ['ROLE_USER'];
    }
    
    public function setRoles(array $roles): self 
    { 
        $this->rolesData = json_encode($roles);
        return $this;
    }
    
    public function eraseCredentials(): void {}
    
    public function getApiUser(): ?ApiUser { return $this->apiUser; }
    public function setApiUser(?ApiUser $apiUser): self { $this->apiUser = $apiUser; return $this; }
}