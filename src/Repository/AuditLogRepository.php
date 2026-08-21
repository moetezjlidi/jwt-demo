<?php

namespace App\Repository;

use App\Entity\AuditLog;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class AuditLogRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AuditLog::class);
    }

    public function findByOrganization(string $organizationId, int $limit = 100): array
    {
        return $this->createQueryBuilder('a')
            ->where('a.organizationId = :orgId')
            ->setParameter('orgId', $organizationId)
            ->orderBy('a.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function findOneByIdAndOrganization(int $id, string $organizationId): ?AuditLog
    {
        return $this->findOneBy(['id' => $id, 'organizationId' => $organizationId]);
    }
}