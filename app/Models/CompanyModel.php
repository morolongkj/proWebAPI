<?php
namespace App\Models;

use CodeIgniter\Model;

class CompanyModel extends Model
{
    protected $table      = 'companies'; // The table name
    protected $primaryKey = 'id';        // The primary key of the table

    protected $useAutoIncrement = false; // Since 'id' is VARCHAR, no auto-increment

    protected $returnType     = 'array'; // Return results as array
    protected $useSoftDeletes = true;    // If you want to use soft deletes, set this to true

    // The fields that can be inserted or updated
    protected $allowedFields = [
        'id',
        'company_name',
        'year_established',
        'company_form',
        'specify_company_form',
        'legal_status',
        'trade_registration_number',
        'vat_number',
        'address',
        'country',
        'telephone',
        'email',
        'website',
        'telefax',
        'telex',
        'extra_data',
        'created_at',
        'updated_at',
    ];

    // Automatically handle timestamps
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation rules
    protected $validationRules = [
        'company_name'              => 'required|min_length[3]|max_length[100]',
        'year_established'          => 'required|min_length[4]|max_length[4]',
        'trade_registration_number' => 'required|max_length[100]',
        'vat_number'                => 'required|max_length[100]',
        'address'                   => 'required|max_length[100]',
        'country'                   => 'required|max_length[100]',
        'telephone'                 => 'required|max_length[100]',
        'email'                     => 'required|valid_email|max_length[100]',
    ];

    // Validation messages (optional)
    protected $validationMessages = [
        'company_name' => [
            'required'   => 'The company name is required',
            'min_length' => 'The company name must be at least 3 characters long',
            'max_length' => 'The company name cannot exceed 100 characters',
        ],
        'email'        => [
            'required'    => 'The email is required',
            'valid_email' => 'Please provide a valid email address',
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

    // Method to create a company along with a user within a transaction
    public function saveCompany(array $data)
    {
        $db = \Config\Database::connect();
        $db->transStart(); // Start the transaction

        // Validate data before saving
        if (! $this->validate($data)) {
            // Return validation errors as a structured response
            return [
                'status' => false,
                'errors' => $this->errors(),
            ];
        }

        // Attempt to save company data
        if (! $this->save($data)) {
            $db->transRollback(); // Rollback transaction on failure
            return [
                'status'  => false,
                'message' => 'Failed to save company data.',
            ];
        }

        $newCompanyId = $this->getInsertID();

        // Prepare the user data for the related user update
        $userData = [
            'company_id' => $newCompanyId,
        ];

        $userModel = new \App\Models\UserModel();
        if (! $userModel->update($data['user_id'], $userData)) {
            $db->transRollback(); // Rollback transaction on failure
            return [
                'status'  => false,
                'message' => 'Failed to update user with company ID.',
            ];
        }

        // Commit the transaction if everything is successful
        $db->transComplete();

        // Check if the transaction was successful
        if ($db->transStatus() === false) {
            return [
                'status'  => false,
                'message' => 'Transaction failed. Company creation was rolled back.',
            ];
        }

        // Fetch and return the newly created company data
        $newCompany = $this->find($newCompanyId);
        if (! $newCompany) {
            return [
                'status'  => false,
                'message' => 'Failed to fetch newly created company.',
            ];
        }

        return [
            'status'  => true,
            'message' => 'Company created successfully.',
            'company' => $newCompany,
        ];
    }

    /**
     * Fetch a single company record by its ID.
     *
     * @param string $id The ID of the company to retrieve.
     * @return array|null Returns the company record as an associative array or null if not found.
     */
    public function findById(string $id): ?array
    {
        // Fetch the company record by ID
        return $this->where('id', $id)->first();
    }

//     public function saveCompany(array $data)
//     {
//         $db = \Config\Database::connect();
//         $db->transStart(); // Start the transaction

// // Validate data before saving
//         if (!$this->validate($data)) {
//             return $this->errors();
//         }

//         if (!$this->save($data)) {
//             $db->transRollback(); // Rollback transaction on failure
//             return false;
//         }

//         $newCompanyId = $this->getInsertID();

// // Prepare the user data
//         $userData = [
//             'company_id' => $newCompanyId,
//         ];

//         $userModel = new \App\Models\UserModel();
//         if (!$userModel->update($data['user_id'], $userData)) {
//             $db->transRollback(); // Rollback transaction on failure
//             return false;
//         }

// // Commit the transaction if everything is successful
//         $db->transComplete();

// // Check if the transaction was successful
//         if ($db->transStatus() === false) {
//             return [];
//         }

// // Return the newly created tender data
//         return $this->find($newCompanyId);

//     }
}
