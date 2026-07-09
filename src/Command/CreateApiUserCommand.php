<?php
// src/Command/CreateApiUserCommand.php

namespace App\Command;

use App\Entity\ApiUser;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class CreateApiUserCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $em,
        private UserRepository $userRepo,
        private UserPasswordHasherInterface $passwordHasher,  // Inject this instead
    ) {
        parent::__construct();
    }

    public static function getDefaultName(): ?string
    {
        return 'app:api-user:create';
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Create an API user (links to existing User)')
            ->addArgument('email', InputArgument::REQUIRED, 'Email')
            ->addArgument('user_id', InputArgument::REQUIRED, 'Existing User ID')
            ->addOption('role', 'r', InputOption::VALUE_REQUIRED, 'Role', 'ROLE_API_RH')
            ->addOption('password', 'p', InputOption::VALUE_REQUIRED, 'Password');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $email = $input->getArgument('email');
        $userId = (int)$input->getArgument('user_id');
        $role = $input->getOption('role');
        $password = $input->getOption('password');

        $user = $this->userRepo->find($userId);
        if (!$user) {
            $io->error("User with ID $userId not found");
            return Command::FAILURE;
        }

        if (!$password) {
            $password = bin2hex(random_bytes(16));
        }

        $apiUser = new ApiUser();
        $apiUser->setEmail($email);
        $apiUser->setUser($user);
        $apiUser->setRoles([$role]);
        $apiUser->setStatus('active');

        // Use injected hasher instead
        $hashedPassword = $this->passwordHasher->hashPassword($apiUser, $password);
        $apiUser->setPasswordHash($hashedPassword);

        $this->em->persist($apiUser);
        $this->em->flush();

        $io->success("✓ API User created!");
        $io->table(['Key', 'Value'], [
            ['Email', $email],
            ['Password', $password],
            ['Organization ID', $user->organization_id],
            ['Linked User', $user->username],
            ['Role', $role],
        ]);

        return Command::SUCCESS;
    }
}