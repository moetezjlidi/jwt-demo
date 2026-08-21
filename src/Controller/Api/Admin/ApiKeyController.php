<?php

declare(strict_types=1);

namespace App\Controller\Api\Admin;

use App\Entity\ApiKey;
use App\Entity\ApiUser;
use App\Entity\AuditLog;
use App\Repository\ApiKeyRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route('/api/v1/admin/api-keys')]
final class ApiKeyController extends AbstractController
{
    public function __construct(
        private ApiKeyRepository $apiKeyRepo,
        private EntityManagerInterface $em,
    ) {}

    /**
     * POST /api/v1/admin/api-keys
     * { "name": "Intégration partenaire X", "permissions": ["trainings:read", "trainings:write"] }
     * Retourne la clé en clair UNE SEULE FOIS — elle n'est jamais stockée en clair.
     */
    #[Route('', methods: ['POST'])]
    public function create(Request $request, #[CurrentUser] ?ApiUser $user): JsonResponse
    {
        if (!$user) {
            return $this->json(['error' => 'Not authenticated'], 401);
        }

        $data = json_decode($request->getContent(), true) ?? [];
        $name = trim($data['name'] ?? '');
        $permissions = $data['permissions'] ?? [];

        if ($name === '') {
            return $this->json(['error' => 'name is required'], 400);
        }

        $rawKey = bin2hex(random_bytes(24)); // 48 caractères
        $keyHash = hash('sha256', $rawKey);
        $keyPrefix = substr($rawKey, 0, 8);

        $apiKey = new ApiKey($user->getOrganizationId(), $name, $keyHash, $keyPrefix, $permissions);
        $this->em->persist($apiKey);

        $this->logAudit($user, 'api_key.created', 'ApiKey', null, ['name' => $name]);

        $this->em->flush();

        $response = $apiKey->toArray();
        $response['key'] = $rawKey; // affiché une seule fois
        $response['id'] = $apiKey->getId();

        return $this->json($response, 201);
    }

    /**
     * GET /api/v1/admin/api-keys
     */
    #[Route('', methods: ['GET'])]
    public function list(#[CurrentUser] ?ApiUser $user): JsonResponse
    {
        if (!$user) {
            return $this->json(['error' => 'Not authenticated'], 401);
        }

        $keys = $this->apiKeyRepo->findByOrganization($user->getOrganizationId());

        return $this->json(array_map(fn(ApiKey $k) => $k->toArray(), $keys));
    }

    /**
     * GET /api/v1/admin/api-keys/{id}
     */
    #[Route('/{id}', methods: ['GET'])]
    public function get(int $id, #[CurrentUser] ?ApiUser $user): JsonResponse
    {
        if (!$user) {
            return $this->json(['error' => 'Not authenticated'], 401);
        }

        $apiKey = $this->apiKeyRepo->findOneByIdAndOrganization($id, $user->getOrganizationId());
        if (!$apiKey) {
            return $this->json(['error' => 'Not found'], 404);
        }

        return $this->json($apiKey->toArray());
    }

    /**
     * POST /api/v1/admin/api-keys/{id}/revoke
     */
    #[Route('/{id}/revoke', methods: ['POST'])]
    public function revoke(int $id, #[CurrentUser] ?ApiUser $user): JsonResponse
    {
        if (!$user) {
            return $this->json(['error' => 'Not authenticated'], 401);
        }

        $apiKey = $this->apiKeyRepo->findOneByIdAndOrganization($id, $user->getOrganizationId());
        if (!$apiKey) {
            return $this->json(['error' => 'Not found'], 404);
        }

        $apiKey->revoke();
        $this->logAudit($user, 'api_key.revoked', 'ApiKey', $id);
        $this->em->flush();

        return $this->json($apiKey->toArray());
    }

    /**
     * POST /api/v1/admin/api-keys/{id}/rotate
     * Révoque l'ancienne clé et en génère une nouvelle avec les mêmes permissions.
     */
    #[Route('/{id}/rotate', methods: ['POST'])]
    public function rotate(int $id, #[CurrentUser] ?ApiUser $user): JsonResponse
    {
        if (!$user) {
            return $this->json(['error' => 'Not authenticated'], 401);
        }

        $apiKey = $this->apiKeyRepo->findOneByIdAndOrganization($id, $user->getOrganizationId());
        if (!$apiKey) {
            return $this->json(['error' => 'Not found'], 404);
        }

        $rawKey = bin2hex(random_bytes(24));
        $apiKey->setKeyHash(hash('sha256', $rawKey));
        $apiKey->setKeyPrefix(substr($rawKey, 0, 8));

        $this->logAudit($user, 'api_key.rotated', 'ApiKey', $id);
        $this->em->flush();

        $response = $apiKey->toArray();
        $response['key'] = $rawKey;

        return $this->json($response);
    }

    /**
     * PATCH /api/v1/admin/api-keys/{id}
     * { "name": "...", "permissions": [...] }
     */
    #[Route('/{id}', methods: ['PATCH'])]
    public function update(int $id, Request $request, #[CurrentUser] ?ApiUser $user): JsonResponse
    {
        if (!$user) {
            return $this->json(['error' => 'Not authenticated'], 401);
        }

        $apiKey = $this->apiKeyRepo->findOneByIdAndOrganization($id, $user->getOrganizationId());
        if (!$apiKey) {
            return $this->json(['error' => 'Not found'], 404);
        }

        $data = json_decode($request->getContent(), true) ?? [];

        if (isset($data['name'])) {
            $apiKey->setName($data['name']);
        }
        if (isset($data['permissions'])) {
            $apiKey->setPermissions($data['permissions']);
        }

        $this->logAudit($user, 'api_key.updated', 'ApiKey', $id, $data);
        $this->em->flush();

        return $this->json($apiKey->toArray());
    }

    private function logAudit(ApiUser $user, string $action, ?string $targetType = null, ?int $targetId = null, ?array $metadata = null): void
    {
        $log = new AuditLog($user->getOrganizationId(), $user->getEmail(), $action, $targetType, $targetId, $metadata);
        $this->em->persist($log);
    }
}