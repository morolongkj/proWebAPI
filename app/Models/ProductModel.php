<?php

namespace App\Models;

use CodeIgniter\Model;

class ProductModel extends Model
{
    protected $table = 'products'; // Name of the table
    protected $primaryKey = 'id'; // Primary key field name

    protected $useAutoIncrement = false; // Since 'id' is VARCHAR, no auto-increment

    protected $returnType = 'array'; // Return results as array
    protected $useSoftDeletes = false; // If you want to use soft deletes, set this to true

    protected $allowedFields = [
        'id', 'code', 'title', 'description', 'category_id', 'extra_data', 'created_at', 'updated_at',
    ];

    // Validation rules
    protected $validationRules = [
        'code' => 'required|min_length[2]|max_length[100]',
        'title' => 'required|min_length[3]|max_length[255]',
        'description' => 'permit_empty|string',
        'category_id' => 'permit_empty|max_length[255]|is_not_unique[categories.id]',
    ];

    protected $validationMessages = [
        'id' => [
            'required' => 'The Product ID is required.',
            'alpha_numeric' => 'The Product ID can only contain alphanumeric characters.',
            'min_length' => 'The Product ID must be at least 3 characters long.',
            'max_length' => 'The Product ID must not exceed 255 characters.',
            'is_unique' => 'The Product ID already exists in the database.',
        ],
        'code' => [
            'required' => 'The Product Code is required.',
            'min_length' => 'The Product Code must be at least 2 characters long.',
            'max_length' => 'The Product Code must not exceed 100 characters.',
            'is_unique' => 'The Product Code already exists in the database.',
        ],
        'title' => [
            'required' => 'The Product Title is required.',
            'min_length' => 'The Product Title must be at least 3 characters long.',
            'max_length' => 'The Product Title must not exceed 255 characters.',
        ],
        'category_id' => [
            'alpha_numeric' => 'The Category ID can only contain alphanumeric characters.',
            'max_length' => 'The Category ID must not exceed 255 characters.',
            'is_not_unique' => 'The Category ID must exist in the categories table.',
        ],
    ];

    protected $skipValidation = false; // Whether to skip validation or not

    // Date handling
    protected $useTimestamps = true; // Enable automatic timestamps
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
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
 * Fetch a single product record by its ID.
 *
 * @param string|null $id The ID of the product to retrieve. Returns an empty array if ID is null.
 * @return array|null Returns the product record as an associative array or an empty array if ID is null.
 */
    public function findById(?string $id = null): array
    {
        // Return an empty array if the ID is null
        if ($id === null) {
            return [];
        }

        // Fetch the product record by ID
        return $this->where('id', $id)->first() ?? [];
    }

}
