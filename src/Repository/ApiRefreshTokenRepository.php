<?php

namespace App\Repository;

use App\Entity\ApiRefreshToken;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ApiRefreshToken>
 */
class ApiRefreshTokenRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ApiRefreshToken::class);
    }

    public function findValidByTokenHash(string $tokenHash): ?ApiRefreshToken
    {
        return $this->createQueryBuilder('t')
            ->where('t.tokenHash = :hash')
            ->andWhere('t.revoked = false')
            ->andWhere('t.expiresAt > CURRENT_TIMESTAMP()')
            ->setParameter('hash', $tokenHash)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function revokeAllForApiUser(int $apiUserId): void
    {
        $this->createQueryBuilder('t')
            ->update()
            ->set('t.revoked', true)
            ->where('t.apiUser = :apiUserId')
            ->setParameter('apiUserId', $apiUserId)
            ->getQuery()
            ->execute();
    }
}