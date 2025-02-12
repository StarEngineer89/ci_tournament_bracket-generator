<?php

namespace App\Controllers\Auth;

use App\Controllers\BaseController;
use CodeIgniter\Events\Events;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\Shield\Controllers\RegisterController as ShieldRegisterController;
use CodeIgniter\Shield\Exceptions\ValidationException;

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

    public function abortVerification()
    {
        $authenticator = auth('session')->getAuthenticator();

        $user = $authenticator->getPendingUser();
        $users = $this->getUserProvider();
        $result = $users->delete($user->id, true);

        // Success!
        return redirect()->to('/')->with('message', '');
    }
}