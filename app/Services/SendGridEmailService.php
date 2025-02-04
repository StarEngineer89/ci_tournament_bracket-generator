<?php
namespace App\Services;

use SendGrid;
use SendGrid\Mail\Mail;

class SendGridEmailService
{
    protected $apiKey;

    public function __construct()
    {
        $this->apiKey = getenv('SENDGRID_API_KEY');
    }

    public function send($to, $subject, $message)
    {
        $email = new Mail();
        $email->setFrom(getenv('Email.fromEmail'), getenv('Email.fromName'));
        $email->setSubject($subject);
        $email->addTo($to);
        $email->addContent("text/html", $message);

        $sendgrid = new SendGrid($this->apiKey);
        
        try {
            $response = $sendgrid->send($email);
            return $response->statusCode() == 202 ? 'Email sent successfully!' : 'Failed to send email';
        } catch (\Exception $e) {
            return 'Error: ' . $e->getMessage();
        }
    }
}