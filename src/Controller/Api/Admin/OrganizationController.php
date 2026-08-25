<?php

declare(strict_types=1);

namespace App\Controller\Api\Admin;

use App\Entity\ApiUser;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route('/api/v1/admin/organizations')]
final class OrganizationController extends AbstractController
{
    public function __construct(
        private UserRepository $userRepo,
    ) {}

    /**
     * GET /api/v1/admin/organizations
     * ROLE_PLATFORM_ADMIN sees every organization (for the API key creation checkboxes).
     * Everyone else only ever sees their own — there's nothing else to pick from.
     */
    #[Route('', methods: ['GET'])]
    public function list(#[CurrentUser] ?ApiUser $user): JsonResponse
    {
        if (!$user) {
            return $this->json(['error' => 'Not authenticated'], 401);
        }

        $organizationIds = in_array('ROLE_PLATFORM_ADMIN', $user->getRoles(), true)
            ? $this->userRepo->findAllOrganizationIds()
            : [$user->getOrganizationId()];

        return $this->json(array_map(fn(string $id) => ['id' => $id], $organizationIds));
    }
}
