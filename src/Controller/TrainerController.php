<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\ApiUser;
use App\Entity\Trainer;
use App\Repository\TrainerRepository;
use App\Security\ApiKeyUser;
use App\Security\OrganizationScopedUser;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

final class TrainerController extends AbstractController
{
    public function __construct(
        private TrainerRepository $trainerRepo,
        private EntityManagerInterface $em,
    ) {
    }

    #[Route('/api/v1/trainers', name: 'api_trainers_list', methods: ['GET'])]
    public function list(#[CurrentUser] ?OrganizationScopedUser $user): JsonResponse
    {
        if (!$user) {
            return $this->json(['error' => 'Not authenticated'], 401);
        }

        if ($user instanceof ApiKeyUser && !$user->hasPermission('trainers:read')) {
            return $this->json(['error' => 'API key lacks trainers:read permission'], 403);
        }

        $trainers = $this->trainerRepo->findByOrganizations($user->getOrganizationIds());

        return $this->json(array_map(fn (Trainer $t) => [
            'id' => $t->getId(),
            'first_name' => $t->getFirstName(),
            'last_name' => $t->getLastName(),
            'email' => $t->getEmail(),
            'is_archived' => $t->isArchived(),
            'is_allow_send_email' => $t->isAllowSendEmail(),
            'is_organization' => $t->isOrganization(),
            'is_public' => $t->isPublic(),
            'comments' => $t->getComments(),
            'organization_id' => $t->getOrganizationId(),
            'created_at' => $t->getCreatedAt()->format(DATE_ATOM),
        ], $trainers));
    }

    #[Route('/api/v1/trainers', name: 'api_trainers_create', methods: ['POST'])]
    public function create(Request $request, #[CurrentUser] ?ApiUser $user): JsonResponse
    {
        if (!$user) {
            return $this->json(['error' => 'Not authenticated'], 401);
        }

        $data = json_decode($request->getContent(), true) ?? [];
        $firstName = trim($data['first_name'] ?? '');
        $lastName = trim($data['last_name'] ?? '');
        $email = trim($data['email'] ?? '');

        if ($firstName === '' || $lastName === '' || $email === '') {
            return $this->json(['error' => 'first_name, last_name and email are required'], 400);
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->json(['error' => 'email is invalid'], 400);
        }

        $existing = $this->trainerRepo->findOneBy([
            'email' => $email,
            'organizationId' => $user->getOrganizationId(),
        ]);
        if ($existing) {
            return $this->json(['error' => 'Cette adresse email est deja utilisee.'], 409);
        }

        $trainer = new Trainer($firstName, $lastName, $email, $user->getOrganizationId());

        if (array_key_exists('is_allow_send_email', $data)) {
            $trainer->setIsAllowSendEmail((bool) $data['is_allow_send_email']);
        }
        if (array_key_exists('is_public', $data)) {
            $trainer->setIsPublic((bool) $data['is_public']);
        }
        if (array_key_exists('comments', $data)) {
            $trainer->setComments($data['comments'] !== null ? (string) $data['comments'] : null);
        }

        $this->em->persist($trainer);
        $this->em->flush();

        return $this->json([
            'id' => $trainer->getId(),
            'first_name' => $trainer->getFirstName(),
            'last_name' => $trainer->getLastName(),
            'email' => $trainer->getEmail(),
            'is_allow_send_email' => $trainer->isAllowSendEmail(),
            'is_public' => $trainer->isPublic(),
            'comments' => $trainer->getComments(),
            'organization_id' => $trainer->getOrganizationId(),
            'created_at' => $trainer->getCreatedAt()->format(DATE_ATOM),
        ], 201);
    }
}
