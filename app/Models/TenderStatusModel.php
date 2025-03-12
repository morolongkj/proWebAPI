<?php
namespace App\Models;

use CodeIgniter\Model;

class TenderStatusModel extends Model
{
    protected $table            = 'tender_status';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = false; // Because 'id' is a VARCHAR, not INT
                                         // Specify the date format for the timestamps
    protected $dateFormat     = 'datetime';
    protected $useSoftDeletes = true; // Enable soft deletes
    protected $returnType     = 'array';
    protected $allowedFields  = ['id', 'status', 'description', 'created_at', 'updated_at'];

    // Enable automatic timestamps management for created_at and updated_at
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Optionally you can use soft deletes
    // protected $useSoftDeletes = true;
    // protected $deletedField  = 'deleted_at';

    // Set validation rules
    protected $validationRules = [
        'status'      => 'required|max_length[100]',
        'description' => 'permit_empty|string',
    ];

    // Set custom error messages
    protected $validationMessages = [
        'id'     => [
            'required'      => 'The ID is required.',
            'alpha_numeric' => 'The ID must contain only alphanumeric characters.',
            'max_length'    => 'The ID cannot exceed 255 characters.',
        ],
        'status' => [
            'required'   => 'The status is required.',
            'max_length' => 'The status cannot exceed 100 characters.',
        ],
    ];

    // Disable callbacks
    protected $skipValidation = false;
    protected $beforeInsert   = ['generateUuid'];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];

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
     * Get a status record by its name.
     *
     * @param string $name
     * @return array|null
     */
    public function getStatusByName(string $name)
    {
        return $this->where('status', $name)->first();
    }
}
