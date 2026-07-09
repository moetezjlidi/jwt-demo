<?php

namespace App\Repository;

use App\Entity\ApiUser;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ApiUser>
 */
class ApiUserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ApiUser::class);
    }

    public function findOneByEmail(string $email): ?ApiUser
    {
        return $this->findOneBy(['email' => $email]);
    }

    public function findActiveByOrganization(string $organizationId): array
    {
        return $this->createQueryBuilder('a')
            ->leftJoin('a.user', 'u')
            ->where('u.organization_id = :orgId')
            ->andWhere('a.status = :status')
            ->setParameter('orgId', $organizationId)
            ->setParameter('status', 'active')
            ->getQuery()
            ->getResult();
    }
}