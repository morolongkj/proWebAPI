<?php

namespace App\Models;

use CodeIgniter\Model;

class CompanyUserModel extends Model
{
    protected $table = 'company_users'; // The table name
    protected $primaryKey = 'id'; // The primary key of the table

    protected $useAutoIncrement = false; // Since 'id' is VARCHAR, no auto-increment

    protected $returnType = 'array'; // Return results as array
  // Specify the date format for the timestamps
    protected $dateFormat = 'datetime';
    protected $useSoftDeletes = true; // Enable soft deletes

    // The fields that can be inserted or updated
    protected $allowedFields = [
        'id',
        'company_id',
        'user_id',
        'created_at',
        'updated_at',
    ];

    // Automatically handle timestamps
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation rules
    protected $validationRules = [
        'company_id' => 'required|max_length[255]',
        'user_id' => 'required|integer|is_not_unique[users.id]',
    ];

    // Validation messages (optional)
    protected $validationMessages = [
        'company_id' => [
            'required' => 'The company ID is required',
            'max_length' => 'The company ID cannot exceed 255 characters',
        ],
        'user_id' => [
            'required' => 'The user ID is required',
            'integer' => 'The user ID must be an integer',
            'is_not_unique' => 'The user ID does not exist in the users table',
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

    // Function to get users by company ID
    public function getUsersByCompanyId($companyId)
    {
        return $this->where('company_id', $companyId)
            ->join('users', 'users.id = company_users.user_id')
            ->findAll();
    }

    // Function to get companies by user ID
    public function getCompaniesByUserId($userId)
    {
        return $this->where('user_id', $userId)
            ->join('companies', 'companies.id = company_users.company_id')
            ->findAll();
    }
}
