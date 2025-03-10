<?php

namespace App\Controllers;

use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\RESTful\ResourceController;

class CategoriesController extends ResourceController
{
       // Load the model
    protected $modelName = 'App\Models\CategoryModel';
    protected $format = 'json';

    /**
     * Get a list of all categories (index)
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
        $totalCategories = $this->model->selectCount('id')->where($where)->get()->getRowArray()['id'];
        $categories = $this->model->where($where)->orderBy('created_at', 'DESC')->paginate($perPage, 'categories', $page);
        $data = [
            'status' => true,
            'data' => [
                'categories' => $categories,
                'total' => $totalCategories,
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
        $category = $this->model->find($id);

        if (!$category) {
            return $this->failNotFound("Category not found with ID: $id");
        }

        return $this->respond($category);
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
     * Create a new resource object, from "posted" parameters.
     *
     * @return ResponseInterface
     */
    public function create_batch()
    {

        $data = $this->request->getJSON(true);

        // Check if $data is an array of records
        if (!isset($data[0]) || !is_array($data[0])) {
            return $this->fail("Invalid data format. Expected an array of records.");
        }

        // Add id to each record if not already present
        foreach ($data as &$record) {
            $id = uuid_v4();
            if (empty($record['id'])) {
                $record['id'] = $id;
            }
        }

        // return $this->respond($data);

        // Perform batch insertion
        if ($this->model->insertBatch($data)) {
            $response = [
                "status" => true,
                "message" => "Categories are Created",
                "categories" => $data,
            ];
            return $this->respondCreated($response);
        }

        return $this->fail("Failed to create Products.");
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
