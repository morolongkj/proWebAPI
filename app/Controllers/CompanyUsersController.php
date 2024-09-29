<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;

class CompanyUsersController extends ResourceController
{
    protected $modelName = 'App\Models\CompanyUserModel'; // The model associated with this controller
    protected $format = 'json'; // The format to return data in

    // Method to list all company-user relationships
    public function index()
    {
        $page = $this->request->getVar('page') ?? 1;
        $perPage = $this->request->getVar('perPage');
        if (!$perPage) {
            $perPage = null;
        }
        $company_id = $this->request->getVar('company_id');
        $user_id = $this->request->getVar('user_id');
        $where = [];
        if ($company_id) {
            $where['company_id like'] = '%' . $company_id . '%';
        }
        if ($user_id) {
            $where['user_id like'] = '%' . $user_id . '%';
        }
        $totalUsers = $this->model->selectCount('id')->where($where)->get()->getRowArray()['id'];
        $users = $this->model->where($where)->orderBy('created_at', 'DESC')->paginate($perPage, 'company_users ', $page);
        $data = [
            'status' => true,
            'data' => [
                'users ' => $users,
                'total' => $totalUsers,
            ],
        ];

        return $this->respond($data);

    }

    // Method to retrieve a single company-user relationship by ID
    public function show($id = null)
    {
        $companyUser = $this->model->find($id); // Find the relationship by ID

        if (!$companyUser) {
            return $this->failNotFound("Company-User relationship with ID: $id not found."); // Respond with 404 if not found
        }

        return $this->respond($companyUser); // Respond with the data
    }

    // Method to create a new company-user relationship
    public function create()
    {
        $data = $this->request->getJSON(true);
// Validate data before inserting
        if (!$this->validate($this->model->validationRules)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        if ($this->model->save($data)) {
            $newId = $this->model->getInsertID();
            // Fetch the newly created companyUser for the response
            $newCompanyUser = $this->model->find($newId);
            $response = [
                "status" => true,
                "message" => "Company-User relationship created successfully.",
                "companyUser" => $newCompanyUser,
            ];
            return $this->respondCreated($response);
        }

        return $this->failServerError('Failed to create the company-user relationship.');

    }

   /**
     * Update a specific companyUser (update)
     * @param string $id
     * @return JSON
     */
    public function update($id = null)
    {
        $data = $this->request->getJSON(true);
        $existingCompanyUser = $this->model->find($id);
        if ($existingCompanyUser) {
            if ($this->model->update($id, $data)) {
                $updatedCompanyUser = $this->model->find($id);
                return $this->respondUpdated($updatedCompanyUser);
            }
        } else {
            return $this->failNotFound('Company User not found');
        }
        return $this->failServerError('Failed to update companyUser.');
    }

    /**
     * Delete a specific companyUser (delete)
     * @param string $id
     * @return JSON
     */
    public function delete($id = null)
    {
        // Find the companyUser
        $companyUser = $this->model->find($id);
        if (!$companyUser) {
            return $this->failNotFound("CompanyUser not found with ID: $id");
        }
        // Delete companyUser
        if ($this->model->delete($id)) {
            return $this->respondDeleted(['message' => 'CompanyUser deleted successfully']);
        }

        return $this->failServerError('Failed to delete companyUser.');
    }
    
    // Method to get users by company ID
    public function usersByCompany($companyId = null)
    {
        if (!$companyId) {
            return $this->failValidationErrors('The company ID is required.');
        }

        $users = $this->model->getUsersByCompanyId($companyId);

        if (empty($users)) {
            return $this->failNotFound("No users found for Company ID: $companyId");
        }

        return $this->respond($users);
    }

    // Method to get companies by user ID
    public function companiesByUser($userId = null)
    {
        if (!$userId) {
            return $this->failValidationErrors('The user ID is required.');
        }

        $companies = $this->model->getCompaniesByUserId($userId);

        if (empty($companies)) {
            return $this->failNotFound("No companies found for User ID: $userId");
        }

        return $this->respond($companies);
    }
}
