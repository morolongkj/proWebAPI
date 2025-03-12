<?php
namespace App\Models;

use CodeIgniter\Model;

class BidModel extends Model
{
    protected $table            = 'bids';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = false; // Using UUIDs
    protected $useSoftDeletes   = true;
    protected $returnType       = 'array';
    protected $allowedFields    = ['id', 'tender_id', 'submission_date', 'current_status_id', 'company_id', 'created_at', 'updated_at'];

    // Validation Rules
    protected $validationRules = [
        'tender_id'  => 'required',
        'company_id' => 'required',
    ];

    protected $validationMessages = [
        'tender_id'  => [
            'required' => 'The tender ID is required.',
        ],
        'company_id' => [
            'required' => 'The company ID is required.',
        ],
    ];

    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';

    // UUID Generator Before Insert
    protected $beforeInsert = ['generateUuid', 'setSubmissionDate'];

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

    // Method to set submission_date before insert
    protected function setSubmissionDate(array $data)
    {
        if (empty($data['data']['submission_date'])) {
            $data['data']['submission_date'] = date('Y-m-d H:i:s'); // Set current date and time
        }
        return $data;
    }
}
