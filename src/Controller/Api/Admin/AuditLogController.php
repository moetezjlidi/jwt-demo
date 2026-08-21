<?php

declare(strict_types=1);

namespace App\Controller\Api\Admin;

use App\Entity\ApiUser;
use App\Repository\AuditLogRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route('/api/v1/admin/audit-logs')]
final class AuditLogController extends AbstractController
{
    public function __construct(
        private AuditLogRepository $auditLogRepo,
    ) {}

    /**
     * GET /api/v1/admin/audit-logs
     */
    #[Route('', methods: ['GET'])]
    public function list(#[CurrentUser] ?ApiUser $user): JsonResponse
    {
        if (!$user) {
            return $this->json(['error' => 'Not authenticated'], 401);
        }

        $logs = $this->auditLogRepo->findByOrganization($user->getOrganizationId());

        return $this->json(array_map(fn($l) => $l->toArray(), $logs));
    }

    /**
     * GET /api/v1/admin/audit-logs/{id}
     */
    #[Route('/{id}', methods: ['GET'])]
    public function get(int $id, #[CurrentUser] ?ApiUser $user): JsonResponse
    {
        if (!$user) {
            return $this->json(['error' => 'Not authenticated'], 401);
        }

        $log = $this->auditLogRepo->findOneByIdAndOrganization($id, $user->getOrganizationId());
        if (!$log) {
            return $this->json(['error' => 'Not found'], 404);
        }

        return $this->json($log->toArray());
    }
}