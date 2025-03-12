<?php

namespace App\Controllers;

use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\RESTful\ResourceController;

class TendersController extends ResourceController
{
    protected $modelName = 'App\Models\TenderModel';
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
            'reference_number' => $this->request->getVar('reference_number'),
            'title' => $this->request->getVar('title'),
            'description' => $this->request->getVar('description'),
            'floating_date' => $this->request->getVar('floating_date'),
            'closing_date' => $this->request->getVar('closing_date'),
            'current_status_id' => $this->request->getVar('current_status_id'),
            'status' => $this->request->getVar('status'),
        ];

        // Call the model method to get the tenders with their details
        $result = $this->model->getTendersWithDetails($filters, $perPage, $page);

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
        $tender = $this->model->getTenderWithDetails($id);

        if (!$tender) {
            return $this->failNotFound("Tender notfound with ID: $id");
        }

        return $this->respond($tender);
    }

    /**
     * Create a new tender  (create)
     * @return JSON
     */
    public function create()
    {
        $user_id = auth()->id();
        $data = $this->request->getJSON(true);
        $data['created_by'] = $user_id;

// Save the tender and status history
        $newTender = $this->model->saveTender($data);

        // return $this->respond($newTender);

// Check if the tender was saved successfully
        if ($newTender === false) {
            // Respond with validation errors or a general error message
            $errors = $this->model->errors();
            if (!empty($errors)) {
                return $this->failValidationErrors($errors);
            }

            return $this->failServerError('Failed to create tender.');
        }

// Respond with the newly created tender data
        $response = [
            "status" => true,
            "message" => "Tender created successfully",
            "tender" => $newTender,
        ];

        return $this->respondCreated($response);

    }

    /**
     * Update a specific tender  (update)
     * @param string $id
     * @return JSON
     */
    public function update($id = null)
    {
        $user_id = auth()->id();
        $data = $this->request->getJSON(true);
        $existingTender = $this->model->find($id);
        if ($existingTender) {
            // Check if the current_status_id has changed
            if (isset($data['current_status_id']) && $existingTender['current_status_id'] !== $data['current_status_id']) {
                // Update the status history
                $tenderStatusHistoryModel = new \App\Models\TenderStatusHistoryModel();
                $remarks = isset($data['remarks']) ? $data['remarks'] : '';
                if (!$tenderStatusHistoryModel->addHistory($id, $data['current_status_id'], $user_id, $remarks)) {
                    return $this->failServerError('Failed to update status history.');
                }
            }

            if ($this->model->update($id, $data)) {
                $updatedTender = $this->model->find($id);
                return $this->respondUpdated($updatedTender);
            }
        } else {
            return $this->failNotFound('Tender notfound');
        }
        return $this->failServerError('Failed to update tender .');
    }

    /**
     * Delete a specific tender  (delete)
     * @param string $id
     * @return JSON
     */
    public function delete($id = null)
    {
        // Find the tender
        $tender = $this->model->find($id);
        if (!$tender) {
            return $this->failNotFound("Tender notfound with ID: $id");
        }
        // Delete tender
        if ($this->model->delete($id)) {
            return $this->respondDeleted(['message' => 'Tender deleted successfully']);
        }

        return $this->failServerError('Failed to delete tender .');
    }
}
