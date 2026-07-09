<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Entity\ApiUser;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener(event: 'lexik_jwt_authentication.on_jwt_created')]
final class JwtCreatedListener
{
    public function __invoke(JWTCreatedEvent $event): void
    {
        $user = $event->getUser();

        if (!$user instanceof ApiUser) {
            return;
        }

        $payload = $event->getData();
        $payload['org_id'] = $user->getOrganizationId();
        $payload['roles'] = $user->getRoles();
        $payload['email'] = $user->getEmail();

        $event->setData($payload);
    }
}
