<?php

namespace App\Controllers\Auth;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Shield\Controllers\RegisterController as ShieldRegisterController;

class RegisterController extends ShieldRegisterController
{
    public function resendVerification()
    {
        /** @var Session $authenticator */
        $authenticator = auth('session')->getAuthenticator();

        // If an action has been defined, start it up.
        if ($authenticator->hasAction()) {
            return $this->response->setJSON(['status' => 'success', 'success' => true, 'message' => 'Verification code sent']);
        }

        return $this->response->setJSON(['status' => 'success', 'success' => true, 'message' => 'Verification code sent']);
    }
}