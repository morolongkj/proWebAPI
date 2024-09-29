<?php

namespace App\Controllers;

use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\RESTful\ResourceController;

class TenderProductsController extends ResourceController
{
    protected $modelName = 'App\Models\TenderProductModel';
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
        $tender_id = $this->request->getVar('tender_id');
        $product_id = $this->request->getVar('product_id');
        $where = [];
        if ($tender_id) {
            $where['tender_id like'] = '%' . $tender_id . '%';
        }
        if ($product_id) {
            $where['product_id like'] = '%' . $product_id . '%';
        }
        $totalTenderProducts = $this->model->selectCount('id')->where($where)->get()->getRowArray()['id'];
        $tenderProducts = $this->model->where($where)->orderBy('created_at', 'DESC')->paginate($perPage, 'tender_products', $page);
        $data = [
            'status' => true,
            'data' => [
                'tenderProducts' => $tenderProducts,
                'total' => $totalTenderProducts,
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
        $tenderProduct = $this->model->find($id);

        if (!$tenderProduct) {
            return $this->failNotFound("Tender Productnot found with ID: $id");
        }

        return $this->respond($tenderProduct);
    }

    /**
     * Create a new tenderProduct (create)
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
            // Fetch the newly created tenderProduct for the response
            $newTenderProduct = $this->model->find($newId);
            $response = [
                "status" => true,
                "message" => "Tender Product created successfully",
                "tenderProduct" => $newTenderProduct,
            ];
            return $this->respondCreated($response);
        }

        return $this->failServerError('Failed to create tender product.');
    }

    /**
     * Update a specific tenderProduct (update)
     * @param string $id
     * @return JSON
     */
    public function update($id = null)
    {
        $data = $this->request->getJSON(true);
        $existingTenderProduct = $this->model->find($id);
        if ($existingTenderProduct) {
            if ($this->model->update($id, $data)) {
                $updatedTenderProduct = $this->model->find($id);
                return $this->respondUpdated($updatedTenderProduct);
            }
        } else {
            return $this->failNotFound('Tender Productnot found');
        }
        return $this->failServerError('Failed to update tender product.');
    }

    /**
     * Delete a specific tenderProduct (delete)
     * @param string $id
     * @return JSON
     */
    public function delete($id = null)
    {
        // Find the tenderProduct
        $tenderProduct = $this->model->find($id);
        if (!$tenderProduct) {
            return $this->failNotFound("Tender Productnot found with ID: $id");
        }
        // Delete tenderProduct
        if ($this->model->delete($id)) {
            return $this->respondDeleted(['message' => 'Tender Productdeleted successfully']);
        }

        return $this->failServerError('Failed to delete tender product.');
    }
}
