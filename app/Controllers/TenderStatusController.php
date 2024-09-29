<?php

namespace App\Controllers;

use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\RESTful\ResourceController;

class TenderStatusController extends ResourceController
{
    // Load the model
    protected $modelName = 'App\Models\TenderStatusModel';
    protected $format = 'json';

    /**
     * Get a list of all tenderStatuses (index)
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
        $totalTenderStatus = $this->model->selectCount('id')->where($where)->get()->getRowArray()['id'];
        $tenderStatuses = $this->model->where($where)->orderBy('created_at', 'DESC')->paginate($perPage, 'tender_status', $page);
        $data = [
            'status' => true,
            'data' => [
                'tenderStatuses' => $tenderStatuses,
                'total' => $totalTenderStatus,
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
        $tenderStatus = $this->model->find($id);

        if (!$tenderStatus) {
            return $this->failNotFound("Tender Status not found with ID: $id");
        }

        return $this->respond($tenderStatus);
    }

    /**
     * Create a new tenderStatus (create)
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
            // Fetch the newly created tenderStatus for the response
            $newTenderStatus = $this->model->find($newId);
            $response = [
                "status" => true,
                "message" => "Tender Status created successfully",
                "tenderStatus" => $newTenderStatus,
            ];
            return $this->respondCreated($response);
        }

        return $this->failServerError('Failed to create tender status.');
    }

    /**
     * Update a specific tenderStatus (update)
     * @param string $id
     * @return JSON
     */
    public function update($id = null)
    {
        $data = $this->request->getJSON(true);
        $existingTenderStatus = $this->model->find($id);
        if ($existingTenderStatus) {
            if ($this->model->update($id, $data)) {
                $updatedTenderStatus = $this->model->find($id);
                return $this->respondUpdated($updatedTenderStatus);
            }
        } else {
            return $this->failNotFound('Tender Status not found');
        }
        return $this->failServerError('Failed to update tender status.');
    }

    /**
     * Delete a specific tenderStatus (delete)
     * @param string $id
     * @return JSON
     */
    public function delete($id = null)
    {
        // Find the tenderStatus
        $tenderStatus = $this->model->find($id);
        if (!$tenderStatus) {
            return $this->failNotFound("Tender Status not found with ID: $id");
        }
        // Delete tenderStatus
        if ($this->model->delete($id)) {
            return $this->respondDeleted(['message' => 'Tender Status deleted successfully']);
        }

        return $this->failServerError('Failed to delete tender status.');
    }
}
