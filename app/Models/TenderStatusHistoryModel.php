<?php

namespace App\Models;

use CodeIgniter\Model;

class TenderStatusHistoryModel extends Model
{
    protected $table = 'tender_status_history';
    protected $primaryKey = 'id';

    protected $useAutoIncrement = false; // Because we are using VARCHAR as primary key
    protected $returnType = 'array'; // You can also use 'object' if you prefer

    protected $allowedFields = [
        'id',
        'tender_id',
        'status_id',
        'changed_by',
        'change_date',
        'remarks',
        'created_at',
        'updated_at',
    ];

    protected $useTimestamps = true; // Automatically manage created_at and updated_at
    protected $dateFormat = 'datetime'; // Date format for timestamps

    // Add validation rules if needed
    protected $validationRules = [
        'tender_id' => 'required',
        'status_id' => 'required',
        'changed_by' => 'required|integer',
        'remarks' => 'permit_empty|string',
    ];

    protected $validationMessages = [
        'tender_id' => [
            'required' => 'Tender ID is required',
        ],
        'status_id' => [
            'required' => 'Status ID is required',
        ],
        'changed_by' => [
            'required' => 'Changed by is required',
            'integer' => 'Changed by must be an integer',
        ],
        'change_date' => [
            'required' => 'Change date is required',
            'valid_date' => 'Change date must be a valid date',
        ],
    ];

    protected $skipValidation = false;
    protected $beforeInsert = ['generateUuid'];

    // Method to add history and update current tender status
    public function addHistory($tenderId, $statusId, $changedBy, $remarks = null)
    {
        // Check if a record already exists for the given tender_id and status_id
        // $existingHistory = $this->where('tender_id', $tenderId)
        //     ->where('status_id', $statusId)
        //     ->first();

        // If no existing history, proceed to add new record
        // if (!$existingHistory) {
            // Prepare data for new history entry
            $data = [
                'tender_id' => $tenderId,
                'status_id' => $statusId,
                'changed_by' => $changedBy,
                'change_date' => date('Y-m-d H:i:s'), // Current timestamp
                'remarks' => $remarks,
            ];

            // Insert new history record
            $this->insert($data);

            // Update the current status of the tender in the tenders table
            $tenderModel = new TenderModel(); // Make sure you have a TenderModel
            $tenderModel->update($tenderId, ['current_status' => $statusId]);

            return true; // Indicate success
        // }

        // return false; // Indicate that the history record already exists
    }

    // If you want to implement custom methods, you can do so below
    public function getHistoryByTenderId($tenderId)
    {
        return $this->where('tender_id', $tenderId)->findAll();
    }

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