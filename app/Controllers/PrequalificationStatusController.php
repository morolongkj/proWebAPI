<?php

namespace App\Controllers;

use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\RESTful\ResourceController;

class PrequalificationStatusController extends ResourceController
{
    // Load the model
    protected $modelName = 'App\Models\PrequalificationStatusModel';
    protected $format = 'json';

    /**
     * Get a list of all prequalificationStatuses (index)
     * @return JSON
     */
    public function index()
    {
        $page = $this->request->getVar('page') ?? 1;
        $perPage = $this->request->getVar('perPage');
        if (!$perPage) {
            $perPage = null;
        }
        $status = $this->request->getVar('status');
        $description = $this->request->getVar('description');
        $where = [];
        if ($status) {
            $where['status like'] = '%' . $status . '%';
        }
        if ($description) {
            $where['description like'] = '%' . $description . '%';
        }
        $totalPrequalificationStatus = $this->model->selectCount('id')->where($where)->get()->getRowArray()['id'];
        $prequalificationStatuses = $this->model->where($where)->orderBy('created_at', 'DESC')->paginate($perPage, 'prequalification_status', $page);
        $data = [
            'status' => true,
            'data' => [
                'prequalificationStatuses' => $prequalificationStatuses,
                'total' => $totalPrequalificationStatus,
            ],
        ];

        return $this->respond($data);
    }

    /**
     * Return the properties of a resource object.
     *
     * @param int|string|null $id
     *
     * @return ResponseInterface
     */
    public function show($id = null)
    {
        $prequalificationStatus = $this->model->find($id);

        if (!$prequalificationStatus) {
            return $this->failNotFound("Prequalification Status not found with ID: $id");
        }

        return $this->respond($prequalificationStatus);
    }

    /**
     * Create a new prequalificationStatus (create)
     * @return JSON
     */
    public function create()
    {
        $data = $this->request->getJSON(true);

        // Validate data before inserting
        if (!$this->validate($this->model->validationRules)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        if ($this->model->save($data)) {
            $newId = $this->model->getInsertID();
            // Fetch the newly created prequalificationStatus for the response
            $newPrequalificationStatus = $this->model->find($newId);
            $response = [
                "status" => true,
                "message" => "Prequalification Status created successfully",
                "prequalificationStatus" => $newPrequalificationStatus,
            ];
            return $this->respondCreated($response);
        }

        return $this->failServerError('Failed to create prequalification status.');
    }

    /**
     * Update a specific prequalificationStatus (update)
     * @param string $id
     * @return JSON
     */
    public function update($id = null)
    {
        $data = $this->request->getJSON(true);
        $existingPrequalificationStatus = $this->model->find($id);
        if ($existingPrequalificationStatus) {
            if ($this->model->update($id, $data)) {
                $updatedPrequalificationStatus = $this->model->find($id);
                return $this->respondUpdated($updatedPrequalificationStatus);
            }
        } else {
            return $this->failNotFound('Prequalification Status not found');
        }
        return $this->failServerError('Failed to update prequalification status.');
    }

    /**
     * Delete a specific prequalificationStatus (delete)
     * @param string $id
     * @return JSON
     */
    public function delete($id = null)
    {
        // Find the prequalificationStatus
        $prequalificationStatus = $this->model->find($id);
        if (!$prequalificationStatus) {
            return $this->failNotFound("Prequalification Status not found with ID: $id");
        }
        // Delete prequalificationStatus
        if ($this->model->delete($id)) {
            return $this->respondDeleted(['message' => 'Prequalification Status deleted successfully']);
        }

        return $this->failServerError('Failed to delete prequalification status.');
    }
}
