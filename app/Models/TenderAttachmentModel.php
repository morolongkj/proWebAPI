<?php

namespace App\Models;

use CodeIgniter\Model;

class TenderAttachmentModel extends Model
{
    protected $table = 'tender_attachments'; // The name of the table
    protected $primaryKey = 'id'; // Primary key field
    protected $useAutoIncrement = false; // 'id' is a VARCHAR, so auto increment is set to false
    protected $returnType = 'array'; // Return type of results (array or object)
    protected $allowedFields = [
        'id',
        'tender_id',
        'file_name',
        'file_path',
        'file_type',
        'created_at',
        'updated_at',
    ]; // List of fields that can be inserted or updated

    // Enable automatic timestamps management for created_at and updated_at
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    // Optionally you can use soft deletes
    // protected $useSoftDeletes = true;
    // protected $deletedField  = 'deleted_at';

    // Set validation rules
    protected $validationRules = [
        'tender_id' => 'permit_empty|alpha_numeric|max_length[255]',
        'file_name' => 'required|max_length[255]',
        'file_path' => 'required|max_length[255]',
        'file_type' => 'required|max_length[50]',
    ];

    // Set custom error messages
    protected $validationMessages = [
        'id' => [
            'required' => 'The ID is required.',
            'alpha_numeric' => 'The ID must contain only alphanumeric characters.',
            'max_length' => 'The ID cannot exceed 255 characters.',
        ],
        'tender_id' => [
            'alpha_numeric' => 'The Tender ID must contain only alphanumeric characters.',
            'max_length' => 'The Tender ID cannot exceed 255 characters.',
        ],
        'file_name' => [
            'required' => 'The file name is required.',
            'max_length' => 'The file name cannot exceed 255 characters.',
        ],
        'file_path' => [
            'required' => 'The file path is required.',
            'max_length' => 'The file path cannot exceed 255 characters.',
        ],
        'file_type' => [
            'required' => 'The file type is required.',
            'max_length' => 'The file type cannot exceed 50 characters.',
        ],
    ];

    // Disable callbacks
    protected $skipValidation = false;
    protected $beforeInsert = ['generateUuid'];
    protected $afterInsert = [];
    protected $beforeUpdate = [];
    protected $afterUpdate = [];
    protected $beforeDelete = [];
    protected $afterDelete = [];

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
