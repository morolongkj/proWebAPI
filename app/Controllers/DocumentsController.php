<?php

namespace App\Controllers;

use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\RESTful\ResourceController;

class DocumentsController extends ResourceController
{
    // Load the model
    protected $modelName = 'App\Models\DocumentModel';
    protected $format = 'json';

    /**
     * Get a list of all documents (index)
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
        $totalDocuments = $this->model->selectCount('id')->where($where)->get()->getRowArray()['id'];
        $documents = $this->model->where($where)->orderBy('created_at', 'DESC')->paginate($perPage, 'documents', $page);
        $data = [
            'status' => true,
            'data' => [
                'documents' => $documents,
                'total' => $totalDocuments,
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
        $document = $this->model->find($id);

        if (!$document) {
            return $this->failNotFound("Document not found with ID: $id");
        }

        return $this->respond($document);
    }

        /**
     * Create a new document (create)
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
            // Fetch the newly created document for the response
            $newDocument = $this->model->find($newId);
            $response = [
                "status" => true,
                "message" => "Document created successfully",
                "document" => $newDocument,
            ];
            return $this->respondCreated($response);
        }

        return $this->failServerError('Failed to create document.');
    }

   /**
     * Update a specific document (update)
     * @param string $id
     * @return JSON
     */
    public function update($id = null)
    {
        $data = $this->request->getJSON(true);
        $existingDocument = $this->model->find($id);
        if ($existingDocument) {
            if ($this->model->update($id, $data)) {
                $updatedDocument = $this->model->find($id);
                return $this->respondUpdated($updatedDocument);
            }
        } else {
            return $this->failNotFound('Document not found');
        }
        return $this->failServerError('Failed to update document.');
    }

   /**
     * Delete a specific document (delete)
     * @param string $id
     * @return JSON
     */
    public function delete($id = null)
    {
        // Find the document
        $document = $this->model->find($id);
        if (!$document) {
            return $this->failNotFound("Document not found with ID: $id");
        }
        // Delete questionnaire
        if ($this->model->delete($id)) {
            return $this->respondDeleted(['message' => 'Document deleted successfully']);
        }

        return $this->failServerError('Failed to delete document.');
    }
}
