<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;

class QuestionnaireDocumentsController extends ResourceController
{
    // Load the model
    protected $modelName = 'App\Models\QuestionnaireDocumentModel';
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
        $questionnaire_id = $this->request->getVar('questionnaire_id');
        $file_name = $this->request->getVar('file_name');
        $where = [];
        if ($questionnaire_id) {
            $where['questionnaire_id like'] = '%' . $questionnaire_id . '%';
        }
        if ($file_name) {
            $where['file_name like'] = '%' . $file_name . '%';
        }
        $totalDocuments = $this->model->selectCount('id')->where($where)->get()->getRowArray()['id'];
        $documents = $this->model->where($where)->orderBy('created_at', 'DESC')->paginate($perPage, 'questionnaire_documents', $page);
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
     * Get a specific document by ID (show)
     * @param string $id
     * @return JSON
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
        $data = $this->request->getPost();

        // Validate data before inserting
        if (!$this->validate($this->model->validationRules)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        // Check if a file is uploaded
        $file = $this->request->getFile('file'); // Assuming the input name is 'file'
        if ($file && $file->isValid() && !$file->hasMoved()) {
            // Define the path where the file will be saved
            $path = WRITEPATH . 'uploads/'; // Make sure the directory exists and is writable
            $fileName = $file->getRandomName(); // Generate a unique file name

            // Move the file to the designated directory
            if ($file->move($path, $fileName)) {
                // Prepare data to save to the database
                $fileUrl = base_url('uploads/' . $fileName);
                $data['file_path'] = $fileUrl; // Save full path or just file name
                // $data['file_name'] = $fileName;
                $data['file_type'] = $file->getClientMimeType(); // Get the MIME type of the file
                $data['file_size'] = $file->getSize(); // Get the file size in bytes

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
            } else {
                return $this->failServerError('Failed to move uploaded file.');
            }
        }

        return $this->failServerError('Failed to upload file.');
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
        // Delete document
        if ($this->model->delete($id)) {
            return $this->respondDeleted(['message' => 'Document deleted successfully']);
        }

        return $this->failServerError('Failed to delete document.');
    }
}
