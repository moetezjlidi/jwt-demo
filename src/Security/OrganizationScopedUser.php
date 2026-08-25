<?php

declare(strict_types=1);

namespace App\Security;

interface OrganizationScopedUser
{
    public function getOrganizationId(): string;
}
