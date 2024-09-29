<?php

namespace App\Models;

use CodeIgniter\Model;

class PrequalifiedCompanyModel extends Model
{
    protected $table = 'prequalified_companies'; // The table name
    protected $primaryKey = 'id'; // The primary key of the table

    protected $useAutoIncrement = false; // Since 'id' is VARCHAR, no auto-increment

    protected $returnType = 'array'; // Return results as array
    protected $useSoftDeletes = false; // If you want to use soft deletes, set this to true

    // The fields that can be inserted or updated
    protected $allowedFields = [
        'id',
        'company_id',
        'product_id',
        'created_at',
        'updated_at',
    ];

    // Automatically handle timestamps
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    // Validation rules
    protected $validationRules = [
        'company_id' => 'required|max_length[255]|is_not_unique[companies.id]',
        'product_id' => 'required|max_length[255]|is_not_unique[products.id]',
    ];

    // Validation messages (optional)
    protected $validationMessages = [
        'company_id' => [
            'required' => 'The company ID is required',
            'max_length' => 'The company ID cannot exceed 255 characters',
            'is_not_unique' => 'The company ID does not exist in the companies table',
        ],
        'product_id' => [
            'required' => 'The product ID is required',
            'max_length' => 'The product ID cannot exceed 255 characters',
            'is_not_unique' => 'The product ID does not exist in the products table',
        ],
    ];

    // Skipping validation if this flag is true
    protected $skipValidation = false;

    // To use UUIDs as primary keys
    protected $beforeInsert = ['generateUUID'];
    protected $beforeUpdate = [];

    // Method to generate UUID
    protected function generateUUID(array $data)
    {
        if (empty($data['data']['id'])) {
            $data['data']['id'] = uuid_v4(); // Generates a 32-character unique string
        }
        return $data;
    }

    // Function to get all prequalified companies for a specific product ID
    public function getPrequalifiedCompaniesByProductId($productId)
    {
        return $this->where('product_id', $productId)
            ->join('companies', 'companies.id = prequalified_companies.company_id')
            ->findAll();
    }

    // Function to get all prequalified products for a specific company ID
    public function getPrequalifiedProductsByCompanyId($companyId)
    {
        return $this->where('company_id', $companyId)
            ->join('products', 'products.id = prequalified_companies.product_id')
            ->findAll();
    }

    // Function to check if a company is prequalified for a specific product
    public function isCompanyPrequalified($companyId, $productId)
    {
        return $this->where([
            'company_id' => $companyId,
            'product_id' => $productId,
        ])
            ->countAllResults() > 0;
    }
}
