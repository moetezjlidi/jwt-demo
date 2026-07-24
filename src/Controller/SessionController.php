<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\ApiUser;
use App\Repository\SessionRepository;
use App\Repository\SessionScheduleRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

final class SessionController extends AbstractController
{
    public function __construct(
        private SessionRepository $sessionRepo,
        private SessionScheduleRepository $scheduleRepo,
    ) {}

    #[Route('/api/v1/trainings/{id}/sessions', name: 'api_sessions_list', methods: ['GET'])]
    public function list(int $id, #[CurrentUser] ?ApiUser $user): JsonResponse
    {
        if (!$user) {
            return $this->json(['error' => 'Not authenticated'], 401);
        }

        $sessions = $this->sessionRepo->findByTraining($id);

        $result = array_map(function ($s) {
            $schedules = $this->scheduleRepo->findBySession($s->getId());

            return [
                'id' => $s->getId(),
                'name' => $s->getName(),
                'inscriptions' => $s->getInscriptions(),
                'afficher_en_ligne' => $s->isAfficherEnLigne(),
                'statut' => $s->getStatut(),
                'date_debut' => $s->getDateDebut()->format('d/m/Y'),
                'date_fin' => $s->getDateFin()->format('d/m/Y'),
                'participants_max' => $s->getParticipantsMax(),
                'date_limite' => $s->getDateLimite()?->format('d/m/Y'),
                'nombre_heures' => $s->getNombreHeures(),
                'nombre_jours' => $s->getNombreJours(),
                'schedules' => array_map(fn($sc) => [
                    'date_debut' => $sc->getDateDebut()->format('d/m/Y'),
                    'date_fin' => $sc->getDateFin()->format('d/m/Y'),
                    'horaires_matin' => $sc->getHorairesMatin(),
                    'nombre_heures_matin' => $sc->getNombreHeuresMatin(),
                    'horaires_apres_midi' => $sc->getHorairesApresMidi(),
                    'nombre_heures_apres_midi' => $sc->getNombreHeuresApresMidi(),
                    'lieu' => $sc->getLieu(),
                ], $schedules),
            ];
        }, $sessions);

        return $this->json($result);
    }
}