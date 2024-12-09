<?php

namespace App\Models;

use CodeIgniter\Model;

class QuestionnaireSubmissionStatusHistoryModel extends Model
{
    protected $table = 'questionnaire_submission_status_history'; // Table name
    protected $primaryKey = 'id'; // Primary key
    protected $useAutoIncrement = false; // ID is not auto-incremented
    protected $allowedFields = [
        'id',
        'submission_id',
        'status_id',
        'changed_by',
        'change_date',
        'remarks',
        'created_at',
        'updated_at',
    ]; // Fields allowed for mass assignment

    // Automatic handling of timestamps
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    // Validation rules
    protected $validationRules = [
        'submission_id' => 'required|max_length[255]',
        'status_id' => 'required|max_length[255]',
        'changed_by' => 'required|integer',
        'remarks' => 'permit_empty|string',
    ];

    // Validation messages
    protected $validationMessages = [
        'submission_id' => [
            'required' => 'The Submission ID is required.',
            'max_length' => 'The Submission ID must not exceed 255 characters.',
        ],
        'status_id' => [
            'required' => 'The Status ID is required.',
            'max_length' => 'The Status ID must not exceed 255 characters.',
        ],
        'changed_by' => [
            'required' => 'The Changed By field is required.',
            'integer' => 'The Changed By field must be an integer.',
        ],
        'remarks' => [
            'string' => 'Remarks must be a valid text.',
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
     * Retrieve a status history record by its ID, resolving relationships.
     *
     * @param string $id
     * @return array|null
     */
    public function getRecordById(string $id): ?array
    {
        return $this->select(
            "questionnaire_submission_status_history.*,
                 CONCAT(users.first_name,' ',users.last_name) as changed_by_name,
                 status.title as status"
        )
            ->join('users', 'users.id = questionnaire_submission_status_history.changed_by', 'left')
            ->join('status', 'status.id = questionnaire_submission_status_history.status_id', 'left')
            ->where('questionnaire_submission_status_history.id', $id)
            ->first();
    }

    /**
     * Retrieve a list of status history records by submission ID, resolving relationships.
     *
     * @param string $submissionId
     * @return array
     */
    public function getRecordsBySubmissionId(string $submissionId): array
    {
        return $this->select(
            "questionnaire_submission_status_history.*,
                 CONCAT(users.first_name,' ',users.last_name) as changed_by_name,
                 status.title as status"
        )
            ->join('users', 'users.id = questionnaire_submission_status_history.changed_by', 'left')
            ->join('status', 'status.id = questionnaire_submission_status_history.status_id', 'left')
            ->where('questionnaire_submission_status_history.submission_id', $submissionId)
            ->orderBy('change_date', 'DESC') // Optional: Order by change date
            ->findAll();
    }

}
