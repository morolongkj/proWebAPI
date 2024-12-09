<?php

namespace App\Models;

use CodeIgniter\Model;

class StatusModel extends Model
{
    protected $table = 'status'; // Table name
    protected $primaryKey = 'id'; // Primary key
    protected $useAutoIncrement = false; // ID is not auto-incremented
    protected $allowedFields = [
        'id',
        'title',
        'description',
        'created_at',
        'updated_at',
    ]; // Fields allowed for mass assignment

    // Automatic handling of timestamps
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    // Validation rules
    protected $validationRules = [
        'title' => 'required|string|max_length[100]',
        'description' => 'permit_empty|string',
    ];

    // Validation messages
    protected $validationMessages = [
        'title' => [
            'required' => 'The Title field is required.',
            'string' => 'The Title must be a valid string.',
            'max_length' => 'The Title must not exceed 100 characters.',
        ],
        'description' => [
            'string' => 'The Description must be a valid text.',
        ],
    ];

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
     * Fetch the ID of a status by its title.
     *
     * @param string $title
     * @return string|null Returns the ID if found, otherwise null.
     */
    public function getStatusIdByTitle(string $title): ?string
    {
        $status = $this->where('title', $title)->first();
        return $status['id'] ?? null; // Return the ID or null if not found
    }
}
