<?php

namespace App\Repository;

use App\Entity\Session;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class SessionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Session::class);
    }

    public function findByTraining(int $trainingId): array
    {
        return $this->findBy(['training' => $trainingId], ['dateDebut' => 'ASC']);
    }

    public function findByOrganization(string $organizationId): array
    {
        return $this->findBy(['organizationId' => $organizationId], ['dateDebut' => 'ASC']);
    }
}