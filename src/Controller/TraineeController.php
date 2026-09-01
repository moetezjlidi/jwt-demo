<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\ApiUser;
use App\Entity\Trainee;
use App\Repository\TraineeRepository;
use App\Security\ApiKeyUser;
use App\Security\OrganizationScopedUser;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

final class TraineeController extends AbstractController
{
    public function __construct(
        private TraineeRepository $traineeRepo,
        private EntityManagerInterface $em,
    ) {
    }

    #[Route('/api/v1/trainees', name: 'api_trainees_list', methods: ['GET'])]
    public function list(#[CurrentUser] ?OrganizationScopedUser $user): JsonResponse
    {
        if (!$user) {
            return $this->json(['error' => 'Not authenticated'], 401);
        }

        if ($user instanceof ApiKeyUser && !$user->hasPermission('trainees:read')) {
            return $this->json(['error' => 'API key lacks trainees:read permission'], 403);
        }

        $trainees = $this->traineeRepo->findByOrganizations($user->getOrganizationIds());

        return $this->json(array_map(fn (Trainee $t) => $this->serialize($t), $trainees));
    }

    #[Route('/api/v1/trainees', name: 'api_trainees_create', methods: ['POST'])]
    public function create(Request $request, #[CurrentUser] ?ApiUser $user): JsonResponse
    {
        if (!$user) {
            return $this->json(['error' => 'Not authenticated'], 401);
        }

        if (!in_array('ROLE_PLATFORM_ADMIN', $user->getRoles(), true)) {
            return $this->json(['error' => 'Seul un administrateur de la plateforme peut créer un stagiaire'], 403);
        }

        $data = json_decode($request->getContent(), true) ?? [];
        $firstName = trim($data['first_name'] ?? '');
        $lastName = trim($data['last_name'] ?? '');
        $email = trim($data['email'] ?? '');
        $institution = isset($data['institution']) ? trim((string) $data['institution']) : null;

        if ($firstName === '' || $lastName === '' || $email === '') {
            return $this->json(['error' => 'first_name, last_name and email are required'], 400);
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->json(['error' => 'email is invalid'], 400);
        }

        $existing = $this->traineeRepo->findOneBy([
            'email' => $email,
            'institution' => $institution,
            'organizationId' => $user->getOrganizationId(),
        ]);
        if ($existing) {
            return $this->json(['error' => 'Cette adresse email est deja utilisee.'], 409);
        }

        $trainee = new Trainee($firstName, $lastName, $email, $user->getOrganizationId());
        $trainee->setInstitution($institution);

        $optionalFields = [
            'birth_date' => 'setBirthDate',
            'amu_statut' => 'setAmuStatut',
            'bap' => 'setBap',
            'corps' => 'setCorps',
            'category' => 'setCategory',
            'campus' => 'setCampus',
            'first_name_sup' => 'setFirstNameSup',
            'last_name_sup' => 'setLastNameSup',
            'email_sup' => 'setEmailSup',
            'first_name_corr' => 'setFirstNameCorr',
            'last_name_corr' => 'setLastNameCorr',
            'email_corr' => 'setEmailCorr',
            'fonction' => 'setFonction',
        ];
        foreach ($optionalFields as $key => $setter) {
            if (array_key_exists($key, $data)) {
                $trainee->$setter($data[$key] !== null ? (string) $data[$key] : null);
            }
        }

        $this->em->persist($trainee);
        $this->em->flush();

        return $this->json($this->serialize($trainee), 201);
    }

    private function serialize(Trainee $t): array
    {
        return [
            'id' => $t->getId(),
            'first_name' => $t->getFirstName(),
            'last_name' => $t->getLastName(),
            'email' => $t->getEmail(),
            'institution' => $t->getInstitution(),
            'birth_date' => $t->getBirthDate(),
            'amu_statut' => $t->getAmuStatut(),
            'bap' => $t->getBap(),
            'corps' => $t->getCorps(),
            'category' => $t->getCategory(),
            'campus' => $t->getCampus(),
            'first_name_sup' => $t->getFirstNameSup(),
            'last_name_sup' => $t->getLastNameSup(),
            'email_sup' => $t->getEmailSup(),
            'first_name_corr' => $t->getFirstNameCorr(),
            'last_name_corr' => $t->getLastNameCorr(),
            'email_corr' => $t->getEmailCorr(),
            'fonction' => $t->getFonction(),
            'organization_id' => $t->getOrganizationId(),
            'created_at' => $t->getCreatedAt()->format(DATE_ATOM),
        ];
    }
}
