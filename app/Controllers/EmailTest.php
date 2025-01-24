<?php

namespace App\Controllers;

use CodeIgniter\Controller;

class EmailTest extends Controller
{
    public function sendTestEmail()
    {
        $email = \Config\Services::email();

        // Set email configuration (optional if already in .env)
        $email->setFrom(env('email.fromEmail'), env('email.fromName'));
        $email->setTo('mrg8406@gmail.com'); // Replace with your test email
        $email->setSubject('Test Email for Tournament Creator');
        $email->setMessage('<p>This is a test email sent for Tournament Creator.</p>');

        // Try sending the email
        if ($email->send()) {
            return "Email successfully sent.";
        } else {
            // Debug if email sending fails
            return $email->printDebugger(['headers', 'subject', 'body']);
        }
    }
}