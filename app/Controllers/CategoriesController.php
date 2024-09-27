<?php

namespace App\Controllers;

use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\RESTful\ResourceController;

class CategoriesController extends ResourceController
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
     * Create a new category (create)
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
            // Fetch the newly created category for the response
            $newCategory = $this->model->find($newId);
            $response = [
                "status" => true,
                "message" => "Category created successfully",
                "category" => $newCategory,
            ];
            return $this->respondCreated($response);
        }

        return $this->failServerError('Failed to create category.');
    }

      /**
     * Update a specific category (update)
     * @param string $id
     * @return JSON
     */
    public function update($id = null)
    {
        $data = $this->request->getJSON(true);
        $existingCategory = $this->model->find($id);
        if ($existingCategory) {
            if ($this->model->update($id, $data)) {
                $updatedCategory = $this->model->find($id);
                return $this->respondUpdated($updatedCategory);
            }
        } else {
            return $this->failNotFound('Category not found');
        }
        return $this->failServerError('Failed to update category.');
    }

    /**
     * Delete a specific category (delete)
     * @param string $id
     * @return JSON
     */
    public function delete($id = null)
    {
        // Find the category
        $category = $this->model->find($id);
        if (!$category) {
            return $this->failNotFound("Category not found with ID: $id");
        }
        // Delete category
        if ($this->model->delete($id)) {
            return $this->respondDeleted(['message' => 'Category deleted successfully']);
        }

        return $this->failServerError('Failed to delete category.');
    }
}
