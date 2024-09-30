<?php

namespace App\Models;

use CodeIgniter\Model;

class TenderApprovalModel extends Model
{
    protected $table = 'tender_approvals';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = false;
    protected $returnType = 'array';
    protected $allowedFields = [
        'id',
        'approval_type',
        'tender_id',
        'user_id',
        'comment',
        'created_at',
        'updated_at',
    ];

    protected $useTimestamps = true; // Automatically manage created_at and updated_at

    // Optional: Define validation rules
    protected $validationRules = [
        'approval_type' => 'required|in_list[approved,rejected]',
        'tender_id' => 'required',
        'comment' => 'permit_empty|string',
    ];

    protected $validationMessages = [
        'id' => [
            'required' => 'An ID is required for approval.',
            'is_unique' => 'This ID already exists.',
        ],
        'approval_type' => [
            'required' => 'Approval type is required.',
            'in_list' => 'Approval type must be either approved or rejected.',
        ],
        'tender_id' => [
            'required' => 'Tender ID is required.',
            'exists' => 'This tender does not exist.',
        ],
        'user_id' => [
            'required' => 'User ID is required.',
            'exists' => 'This user does not exist.',
        ],
    ];
    protected $skipValidation = false;
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
