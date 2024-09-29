<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;

class CompaniesController extends ResourceController
{
    protected $modelName = 'App\Models\CompanyModel'; // The model associated with this controller
    protected $format = 'json'; // The format to return data in

    // Method to list all companies
    public function index()
    {
        $page = $this->request->getVar('page') ?? 1;
        $perPage = $this->request->getVar('perPage');
        if (!$perPage) {
            $perPage = null;
        }
        $company_name = $this->request->getVar('company_name');
        $year_established = $this->request->getVar('year_established');
        $where = [];
        if ($company_name) {
            $where['company_name like'] = '%' . $company_name . '%';
        }
        if ($year_established) {
            $where['year_established like'] = '%' . $year_established . '%';
        }
        $totalCompanies = $this->model->selectCount('id')->where($where)->get()->getRowArray()['id'];
        $companies = $this->model->where($where)->orderBy('created_at', 'DESC')->paginate($perPage, 'companies ', $page);
        $data = [
            'status' => true,
            'data' => [
                'companies ' => $companies,
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
    public function create()
    {
        $user_id = auth()->id();
        $data = $this->request->getJSON(true);
        $data['user_id'] = $user_id;

// Save the company and status history
        $newCompany = $this->model->saveCompany($data);

// Check if the company was saved successfully
        if ($newCompany === false) {
            // Respond with validation errors or a general error message
            $errors = $this->model->errors();
            if (!empty($errors)) {
                return $this->failValidationErrors($errors);
            }

            return $this->failServerError('Failed to create company.');
        }

// Respond with the newly created company data
        $response = [
            "status" => true,
            "message" => "Company created successfully",
            "company" => $newCompany,
        ];

        return $this->respondCreated($response);

    }

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
