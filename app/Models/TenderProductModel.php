<?php

namespace App\Models;

use CodeIgniter\Model;

class TenderProductModel extends Model
{
    protected $table = 'tender_products'; // Name of the table
    protected $primaryKey = 'id'; // Primary key of the table

    // Specify that the primary key is not auto-incrementing (since it's a VARCHAR)
    protected $useAutoIncrement = false;

    // Return type of the results (you can use 'object' if you prefer)
    protected $returnType = 'array';

    // Enable automatic timestamps for created_at and updated_at fields
    protected $useTimestamps = true;

    // Specify the date format for the timestamps
    protected $dateFormat = 'datetime';

    // List of fields that are allowed to be set during insert/update
    protected $allowedFields = [
        'id',
        'tender_id',
        'product_id',
        'quantity',
        'created_at',
        'updated_at',
    ];

    // Validation rules for the fields
    protected $validationRules = [
        'tender_id' => 'required|max_length[255]',
        'product_id' => 'required|max_length[255]',
        'quantity' => 'required|integer|greater_than_equal_to[1]',
    ];

    // Validation messages for custom error messages
    protected $validationMessages = [
        'tender_id' => [
            'required' => 'Tender ID is required',
            'alpha_numeric_space' => 'Tender ID can only contain alphanumeric characters and spaces',
            'max_length' => 'Tender ID cannot exceed 255 characters',
        ],
        'product_id' => [
            'required' => 'Product ID is required',
            'alpha_numeric_space' => 'Product ID can only contain alphanumeric characters and spaces',
            'max_length' => 'Product ID cannot exceed 255 characters',
        ],
        'quantity' => [
            'required' => 'Quantity is required',
            'integer' => 'Quantity must be an integer',
            'greater_than_equal_to' => 'Quantity must be at least 1',
        ],
    ];

    // Disables validation rules if set to true
    protected $skipValidation = false;

    // If you want to implement custom methods, you can do so below
    public function getProductsByTenderId($tenderId)
    {
        return $this->where('tender_id', $tenderId)->findAll();
    }

    public function getTendersByProductId($productId)
    {
        return $this->where('product_id', $productId)->findAll();
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

    protected $beforeInsert = ['generateUuid'];

    public function findWithDetails(string $id)
    {
        return $this
            ->select('tender_products.*, products.title, products.code, products.description') // Select columns you need
            ->join('products', 'products.id = tender_products.product_id')
            ->where('tender_products.id', $id)
            ->first();
    }

    /**
     * Get a list of products for a specific tender ID, including product details.
     *
     * @param string $tenderId
     * @return array|null
     */
    public function getProductsByTenderIdWithDetails(string $tenderId)
    {
        return $this
            ->select('tender_products.*, products.title, products.code, products.description') // Columns to select
            ->join('products', 'products.id = tender_products.product_id') // Join with products table
            ->where('tender_products.tender_id', $tenderId) // Filter by tender ID
            ->findAll();
    }

}
