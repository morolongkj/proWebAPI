<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;

class PrequalifiedCompaniesController extends ResourceController
{
    protected $modelName = 'App\Models\PrequalifiedCompanyModel';
    protected $format = 'json';

    // Return all prequalified companies
    public function index()
    {
        $data = $this->model->findAll();
        return $this->respond($data);
    }

    // Return a single prequalified company by ID
    public function show($id = null)
    {
        $data = $this->model->find($id);
        if (!$data) {
            return $this->failNotFound('Prequalified company not found');
        }
        return $this->respond($data);
    }

    // Create a new prequalified company
    public function create()
    {
        $input = $this->getRequestInput($this->request);
        if (!$this->validate($this->model->validationRules)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        if ($this->model->insert($input)) {
            $response = [
                'status' => 201,
                'message' => 'Prequalified company created successfully',
                'data' => $input,
            ];
            return $this->respondCreated($response);
        } else {
            return $this->failServerError('Failed to create prequalified company');
        }
    }

    // Update an existing prequalified company by ID
    public function update($id = null)
    {
        $data = $this->model->find($id);
        if (!$data) {
            return $this->failNotFound('Prequalified company not found');
        }

        $input = $this->getRequestInput($this->request);
        if (!$this->validate($this->model->validationRules)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        if ($this->model->update($id, $input)) {
            $response = [
                'status' => 200,
                'message' => 'Prequalified company updated successfully',
                'data' => $input,
            ];
            return $this->respond($response);
        } else {
            return $this->failServerError('Failed to update prequalified company');
        }
    }

    // Delete an existing prequalified company by ID
    public function delete($id = null)
    {
        $data = $this->model->find($id);
        if (!$data) {
            return $this->failNotFound('Prequalified company not found');
        }

        if ($this->model->delete($id)) {
            return $this->respondDeleted(['message' => 'Prequalified company deleted successfully']);
        } else {
            return $this->failServerError('Failed to delete prequalified company');
        }
    }

    // Get all prequalified companies for a specific product ID
    public function companiesByProduct($productId)
    {
        $data = $this->model->getPrequalifiedCompaniesByProductId($productId);
        if (empty($data)) {
            return $this->failNotFound('No prequalified companies found for the given product ID');
        }
        return $this->respond($data);
    }

    // Get all prequalified products for a specific company ID
    public function productsByCompany($companyId)
    {
        $data = $this->model->getPrequalifiedProductsByCompanyId($companyId);
        if (empty($data)) {
            return $this->failNotFound('No prequalified products found for the given company ID');
        }
        return $this->respond($data);
    }

    // Check if a company is prequalified for a specific product
    public function isPrequalified($companyId, $productId)
    {
        $isPrequalified = $this->model->isCompanyPrequalified($companyId, $productId);
        return $this->respond(['isPrequalified' => $isPrequalified]);
    }

    // Helper method to get request input
    private function getRequestInput($request)
    {
        $input = $request->getPost();
        if (empty($input)) {
            $input = json_decode($request->getBody(), true);
        }
        return $input;
    }
}
