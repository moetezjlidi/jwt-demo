<?php

declare(strict_types=1);

namespace App\Security;

interface OrganizationScopedUser
{
    /** @return string[] organizations this principal is allowed to access */
    public function getOrganizationIds(): array;
}
