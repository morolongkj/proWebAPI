<?php

namespace App\Models;

use CodeIgniter\Model;

class PrequalificationStageModel extends Model
{
    protected $table = 'prequalification_stages'; // Table name
    protected $primaryKey = 'id'; // Primary key of the table
     protected $useAutoIncrement = false;

    // Allowed fields for insert and update
    protected $allowedFields = ['id', 'title', 'description', 'created_at', 'updated_at'];

    // Automatically manage created_at and updated_at fields
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $beforeInsert = ['generateUuid'];

    // Validation rules for insert/update
    protected $validationRules = [
        'title' => 'required|max_length[255]|is_unique[prequalification_stages.title]',
        'description' => 'permit_empty',
    ];

    // Validation error messages
    protected $validationMessages = [
        'id' => [
            'required' => 'The ID field is required.',
            'alpha_dash' => 'The ID can only contain alphanumeric characters, underscores, and dashes.',
            'max_length' => 'The ID cannot exceed 255 characters.',
            'is_unique' => 'The ID must be unique.',
        ],
        'title' => [
            'required' => 'The title field is required.',
            'max_length' => 'The title cannot exceed 255 characters.',
            'is_unique' => 'The title must be unique.',
        ],
    ];

    // Enable soft deletes if needed (optional)
    // protected $useSoftDeletes = true;
    // protected $deletedField = 'deleted_at';

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
