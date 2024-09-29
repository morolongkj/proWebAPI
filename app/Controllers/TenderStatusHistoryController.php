<?php

namespace App\Controllers;

use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\RESTful\ResourceController;

class TenderStatusHistoryController extends ResourceController
{
    // Load the model
    protected $modelName = 'App\Models\TenderStatusHistoryModel';
    protected $format = 'json';

    /**
     * Get a list of all tenderStatusHistory (index)
     * @return JSON
     */
    public function index()
    {
        $page = $this->request->getVar('page') ?? 1;
        $perPage = $this->request->getVar('perPage');
        if (!$perPage) {
            $perPage = null;
        }
        $tender_id = $this->request->getVar('tender_id');
        $status_id = $this->request->getVar('status_id');
        $where = [];
        if ($tender_id) {
            $where['tender_id like'] = '%' . $tender_id . '%';
        }
        if ($status_id) {
            $where['status_id like'] = '%' . $status_id . '%';
        }
        $totalTenderStatusHistory = $this->model->selectCount('id')->where($where)->get()->getRowArray()['id'];
        $tenderStatusHistory = $this->model->where($where)->orderBy('created_at', 'DESC')->paginate($perPage, 'tender_status_history', $page);
        $data = [
            'status' => true,
            'data' => [
                'tenderStatusHistory' => $tenderStatusHistory,
                'total' => $totalTenderStatusHistory,
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
        $tenderStatusHistory = $this->model->find($id);

        if (!$tenderStatusHistory) {
            return $this->failNotFound("Tender Status History not found with ID: $id");
        }

        return $this->respond($tenderStatusHistory);
    }

    /**
     * Create a new tenderStatusHistory (create)
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
            // Fetch the newly created tenderStatusHistory for the response
            $newTenderStatusHistory = $this->model->find($newId);
            $response = [
                "status" => true,
                "message" => "Tender Status History created successfully",
                "tenderStatusHistory" => $newTenderStatusHistory,
            ];
            return $this->respondCreated($response);
        }

        return $this->failServerError('Failed to create tender status history.');
    }

    /**
     * Update a specific tenderStatusHistory (update)
     * @param string $id
     * @return JSON
     */
    public function update($id = null)
    {
        $data = $this->request->getJSON(true);
        $existingTenderStatusHistory = $this->model->find($id);
        if ($existingTenderStatusHistory) {
            if ($this->model->update($id, $data)) {
                $updatedTenderStatusHistory = $this->model->find($id);
                return $this->respondUpdated($updatedTenderStatusHistory);
            }
        } else {
            return $this->failNotFound('Tender Status History not found');
        }
        return $this->failServerError('Failed to update tender status history.');
    }

    /**
     * Delete a specific tenderStatusHistory (delete)
     * @param string $id
     * @return JSON
     */
    public function delete($id = null)
    {
        // Find the tenderStatusHistory
        $tenderStatusHistory = $this->model->find($id);
        if (!$tenderStatusHistory) {
            return $this->failNotFound("Tender Status History not found with ID: $id");
        }
        // Delete tenderStatusHistory
        if ($this->model->delete($id)) {
            return $this->respondDeleted(['message' => 'Tender Status History deleted successfully']);
        }

        return $this->failServerError('Failed to delete tender status history.');
    }
}
