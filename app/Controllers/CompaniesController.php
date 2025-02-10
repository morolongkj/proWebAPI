<?php

namespace App\Controllers;

use App\Models\PrequalifiedCompanyModel;
use CodeIgniter\RESTful\ResourceController;

class CompaniesController extends ResourceController
{
    protected $modelName = 'App\Models\CompanyModel'; // The model associated with this controller
    protected $format = 'json'; // The format to return data in
    protected $prequalifiedCompanyModel;

    public function __construct()
    {
        $this->prequalifiedCompanyModel = new PrequalifiedCompanyModel();
    }

    // Method to list all companies
    // public function index()
    // {
    //     $page = $this->request->getVar('page') ?? 1;
    //     $perPage = $this->request->getVar('perPage');
    //     if (!$perPage) {
    //         $perPage = null;
    //     }
    //     $company_name = $this->request->getVar('company_name');
    //     $year_established = $this->request->getVar('year_established');
    //     $user_id = $this->request->getVar('user_id');

    //     $where = [];
    //     if ($company_name) {
    //         $where['companies.company_name like'] = '%' . $company_name . '%';
    //     }
    //     if ($year_established) {
    //         $where['companies.year_established like'] = '%' . $year_established . '%';
    //     }
    //     if ($user_id) {
    //         $where['users.user_id like'] = '%' . $user_id . '%';
    //     }

    //     $this->model->select('companies.*, users.id as user_id, CONCAT(users.first_name,"",users.last_name) as contact_person')
    //         ->join('users', 'users.company_id = companies.id', 'left')
    //         ->where($where);
    //     $totalCompanies = $this->model->countAllResults(false); // false to avoid resetting query
    //     $companies = $this->model->paginate($perPage, 'companies', $page);

    //     $data = [
    //         'status' => true,
    //         'data' => [
    //             'companies ' => $companies,
    //             'total' => $totalCompanies,
    //         ],
    //     ];

    //     return $this->respond($data);

    // }
    public function index()
    {
        $page = $this->request->getVar('page') ?? 1;
        $perPage = $this->request->getVar('perPage') ?? 10;
        $company_name = $this->request->getVar('company_name');
        $year_established = $this->request->getVar('year_established');
        $user_id = $this->request->getVar('user_id');
        $sortBy = $this->request->getVar('sortBy') ?? 'company_name'; // Default sort field
        $sortOrder = $this->request->getVar('sortOrder') ?? 'asc'; // Default sort order

        $validSortFields = ['company_name', 'year_established'];
        if (!in_array($sortBy, $validSortFields)) {
            $sortBy = 'company_name'; // Fallback to default
        }

        $where = [];
        if ($company_name) {
            $where['companies.company_name like'] = '%' . $company_name . '%';
        }
        if ($year_established) {
            $where['companies.year_established like'] = '%' . $year_established . '%';
        }
        if ($user_id) {
            $where['users.user_id like'] = '%' . $user_id . '%';
        }

        // Fetch companies with sorting
        $this->model->select('companies.*')
            ->where($where)
            ->orderBy($sortBy, $sortOrder);
        $totalCompanies = $this->model->countAllResults(false); // False to avoid resetting query
        $companies = $this->model->paginate($perPage, 'companies', $page);

        // Fetch users for the listed companies with additional fields
        $companyIds = array_column($companies, 'id'); // Extract company IDs
        $users = $this->model->db->table('users')
            ->select('users.company_id, users.id as user_id, CONCAT(users.first_name, " ", users.last_name) as contact_person, users.username, users.phone_number')
            ->whereIn('users.company_id', $companyIds)
            ->get()
            ->getResultArray();

        // Group users by company ID
        $usersByCompany = [];
        foreach ($users as $user) {
            $usersByCompany[$user['company_id']][] = $user;
        }

        // Attach users to their respective companies
        foreach ($companies as &$company) {
            $company['users'] = $usersByCompany[$company['id']] ?? [];
            $company['products'] = $this->prequalifiedCompanyModel->getPrequalifiedProductsByCompanyId($company['id']);
        }

        $data = [
            'status' => true,
            'data' => [
                'companies' => $companies,
                'total' => $totalCompanies,
            ],
        ];

        return $this->respond($data);
    }

    // Method to retrieve a single company by ID
    public function show($id = null)
    {
        $company = $this->model->find($id); // Find the company by ID

        if (!$company) {
            return $this->failNotFound("Company with ID: $id not found."); // Respond with 404 if not found
        }

        return $this->respond($company); // Respond with the company data
    }

    // Method to create a new company
    // Method to create a new company
    public function create()
    {
        $user_id = auth()->id(); // Get the authenticated user's ID
        $data = $this->request->getJSON(true); // Parse JSON request data
        $data['user_id'] = $user_id; // Add user ID to the data array

        // Encode extra_data if it exists and is not empty
        if (!empty($data['extra_data'])) {
            $data['extra_data'] = json_encode($data['extra_data']);
        }

        // Call the saveCompany method to handle company creation
        $result = $this->model->saveCompany($data);

        if (!$result['status']) {
            // Handle errors
            if (isset($result['errors'])) {
                // Return validation errors if present
                return $this->failValidationErrors($result['errors']);
            }

            // Return general failure response with the error message
            return $this->fail($result['message']);
        }

        // Success response with company data
        return $this->respondCreated([
            'status' => true,
            'message' => 'Company created successfully.',
            'company' => $result['company'],
        ]);
    }

//     public function create()
//     {
//         $user_id = auth()->id();
//         $data = $this->request->getJSON(true);
//         $data['user_id'] = $user_id;
//         if (isset($data['extra_data']) && $data['extra_data']) {
//             $data['extra_data'] = json_encode($data['extra_data']);
//         }

//         $newCompany = $this->model->saveCompany($data);
// // Check if the company was saved successfully
//         if ($newCompany == false) {
//             // Respond with validation errors or a general error message
//             $errors = $this->model->errors();
//             if (!empty($errors)) {
//                 return $this->failValidationErrors($errors);
//             }

//             return $this->failServerError('Failed to create company.');
//         }

//         $response = [
//             "status" => true,
//             "message" => "Company created successfully",
//             "company" => $newCompany,
//         ];

//         return $this->respondCreated($response);

//     }

    /**
     * Update a specific company  (update)
     * @param string $id
     * @return JSON
     */
    public function update($id = null)
    {
        $user_id = auth()->id();
        $data = $this->request->getJSON(true);
        $existingCompany = $this->model->find($id);
        if ($existingCompany) {
            if ($this->model->update($id, $data)) {
                $updatedCompany = $this->model->find($id);
                return $this->respondUpdated($updatedCompany);
            }
        } else {
            return $this->failNotFound('Company notfound');
        }
        return $this->failServerError('Failed to update company .');
    }

    /**
     * Delete a specific company  (delete)
     * @param string $id
     * @return JSON
     */
    public function delete($id = null)
    {
        // Find the company
        $company = $this->model->find($id);
        if (!$company) {
            return $this->failNotFound("Company notfound with ID: $id");
        }
        // Delete company
        if ($this->model->delete($id)) {
            return $this->respondDeleted(['message' => 'Company deleted successfully']);
        }

        return $this->failServerError('Failed to delete company .');
    }
}
