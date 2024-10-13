<?php

namespace App\Controllers;

use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\RESTful\ResourceController;

class PrequalificationStatusHistoryController extends ResourceController
{
    // Load the model
    protected $modelName = 'App\Models\PrequalificationStatusHistoryModel';
    protected $format = 'json';

    /**
     * Get a list of all prequalificationStatusHistory (index)
     * @return JSON
     */
    public function index()
    {
        $page = $this->request->getVar('page') ?? 1;
        $perPage = $this->request->getVar('perPage');
        if (!$perPage) {
            $perPage = null;
        }
        $prequalification_id = $this->request->getVar('prequalification_id');
        $status_id = $this->request->getVar('status_id');
        $where = [];
        if ($prequalification_id) {
            $where['prequalification_id like'] = '%' . $prequalification_id . '%';
        }
        if ($status_id) {
            $where['status_id like'] = '%' . $status_id . '%';
        }
        $totalPrequalificationStatusHistory = $this->model->selectCount('id')->where($where)->get()->getRowArray()['id'];
        $prequalificationStatusHistory = $this->model->where($where)->orderBy('created_at', 'DESC')->paginate($perPage, 'prequalification_status_history', $page);
        $data = [
            'status' => true,
            'data' => [
                'prequalificationStatusHistory' => $prequalificationStatusHistory,
                'total' => $totalPrequalificationStatusHistory,
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
        $prequalificationStatusHistory = $this->model->find($id);

        if (!$prequalificationStatusHistory) {
            return $this->failNotFound("Prequalification Status History not found with ID: $id");
        }

        return $this->respond($prequalificationStatusHistory);
    }

    /**
     * Create a new prequalificationStatusHistory (create)
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
            // Fetch the newly created prequalificationStatusHistory for the response
            $newPrequalificationStatusHistory = $this->model->find($newId);
            $response = [
                "status" => true,
                "message" => "Prequalification Status History created successfully",
                "prequalificationStatusHistory" => $newPrequalificationStatusHistory,
            ];
            return $this->respondCreated($response);
        }

        return $this->failServerError('Failed to create prequalification status history.');
    }

    /**
     * Update a specific prequalificationStatusHistory (update)
     * @param string $id
     * @return JSON
     */
    public function update($id = null)
    {
        $data = $this->request->getJSON(true);
        $existingPrequalificationStatusHistory = $this->model->find($id);
        if ($existingPrequalificationStatusHistory) {
            if ($this->model->update($id, $data)) {
                $updatedPrequalificationStatusHistory = $this->model->find($id);
                return $this->respondUpdated($updatedPrequalificationStatusHistory);
            }
        } else {
            return $this->failNotFound('Prequalification Status History not found');
        }
        return $this->failServerError('Failed to update prequalification status history.');
    }

    /**
     * Delete a specific prequalificationStatusHistory (delete)
     * @param string $id
     * @return JSON
     */
    public function delete($id = null)
    {
        // Find the prequalificationStatusHistory
        $prequalificationStatusHistory = $this->model->find($id);
        if (!$prequalificationStatusHistory) {
            return $this->failNotFound("Prequalification Status History not found with ID: $id");
        }
        // Delete prequalificationStatusHistory
        if ($this->model->delete($id)) {
            return $this->respondDeleted(['message' => 'Prequalification Status History deleted successfully']);
        }

        return $this->failServerError('Failed to delete prequalification status history.');
    }
}
