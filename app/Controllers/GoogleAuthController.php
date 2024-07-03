<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use Google_Client;
use Google_Service_Oauth2;
use CodeIgniter\Shield\Entities\User;
use CodeIgniter\Shield\Models\UserModel;

class GoogleAuthController extends Controller
{
    protected $googleClient;

    public function __construct()
    {
        $this->googleClient = new Google_Client();
        $this->googleClient->setClientId(env('GOOGLE_CLIENT_ID'));
        $this->googleClient->setClientSecret(env('GOOGLE_CLIENT_SECRET'));
        $this->googleClient->setRedirectUri(env('GOOGLE_REDIRECT_URI'));
        $this->googleClient->addScope('email');
        $this->googleClient->addScope('profile');
    }

    public function login()
    {
        $authUrl = $this->googleClient->createAuthUrl();
        return redirect()->to($authUrl);
    }

    public function callback()
    {
        $code = $this->request->getVar('code');

        if ($code) {
            $token = $this->googleClient->fetchAccessTokenWithAuthCode($code);
            $this->googleClient->setAccessToken($token['access_token']);

            $googleService = new Google_Service_Oauth2($this->googleClient);
            $googleUser = $googleService->userinfo->get();

            $userModel = new UserModel();
            $existingUser = $userModel->findByCredentials(['email' => $googleUser->email]);

            if ($existingUser) {
                // Login existing user
                auth()->login($existingUser);
            } else {
                // Register new user
                $user = new User([
                    'email' => $googleUser->email,
                    'username' => $googleUser->name,
                    'password' => bin2hex(random_bytes(16)), // Dummy password
                    'active' => 1
                ]);

                $userModel->save($user);

                $user = $userModel->find($userModel->getInsertID());
                auth()->login($user);
            }

            if (auth()->user()) {
                session()->setTempdata('welcome_message', 'Welcome, ' . auth()->user()->username . '!');
            }

            return redirect()->to('/tournaments');
        }

        return redirect()->to('/login');
    }
}