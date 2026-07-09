<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\ApiUser;
use App\Entity\Training;
use App\Repository\TrainingRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

final class TrainingController extends AbstractController
{
    public function __construct(
        private TrainingRepository $trainingRepo,
        private EntityManagerInterface $em,
    ) {
    }

    #[Route('/api/v1/trainings', name: 'api_trainings_list', methods: ['GET'])]
    public function list(#[CurrentUser] ?ApiUser $user): JsonResponse
    {
        if (!$user) {
            return $this->json(['error' => 'Not authenticated'], 401);
        }

        $trainings = $this->trainingRepo->findByOrganization($user->getOrganizationId());

        return $this->json(array_map(fn (Training $t) => [
            'id' => $t->getId(),
            'title' => $t->getTitle(),
            'organization_id' => $t->getOrganizationId(),
            'created_by' => $t->getCreatedByEmail(),
            'created_at' => $t->getCreatedAt()->format(DATE_ATOM),
        ], $trainings));
    }

    #[Route('/api/v1/trainings', name: 'api_trainings_create', methods: ['POST'])]
    public function create(Request $request, #[CurrentUser] ?ApiUser $user): JsonResponse
    {
        if (!$user) {
            return $this->json(['error' => 'Not authenticated'], 401);
        }

        $data = json_decode($request->getContent(), true) ?? [];
        $title = trim($data['title'] ?? '');

        if ($title === '') {
            return $this->json(['error' => 'title is required'], 400);
        }

        $training = new Training($title, $user->getOrganizationId(), $user->getEmail());
        $this->em->persist($training);
        $this->em->flush();

        return $this->json([
            'id' => $training->getId(),
            'title' => $training->getTitle(),
            'organization_id' => $training->getOrganizationId(),
            'created_by' => $training->getCreatedByEmail(),
            'created_at' => $training->getCreatedAt()->format(DATE_ATOM),
        ], 201);
    }
}
