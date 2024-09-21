<?php

namespace App\Models;

use CodeIgniter\Model;

class QuestionnaireDocumentModel extends Model
{
    protected $table = 'questionnaire_documents';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = false;
    protected $allowedFields = ['id', 'questionnaire_id', 'file_path', 'file_name', 'file_type', 'file_size', 'created_at', 'updated_at'];
    protected $useTimestamps = true;

    protected $beforeInsert = ['generateUuid'];

    // Validation rules
    protected $validationRules = [
        'questionnaire_id' => 'required|max_length[255]',
        'file_name' => 'required|max_length[255]',
    ];

    protected $validationMessages = [
        'id' => [
            'required' => 'The document ID is required.',
            'max_length' => 'The document ID cannot exceed 255 characters.',
            'is_unique' => 'The document ID must be unique.',
        ],
        'questionnaire_id' => [
            'required' => 'The questionnaire ID is required.',
            'max_length' => 'The questionnaire ID cannot exceed 255 characters.',
        ],
        'file_path' => [
            'required' => 'The file path is required.',
            'max_length' => 'The file path cannot exceed 255 characters.',
        ],
        'file_name' => [
            'required' => 'The file name is required.',
            'max_length' => 'The file name cannot exceed 255 characters.',
        ],
        'file_type' => [
            'required' => 'The file type is required.',
            'max_length' => 'The file type cannot exceed 50 characters.',
        ],
        'file_size' => [
            'required' => 'The file size is required.',
            'is_natural' => 'The file size must be a positive number.',
        ],
    ];

    protected $skipValidation = false;

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
