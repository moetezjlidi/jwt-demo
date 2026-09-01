<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\ApiUser;
use App\Entity\Inscription;
use App\Repository\InscriptionRepository;
use App\Repository\SessionRepository;
use App\Repository\TraineeRepository;
use App\Security\ApiKeyUser;
use App\Security\OrganizationScopedUser;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

final class InscriptionController extends AbstractController
{
    public function __construct(
        private InscriptionRepository $inscriptionRepo,
        private TraineeRepository $traineeRepo,
        private SessionRepository $sessionRepo,
        private EntityManagerInterface $em,
    ) {
    }

    #[Route('/api/v1/inscriptions', name: 'api_inscriptions_list', methods: ['GET'])]
    public function list(#[CurrentUser] ?OrganizationScopedUser $user): JsonResponse
    {
        if (!$user) {
            return $this->json(['error' => 'Not authenticated'], 401);
        }

        if ($user instanceof ApiKeyUser && !$user->hasPermission('inscriptions:read')) {
            return $this->json(['error' => 'API key lacks inscriptions:read permission'], 403);
        }

        $inscriptions = $this->inscriptionRepo->findByOrganizations($user->getOrganizationIds());

        return $this->json(array_map(fn (Inscription $i) => $this->serialize($i), $inscriptions));
    }

    #[Route('/api/v1/inscriptions', name: 'api_inscriptions_create', methods: ['POST'])]
    public function create(Request $request, #[CurrentUser] ?ApiUser $user): JsonResponse
    {
        if (!$user) {
            return $this->json(['error' => 'Not authenticated'], 401);
        }

        if (!in_array('ROLE_PLATFORM_ADMIN', $user->getRoles(), true)) {
            return $this->json(['error' => 'Seul un administrateur de la plateforme peut créer une inscription'], 403);
        }

        $data = json_decode($request->getContent(), true) ?? [];
        $traineeId = (int) ($data['trainee_id'] ?? 0);
        $sessionId = (int) ($data['session_id'] ?? 0);

        if ($traineeId <= 0 || $sessionId <= 0) {
            return $this->json(['error' => 'trainee_id and session_id are required'], 400);
        }

        $trainee = $this->traineeRepo->findOneByIdAndOrganization($traineeId, $user->getOrganizationId());
        if (!$trainee) {
            return $this->json(['error' => 'Stagiaire introuvable pour cette organisation'], 404);
        }

        $session = $this->sessionRepo->findOneBy(['id' => $sessionId, 'organizationId' => $user->getOrganizationId()]);
        if (!$session) {
            return $this->json(['error' => 'Session introuvable pour cette organisation'], 404);
        }

        $existing = $this->inscriptionRepo->findOneBy(['trainee' => $trainee, 'session' => $session]);
        if ($existing) {
            return $this->json(['error' => 'Cet utilisateur est deja inscrit a cette session !'], 409);
        }

        $inscription = new Inscription($trainee, $session);

        if (array_key_exists('inscription_status', $data) && $data['inscription_status'] !== null) {
            $inscription->setInscriptionStatus((string) $data['inscription_status']);
        }
        if (array_key_exists('motivation', $data)) {
            $inscription->setMotivation($data['motivation'] !== null ? (string) $data['motivation'] : null);
        }

        $this->em->persist($inscription);
        $this->em->flush();

        return $this->json($this->serialize($inscription), 201);
    }

    private function serialize(Inscription $i): array
    {
        return [
            'id' => $i->getId(),
            'trainee_id' => $i->getTrainee()->getId(),
            'session_id' => $i->getSession()->getId(),
            'inscription_status' => $i->getInscriptionStatus(),
            'presence_status' => $i->getPresenceStatus(),
            'motivation' => $i->getMotivation(),
            'message' => $i->getMessage(),
            'refuse' => $i->getRefuse(),
            'dif' => $i->isDif(),
            'organization_id' => $i->getOrganizationId(),
            'created_at' => $i->getCreatedAt()->format(DATE_ATOM),
        ];
    }
}
