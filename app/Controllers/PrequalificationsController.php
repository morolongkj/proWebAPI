<?php

namespace App\Controllers;

use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\RESTful\ResourceController;

class PrequalificationsController extends ResourceController
{
    protected $modelName = 'App\Models\PrequalificationModel';
    protected $format = 'json';
    /**
     * Return an array of resource objects, themselves in array format.
     *
     * @return ResponseInterface
     */
    public function index()
    {
        $page = $this->request->getVar('page') ?? 1;
        $perPage = $this->request->getVar('perPage');
        if (!$perPage) {
            $perPage = null;
        }

        // Filtering parameters
        $filters = [
            'company_id' => $this->request->getVar('company_id'),
            'questionnaire_id' => $this->request->getVar('questionnaire_id'),
            'current_status_id' => $this->request->getVar('current_status_id'),
            'status' => $this->request->getVar('status'),
        ];

        // Call the model method to get the prequalifications with their details
        $result = $this->model->getPrequalificationsWithDetails($filters, $perPage, $page);

        // Prepare the response
        $data = [
            'status' => true,
            'data' => $result,
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
        $prequalification = $this->model->getPrequalificationWithDetails($id);

        if (!$prequalification) {
            return $this->failNotFound("Prequalification notfound with ID: $id");
        }

        return $this->respond($prequalification);
    }

    /**
     * Create a new prequalification  (create)
     * @return JSON
     */
    public function create()
    {
        $user_id = auth()->id();
        $data = $this->request->getJSON(true);
        $data['created_by'] = $user_id;

// Save the prequalification and status history
        $newPrequalification = $this->model->savePrequalification($data);

// Check if the prequalification was saved successfully
        if ($newPrequalification === false) {
            // Respond with validation errors or a general error message
            $errors = $this->model->errors();
            if (!empty($errors)) {
                return $this->failValidationErrors($errors);
            }

            return $this->failServerError('Failed to create prequalification.');
        }

// Respond with the newly created prequalification data
        $response = [
            "status" => true,
            "message" => "Prequalification created successfully",
            "prequalification" => $newPrequalification,
        ];

        return $this->respondCreated($response);

    }

    /**
     * Update a specific prequalification  (update)
     * @param string $id
     * @return JSON
     */
    public function update($id = null)
    {
        $user_id = auth()->id();
        $data = $this->request->getJSON(true);
        $existingPrequalification = $this->model->find($id);
        if ($existingPrequalification) {
            // Check if the current_status_id has changed
            if (isset($data['current_status_id']) && $existingPrequalification['current_status_id'] !== $data['current_status_id']) {
                // Update the status history
                $prequalificationStatusHistoryModel = new \App\Models\PrequalificationStatusHistoryModel();
                $remarks = isset($data['remarks']) ? $data['remarks'] : '';
                if (!$prequalificationStatusHistoryModel->addHistory($id, $data['current_status_id'], $user_id, $remarks)) {
                    return $this->failServerError('Failed to update status history.');
                }
            }

            if ($this->model->update($id, $data)) {
                $updatedPrequalification = $this->model->find($id);
                return $this->respondUpdated($updatedPrequalification);
            }
        } else {
            return $this->failNotFound('Prequalification notfound');
        }
        return $this->failServerError('Failed to update prequalification .');
    }

    /**
     * Delete a specific prequalification  (delete)
     * @param string $id
     * @return JSON
     */
    public function delete($id = null)
    {
        // Find the prequalification
        $prequalification = $this->model->find($id);
        if (!$prequalification) {
            return $this->failNotFound("Prequalification not found with ID: $id");
        }
        // Delete prequalification
        if ($this->model->delete($id)) {
            return $this->respondDeleted(['message' => 'Prequalification deleted successfully']);
        }

        return $this->failServerError('Failed to delete prequalification .');
    }
}
