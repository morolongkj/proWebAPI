<?php
namespace App\Models;

use CodeIgniter\Model;

class BidStatusHistoryModel extends Model
{
    protected $table            = 'bid_status_history';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = false;   // Using UUIDs
    protected $returnType       = 'array'; // Specify the date format for the timestamps
    protected $dateFormat       = 'datetime';
    protected $useSoftDeletes   = true; // Enable soft deletes

    protected $allowedFields = [
        'id',
        'bid_id',
        'status_id',
        'changed_by',
        'change_date',
        'remarks',
        'created_at',
        'updated_at',
    ];

    // Validation Rules
    protected $validationRules = [
        'bid_id'     => 'required|max_length[255]',
        'status_id'  => 'required|max_length[255]',
        'changed_by' => 'required|integer',
        'remarks'    => 'permit_empty|string',
    ];

    protected $validationMessages = [
        'bid_id'     => [
            'required'   => 'The bid ID is required.',
            'max_length' => 'The bid ID cannot exceed 255 characters.',
        ],
        'status_id'  => [
            'required'   => 'The status is required.',
            'max_length' => 'The status cannot exceed 255 characters.',
        ],
        'changed_by' => [
            'required'   => 'The changed by field is required.',
            'max_length' => 'The changed by field cannot exceed 255 characters.',
        ],
        'remarks'    => [
            'max_length' => 'The remarks cannot exceed 500 characters.',
        ],
    ];

    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';

    // UUID Generator Before Insert
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
            "bid_status_history.*,
                 CONCAT(users.first_name,' ',users.last_name) as changed_by_name,
                 status.title as status"
        )
            ->join('users', 'users.id = bid_status_history.changed_by', 'left')
            ->join('status', 'status.id = bid_status_history.status_id', 'left')
            ->where('bid_status_history.id', $id)
            ->first();
    }

    /**
     * Retrieve a list of status history records by bid ID, resolving relationships.
     *
     * @param string $bidId
     * @return array
     */
    public function getRecordsByBidId(string $bidId): array
    {
        return $this->select(
            "bid_status_history.*,
                 CONCAT(users.first_name,' ',users.last_name) as changed_by_name,
                 status.title as status"
        )
            ->join('users', 'users.id = bid_status_history.changed_by', 'left')
            ->join('status', 'status.id = bid_status_history.status_id', 'left')
            ->where('bid_status_history.bid_id', $bidId)
            ->orderBy('change_date', 'DESC') // Optional: Order by change date
            ->findAll();
    }
}
