<?php

namespace App\Repository;

use App\Entity\Inscription;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Inscription>
 */
class InscriptionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Inscription::class);
    }

    /** @param string[] $organizationIds */
    public function findByOrganizations(array $organizationIds): array
    {
        return $this->createQueryBuilder('i')
            ->where('i.organizationId IN (:ids)')
            ->setParameter('ids', $organizationIds)
            ->orderBy('i.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /** @param string[] $organizationIds */
    public function findOneByIdAndOrganizations(int $id, array $organizationIds): ?Inscription
    {
        return $this->createQueryBuilder('i')
            ->where('i.id = :id')
            ->andWhere('i.organizationId IN (:ids)')
            ->setParameter('id', $id)
            ->setParameter('ids', $organizationIds)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
