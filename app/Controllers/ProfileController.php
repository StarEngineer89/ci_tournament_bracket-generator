<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Shield\Entities\User;
use CodeIgniter\Shield\Models\UserModel;
use CodeIgniter\Shield\Authentication\Authenticators\Session;

class ProfileController extends BaseController
{
    protected $session;

    public function __construct()
    {
        $this->session = service('session');
    }

    public function index() {
        $userModel = new UserModel();
        $user = $userModel->find(auth()->user()->id);

        return view('profile/user_profile', ['userInfo' => $user]);
    }

    public function changeEmail()
    {
        return view('profile/change_email');
    }

    public function updateEmail()
    {
        $validation = service('validation');

        // Validate input
        $validation->setRules([
            'new_email' => 'required|valid_email|is_unique[users.email]',
        ]);

        if (!$validation->withRequest($this->request)->run()) {
            return redirect()->back()->withInput()->with('errors', $validation->getErrors());
        }

        // Get current user
        $user = auth()->user();

        // Update email
        $newEmail = $this->request->getPost('new_email');
        $user->email = $newEmail;
        $user->email_verified_at = null; // Mark email as unverified
        $userModel = new UserModel();
        $userModel->save($user);

        // Send verification email
        auth()->sendVerificationEmail($user);

        return redirect()->back()->with('message', 'Email updated. Please verify the new email address.');
    }

    public function changePassword()
    {
        return view('profile/change_password');
    }

    public function updatePassword()
    {
        if ($this->request->isAJAX()) {
            $currentPassword = $this->request->getPost('current_password');
            $newPassword = $this->request->getPost('new_password');
            $confirmPassword = $this->request->getPost('confirm_password');

            // Validate the form input
            $validation = \Config\Services::validation();
            $validation->setRules([
                'current_password' => 'required',
                'new_password' => 'required|min_length[8]',
                'confirm_password' => 'required|matches[new_password]'
            ]);

            if (!$validation->withRequest($this->request)->run()) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => implode('<br>', $validation->getErrors())
                ]);
            }

            $auth = service('auth');
            $user = $auth->user();
            
            // Verify the current password
            if (!$auth->check(['email' => $user->email, 'password' => $currentPassword])->isOK()) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Current password is incorrect.'
                ]);
            }

            // Update the password
            $user->setPassword($newPassword);

            $userModel = new UserModel();
            $userModel->save($user);

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Password successfully updated.'
            ]);
        }
    }
}