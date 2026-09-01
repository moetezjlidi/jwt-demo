<?php

namespace App\Repository;

use App\Entity\Trainee;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Trainee>
 */
class TraineeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Trainee::class);
    }

    public function findByOrganization(string $organizationId): array
    {
        return $this->findBy(['organizationId' => $organizationId], ['createdAt' => 'DESC']);
    }

    public function findOneByIdAndOrganization(int $id, string $organizationId): ?Trainee
    {
        return $this->findOneBy(['id' => $id, 'organizationId' => $organizationId]);
    }

    /** @param string[] $organizationIds */
    public function findByOrganizations(array $organizationIds): array
    {
        return $this->createQueryBuilder('t')
            ->where('t.organizationId IN (:ids)')
            ->setParameter('ids', $organizationIds)
            ->orderBy('t.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /** @param string[] $organizationIds */
    public function findOneByIdAndOrganizations(int $id, array $organizationIds): ?Trainee
    {
        return $this->createQueryBuilder('t')
            ->where('t.id = :id')
            ->andWhere('t.organizationId IN (:ids)')
            ->setParameter('id', $id)
            ->setParameter('ids', $organizationIds)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
