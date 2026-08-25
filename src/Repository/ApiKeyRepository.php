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

    /**
     * Keys that include the given organization among their organizationIds.
     * organizationIds is a JSON column, so we match on its serialized text.
     */
    public function findByOrganization(string $organizationId): array
    {
        return $this->createQueryBuilder('k')
            ->where('k.organizationIds LIKE :needle')
            ->setParameter('needle', '%"' . $organizationId . '"%')
            ->orderBy('k.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findOneByIdAndOrganization(int $id, string $organizationId): ?ApiKey
    {
        return $this->createQueryBuilder('k')
            ->where('k.id = :id')
            ->andWhere('k.organizationIds LIKE :needle')
            ->setParameter('id', $id)
            ->setParameter('needle', '%"' . $organizationId . '"%')
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findAllOrdered(): array
    {
        return $this->findBy([], ['createdAt' => 'DESC']);
    }

    public function findOneActiveByHash(string $keyHash): ?ApiKey
    {
        return $this->findOneBy(['keyHash' => $keyHash, 'status' => 'active']);
    }
}
