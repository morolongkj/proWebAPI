<?php

namespace App\Models;

use CodeIgniter\Model;

class DocumentModel extends Model
{
    protected $table = 'documents'; // Name of the table
    protected $primaryKey = 'id'; // Primary key field name

    protected $useAutoIncrement = false; // 'id' is VARCHAR, no auto-increment

    protected $returnType = 'array'; // Return results as array
    protected $useSoftDeletes = false; // If you want to use soft deletes, set this to true

    protected $allowedFields = [
        'id', 'title', 'description', 'created_at', 'updated_at',
    ];

    // Validation rules
    protected $validationRules = [
        'title' => 'required|min_length[3]|max_length[255]',
        'description' => 'permit_empty|string',
    ];

    protected $validationMessages = [
        'id' => [
            'required' => 'The Document ID is required.',
            'alpha_numeric' => 'The Document ID can only contain alphanumeric characters.',
            'min_length' => 'The Document ID must be at least 3 characters long.',
            'max_length' => 'The Document ID must not exceed 255 characters.',
            'is_unique' => 'The Document ID already exists in the database.',
        ],
        'title' => [
            'required' => 'The Document Title is required.',
            'min_length' => 'The Document Title must be at least 3 characters long.',
            'max_length' => 'The Document Title must not exceed 255 characters.',
        ],
        'description' => [
            'string' => 'The Document Description must be a valid string.',
        ],
    ];

    protected $skipValidation = false; // Whether to skip validation or not

    // Date handling
    protected $useTimestamps = true; // Enable automatic timestamps
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
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
}
