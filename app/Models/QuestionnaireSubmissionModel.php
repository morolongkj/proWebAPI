<?php

namespace App\Models;

use CodeIgniter\Model;

class QuestionnaireSubmissionModel extends Model
{
    protected $table = 'questionnaire_submissions'; // The table name
    protected $primaryKey = 'id'; // The primary key of the table

    protected $useAutoIncrement = false; // Since 'id' is VARCHAR, no auto-increment

    protected $returnType = 'array'; // Return results as array
    protected $useSoftDeletes = false; // No soft deletes for this table

    protected $allowedFields = [
        'id',
        'questionnaire_id',
        'company_id',
        'message',
        'current_status_id',
        'product_id',
        'extra_data',
        'created_at',
        'updated_at',
    ];

    protected $validationRules = [
        'questionnaire_id' => 'required|max_length[255]',
        'company_id' => 'required|max_length[255]',
        'current_status_id' => 'permit_empty|max_length[255]',
        'extra_data' => 'permit_empty|valid_json',
    ];

    protected $validationMessages = [
        'questionnaire_id' => [
            'required' => 'The questionnaire ID is required.',
            'max_length' => 'The questionnaire ID cannot exceed 255 characters.',
        ],
        'company_id' => [
            'required' => 'The company ID is required.',
            'max_length' => 'The company ID cannot exceed 255 characters.',
        ],
        'current_status_id' => [
            'max_length' => 'The current status ID cannot exceed 255 characters.',
        ],
        'extra_data' => [
            'valid_json' => 'The extra data must be a valid JSON string.',
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
