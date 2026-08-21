<?php

namespace App\Repository;

use App\Entity\ApiKey;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ApiKeyRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ApiKey::class);
    }

    public function findByOrganization(string $organizationId): array
    {
        return $this->findBy(['organizationId' => $organizationId], ['createdAt' => 'DESC']);
    }

    public function findOneByIdAndOrganization(int $id, string $organizationId): ?ApiKey
    {
        return $this->findOneBy(['id' => $id, 'organizationId' => $organizationId]);
    }
}