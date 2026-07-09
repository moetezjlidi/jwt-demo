<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Attribute\Route;

final class MockShibbolethController extends AbstractController
{
    /**
     * Simulates a Shibboleth reverse proxy's behavior.
     * 
     * In production:
     * - Apache/Nginx with mod_shib would intercept requests to protected routes
     * - On successful SSO with IdP, it would set REMOTE_USER
     * - Then forward the request to your Symfony app
     * 
     * In this demo:
     * - We manually set REMOTE_USER in the request
     * - Then redirect to /auth/shibboleth
     * 
     * Usage: Visit http://localhost:8000/mock-shibboleth?REMOTE_USER=alice
     * If no REMOTE_USER is provided, defaults to 'testuser'
     */
    #[Route('/mock-shibboleth', name: 'mock_shibboleth', methods: ['GET'])]
    public function simulateShibboleth(Request $request): RedirectResponse
    {
        // Get the user from query params (simulating SSO result)
        $remoteUser = $request->query->get('REMOTE_USER', 'testuser');

        // Set it in the request server variables
        // (In production, Apache mod_shib would do this automatically)
        $request->server->set('REMOTE_USER', $remoteUser);

        // Redirect to the JWT issuance endpoint
        // The ShibbolethAuthenticator will pick up REMOTE_USER from the server variables
        return $this->redirect('/auth/shibboleth');
    }
}