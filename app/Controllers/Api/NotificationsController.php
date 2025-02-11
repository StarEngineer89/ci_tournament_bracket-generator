<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Services\NotificationService;

class NotificationsController extends BaseController
{
    protected $notificationService;
    
    public function __construct()
    {
        $this->notificationService = new NotificationService();
    }

    /**
     * Mark a notification as read.
     *
     * @param int $id Notification ID
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function markAsRead($id)
    {
        if ($this->notificationService->markAsRead($id)) {
            return $this->response->setJSON(['status' => 'success', 'message' => 'Notification marked as read']);
        }

        return $this->response->setJSON(['status' => 'error', 'message' => 'Failed to mark notification as read']);
    }

    /**
     * Delete a notification.
     *
     * @param int $id Notification ID
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function delete($id)
    {
        if ($this->notificationService->deleteNotification($id)) {
            return $this->response->setJSON(['status' => 'success', 'message' => 'Notification deleted']);
        }

        return $this->response->setJSON(['status' => 'error', 'message' => 'Failed to delete notification']);
    }

    public function clear()
    {
        $user_id = auth()->user() ? auth()->user()->id : 0;
        if ($this->notificationService->clearNotifications($user_id)) {
            return $this->response->setJSON(['status' => 'success', 'message' => 'All notifications were deleted.']);
        }

        return $this->response->setJSON(['status' => 'error', 'message' => 'Failed to clear the notifications.']);
    }
}