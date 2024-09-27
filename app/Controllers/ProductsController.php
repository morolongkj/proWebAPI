<?php

namespace App\Controllers;

use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\RESTful\ResourceController;

class ProductsController extends ResourceController
{
    /**
     * Return an array of resource objects, themselves in array format.
     *
     * @return ResponseInterface
     */
    public function index()
    {
        //
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
        //
    }

         /**
     * Create a new product (create)
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
            // Fetch the newly created product for the response
            $newProduct = $this->model->find($newId);
            $response = [
                "status" => true,
                "message" => "Product created successfully",
                "product" => $newProduct,
            ];
            return $this->respondCreated($response);
        }

        return $this->failServerError('Failed to create product.');
    }

    /**
     * Update a specific product (update)
     * @param string $id
     * @return JSON
     */
    public function update($id = null)
    {
        $data = $this->request->getJSON(true);
        $existingProduct = $this->model->find($id);
        if ($existingProduct) {
            if ($this->model->update($id, $data)) {
                $updatedProduct = $this->model->find($id);
                return $this->respondUpdated($updatedProduct);
            }
        } else {
            return $this->failNotFound('Product not found');
        }
        return $this->failServerError('Failed to update product.');
    }

      /**
     * Delete a specific product (delete)
     * @param string $id
     * @return JSON
     */
    public function delete($id = null)
    {
        // Find the product
        $product = $this->model->find($id);
        if (!$product) {
            return $this->failNotFound("Product not found with ID: $id");
        }
        // Delete product
        if ($this->model->delete($id)) {
            return $this->respondDeleted(['message' => 'Product deleted successfully']);
        }

        return $this->failServerError('Failed to delete product.');
    }
}
