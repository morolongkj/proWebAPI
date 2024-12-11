<?php

namespace App\Models;

use CodeIgniter\Model;

class NotificationModel extends Model
{
    protected $table = 'notifications'; // Table name
    protected $primaryKey = 'id'; // Primary key

    protected $useAutoIncrement = false; // `id` is not auto-incremented as it's a VARCHAR
    protected $returnType = 'array'; // Return results as an associative array

    protected $allowedFields = [
        'id',
        'company_id',
        'subject',
        'message',
        'is_read',
        'created_at',
        'updated_at',
    ]; // Fields allowed to be inserted or updated

    protected $useTimestamps = true; // Automatically handle created_at and updated_at
    protected $createdField = 'created_at'; // Field for creation timestamp
    protected $updatedField = 'updated_at'; // Field for update timestamp

    protected $validationRules = [
        'company_id' => 'permit_empty|string|max_length[255]',
        'subject' => 'required|string|max_length[255]',
        'message' => 'required|string',
        'is_read' => 'required|integer|in_list[0,1]',
    ]; // Validation rules for input data

    protected $validationMessages = [
        'subject' => [
            'required' => 'The subject field is required.',
            'max_length' => 'The subject must not exceed 255 characters.',
        ],
        'message' => [
            'required' => 'The message field is required.',
        ],
        'is_read' => [
            'in_list' => 'The is_read field must be either 0 or 1.',
        ],
    ]; // Custom validation messages

    protected $skipValidation = false; // Enable validation

    protected $beforeInsert = ['generateUuid'];

    /**
     * Automatically generate UUID v4 for the `id` field if it's not provided.
     *
     * @param array $data
     * @return array
     */
    protected function generateUuid(array $data)
    {
        if (empty($data['data']['id'])) {
            $data['data']['id'] = uuid_v4(); // Generate and set UUID v4
        }
        return $data;
    }

    /**
     * Mark a notification as read.
     *
     * @param string $id
     * @return bool
     */
    public function markAsRead(string $id): bool
    {
        return $this->update($id, ['is_read' => 1]);
    }

    /**
     * Fetch unread notifications for a specific company.
     *
     * @param string $companyId
     * @return array
     */
    public function getUnreadNotifications(string $companyId): array
    {
        return $this->where('company_id', $companyId)
            ->where('is_read', 0)
            ->findAll();
    }

    /**
     * Send a notification to a company.
     *
     * @param string $companyId
     * @param string $subject
     * @param string $message
     * @return bool
     * @throws \Exception
     */
    public function sendNotification(string $companyId, string $subject, string $message): bool
    {
        // Load the Companies model
        $companyModel = new \App\Models\CompanyModel();

        // Get the company's email address
        $company = $companyModel->find($companyId);
        if (!$company || empty($company['email'])) {
            throw new \Exception('Company email not found.');
        }

        // Add the notification to the database
        $notificationData = [
            'company_id' => $companyId,
            'subject' => $subject,
            'message' => $message,
            'is_read' => 0,
        ];

        if (!$this->insert($notificationData)) {
            throw new \Exception('Failed to insert notification into database.');
        }

        // Send the notification via email
        send_mail($company['email'], $subject, $message);

        return true;
    }
}
