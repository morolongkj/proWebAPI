<?php

namespace App\Models;

use CodeIgniter\Model;

class QuestionnaireSubmissionAttachmentModel extends Model
{
    protected $table = 'questionnaire_submission_attachments'; // The table name
    protected $primaryKey = 'id'; // The primary key of the table

    protected $useAutoIncrement = false; // Since 'id' is VARCHAR, no auto-increment

    protected $returnType = 'array'; // Return results as array
    protected $useSoftDeletes = false; // No soft deletes for this table

    protected $allowedFields = [
        'id',
        'submission_id',
        'file_name',
        'file_path',
        'file_type',
        'file_size',
        'attachment_name',
        'created_at',
        'updated_at',
    ];

    protected $validationRules = [
        'submission_id' => 'required|max_length[255]',
        'file_name' => 'required|max_length[255]',
        'file_path' => 'required',
        'file_type' => 'required|max_length[100]',
        'file_size' => 'permit_empty|numeric',
    ];

    protected $validationMessages = [
        'submission_id' => [
            'required' => 'The submission ID is required.',
            'max_length' => 'The submission ID cannot exceed 255 characters.',
        ],
        'file_name' => [
            'required' => 'The file name is required.',
            'max_length' => 'The file name cannot exceed 255 characters.',
        ],
        'file_path' => [
            'required' => 'The file path is required.',
        ],
        'file_type' => [
            'required' => 'The file type is required.',
            'max_length' => 'The file type cannot exceed 100 characters.',
        ],
        'file_size' => [
            'numeric' => 'The file size must be a numeric value.',
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
}
