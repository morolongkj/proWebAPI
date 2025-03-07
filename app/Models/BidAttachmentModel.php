<?php

namespace App\Models;

use CodeIgniter\Model;

class BidAttachmentModel extends Model
{
    protected $table = 'bid_attachments';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = false; // Using UUIDs
    protected $returnType = 'array';
    protected $allowedFields = [
        'id',
        'bid_id',
        'file_name',
        'file_path',
        'file_type',
        'file_size',
        'attachment_name',
        'created_at',
        'updated_at',
    ];

    // Validation Rules
    protected $validationRules = [

    ];

    protected $validationMessages = [

    ];

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
     * Retrieve a single attachment record by its ID.
     *
     * @param string $id
     * @return array|null
     */
    public function getRecordById(string $id): ?array
    {
        return $this->where('id', $id)->first();
    }

    /**
     * Retrieve a list of attachments for a specific submission ID.
     *
     * @param string $bidId
     * @return array
     */
    public function getRecordsByBidId(string $bidId): array
    {
        return $this->where('bid_id', $bidId)
            ->orderBy('created_at', 'ASC') // Optional: order by creation time
            ->findAll();
    }
}
