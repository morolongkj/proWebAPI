<?php

namespace App\Models;

use CodeIgniter\Model;

class CategoryModel extends Model
{
    protected $table = 'categories'; // Name of the table
    protected $primaryKey = 'id'; // Primary key field name

    protected $useAutoIncrement = false; // Since 'id' is VARCHAR, no auto-increment

    protected $returnType = 'array'; // Return results as array
    protected $useSoftDeletes = false; // If you want to use soft deletes, set this to true

    protected $allowedFields = ['id', 'title', 'description', 'created_at', 'updated_at'];

    // Validation rules
    protected $validationRules = [
        'title' => 'required|min_length[3]|max_length[255]',
        'description' => 'permit_empty|string',
    ];

    protected $validationMessages = [
        'id' => [
            'required' => 'The Category ID is required.',
            'alpha_numeric' => 'The Category ID can only contain alphanumeric characters.',
            'min_length' => 'The Category ID must be at least 3 characters long.',
            'max_length' => 'The Category ID must not exceed 255 characters.',
            'is_unique' => 'The Category ID already exists in the database.',
        ],
        'title' => [
            'required' => 'The Title is required.',
            'min_length' => 'The Title must be at least 3 characters long.',
            'max_length' => 'The Title must not exceed 255 characters.',
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
     * Get a category by its title.
     *
     * @param string $title
     * @return array|null
     */
    public function getCategoryByTitle(string $title)
    {
        return $this->where('title', $title)->first();
    }
}
