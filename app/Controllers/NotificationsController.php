<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;

class NotificationsController extends ResourceController
{
    protected $modelName = 'App\Models\NotificationModel';
    protected $format = 'json';

    /**
     * Get all notifications for a company.
     *
     * @param string|null $companyId
     * @return JSON
     */
    public function index()
    {
        $companyId = $this->request->getGet('company_id');
        if (!$companyId) {
            return $this->failValidationError('Company ID is required.');
        }

        $page = $this->request->getVar('page') ?? 1;
        $perPage = $this->request->getVar('perPage');
        if (!$perPage) {
            $perPage = null;
        }
        $subject = $this->request->getVar('subject');
        $is_read = $this->request->getVar('is_read');
        $where = [];
        $where['company_id like'] = '%' . $companyId . '%';
        if ($subject) {
            $where['subject like'] = '%' . $subject . '%';
        }
        if ($is_read) {
            $where['is_read like'] = '%' . $is_read . '%';
        }
        $totalNotifications = $this->model->selectCount('id')->where($where)->get()->getRowArray()['id'];
        $notifications = $this->model->where($where)->orderBy('created_at', 'DESC')->paginate($perPage, 'notifications', $page);
        $data = [
            'status' => true,
            'data' => [
                'notifications' => $notifications,
                'total' => $totalNotifications,
            ],
        ];

        return $this->respond($data);

        // $notifications = $this->model->where('company_id', $companyId)->findAll();

        // return $this->respond($notifications);
    }

    /**
     * Get a specific notification by ID.
     *
     * @param string|null $id
     * @return JSON
     */
    public function show($id = null)
    {
        $notification = $this->model->find($id);

        if (!$notification) {
            return $this->failNotFound('Notification not found.');
        }

        return $this->respond($notification);
    }

    /**
     * Create a new notification.
     *
     * @return JSON
     */
    public function create()
    {
        $data = $this->request->getJSON(true);

        if (!$this->model->insert($data)) {
            return $this->failValidationErrors($this->model->errors());
        }

        $data['id'] = $this->model->getInsertID(); // Get the newly created ID
        return $this->respondCreated($data);
    }

    /**
     * Update a specific notification by ID.
     *
     * @param string|null $id
     * @return JSON
     */
    public function update($id = null)
    {
        if (!$this->model->find($id)) {
            return $this->failNotFound('Notification not found.');
        }

        $data = $this->request->getJSON(true);

        if (!$this->model->update($id, $data)) {
            return $this->failValidationErrors($this->model->errors());
        }

        $updatedNotification = $this->model->find($id);
        return $this->respond($updatedNotification);
    }

    /**
     * Delete a specific notification by ID.
     *
     * @param string|null $id
     * @return JSON
     */
    public function delete($id = null)
    {
        if (!$this->model->find($id)) {
            return $this->failNotFound('Notification not found.');
        }

        if (!$this->model->delete($id)) {
            return $this->failServerError('Failed to delete notification.');
        }

        return $this->respondDeleted(['id' => $id]);
    }

    /**
     * Mark a specific notification as read.
     *
     * @param string|null $id
     * @return JSON
     */
    public function markAsRead($id = null)
    {
        if (!$this->model->find($id)) {
            return $this->failNotFound('Notification not found.');
        }

        if (!$this->model->markAsRead($id)) {
            return $this->failServerError('Failed to mark notification as read.');
        }

        $updatedNotification = $this->model->find($id);
        return $this->respond($updatedNotification);
    }

    /**
     * Get all unread notifications for a company.
     *
     * @param string|null $companyId
     * @return JSON
     */
    public function getUnreadNotifications()
    {
        $companyId = $this->request->getGet('company_id');
        if (!$companyId) {
            return $this->failValidationError('Company ID is required.');
        }

        $notifications = $this->model->getUnreadNotifications($companyId);

        return $this->respond($notifications);
    }
}
