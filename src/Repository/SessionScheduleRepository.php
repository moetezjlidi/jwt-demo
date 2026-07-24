<?php

namespace App\Repository;

use App\Entity\SessionSchedule;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class SessionScheduleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SessionSchedule::class);
    }

    public function findBySession(int $sessionId): array
    {
        return $this->findBy(['session' => $sessionId], ['dateDebut' => 'ASC']);
    }
}