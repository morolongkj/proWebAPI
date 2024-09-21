<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;

class PrequalificationStagesController extends ResourceController
{
    // Load the model
    protected $modelName = 'App\Models\PrequalificationStageModel';
    protected $format = 'json';

    /**
     * Get a list of all prequalificationStages (index)
     * @return JSON
     */
    public function index()
    {
        $page = $this->request->getVar('page') ?? 1;
        $perPage = $this->request->getVar('perPage');
        if (!$perPage) {
            $perPage = null;
        }
        $title = $this->request->getVar('title');
        $description = $this->request->getVar('description');
        $where = [];
        if ($title) {
            $where['title like'] = '%' . $title . '%';
        }
        if ($description) {
            $where['description like'] = '%' . $description . '%';
        }
        $totalPrequalificationStages = $this->model->selectCount('id')->where($where)->get()->getRowArray()['id'];
        $prequalificationStages = $this->model->where($where)->orderBy('created_at', 'DESC')->paginate($perPage, 'prequalification_states', $page);
        $data = [
            'status' => true,
            'data' => [
                'prequalificationStages' => $prequalificationStages,
                'total' => $totalPrequalificationStages,
            ],
        ];

        return $this->respond($data);

    }

    /**
     * Get a specific prequalificationStage by ID (show)
     * @param string $id
     * @return JSON
     */
    public function show($id = null)
    {
        $prequalificationStage = $this->model->find($id);

        if (!$prequalificationStage) {
            return $this->failNotFound("Prequalification Stage not found with ID: $id");
        }

        return $this->respond($prequalificationStage);
    }

    /**
     * Create a new prequalificationStage (create)
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
            // Fetch the newly created prequalificationStage for the response
            $newPrequalificationStage = $this->model->find($newId);
            $response = [
                "status" => true,
                "message" => "PrequalificationStage created successfully",
                "prequalificationStage" => $newPrequalificationStage,
            ];
            return $this->respondCreated($response);
        }

        return $this->failServerError('Failed to create prequalificationStage.');
    }

    /**
     * Update a specific prequalificationStage (update)
     * @param string $id
     * @return JSON
     */
    public function update($id = null)
    {
        $data = $this->request->getJSON(true);
        $existingPrequalificationStage = $this->model->find($id);
        if ($existingPrequalificationStage) {
            if ($this->model->update($id, $data)) {
                $updatedPrequalificationStage = $this->model->find($id);
                return $this->respondUpdated($updatedPrequalificationStage);
            }
        } else {
            return $this->failNotFound('Prequalification Stage not found');
        }
        return $this->failServerError('Failed to update document.');
    }

    /**
     * Delete a specific prequalificationStage (delete)
     * @param string $id
     * @return JSON
     */
    public function delete($id = null)
    {
        // Find the prequalificationStage
        $prequalificationStage = $this->model->find($id);
        if (!$prequalificationStage) {
            return $this->failNotFound("Prequalification Stage not found with ID: $id");
        }
        // Delete prequalificationStage
        if ($this->model->delete($id)) {
            return $this->respondDeleted(['message' => 'Prequalification Stage deleted successfully']);
        }

        return $this->failServerError('Failed to delete prequalification Stage.');
    }
}
