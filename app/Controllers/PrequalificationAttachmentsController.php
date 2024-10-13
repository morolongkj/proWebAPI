<?php

namespace App\Controllers;

use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\RESTful\ResourceController;

class PrequalificationAttachmentsController extends ResourceController
{
    // Load the model
    protected $modelName = 'App\Models\PrequalificationAttachmentModel';
    protected $format = 'json';

    /**
     * Get a list of all prequalificationAttachments (index)
     * @return JSON
     */
    public function index()
    {
        $page = $this->request->getVar('page') ?? 1;
        $perPage = $this->request->getVar('perPage');
        if (!$perPage) {
            $perPage = null;
        }
        $prequalification_id = $this->request->getVar('prequalification_id');
        $where = [];
        if ($prequalification_id) {
            $where['prequalification_id like'] = '%' . $prequalification_id . '%';
        }

        $totalPrequalificationAttachments = $this->model->selectCount('id')->where($where)->get()->getRowArray()['id'];
        $prequalificationAttachments = $this->model->where($where)->orderBy('created_at', 'DESC')->paginate($perPage, 'prequalification_attachments', $page);
        $data = [
            'status' => true,
            'data' => [
                'prequalificationAttachments' => $prequalificationAttachments,
                'total' => $totalPrequalificationAttachments,
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
        $prequalificationAttachment = $this->model->find($id);

        if (!$prequalificationAttachment) {
            return $this->failNotFound("Prequalification Attachment not found with ID: $id");
        }

        return $this->respond($prequalificationAttachment);
    }

    /**
     * Delete a specific prequalificationAttachment (delete)
     * @param string $id
     * @return JSON
     */
    public function delete($id = null)
    {
        // Find the prequalificationAttachment
        $prequalificationAttachment = $this->model->find($id);
        if (!$prequalificationAttachment) {
            return $this->failNotFound("Prequalification attachment not found with ID: $id");
        }
        // Delete prequalificationAttachment
        if ($this->model->delete($id)) {
            return $this->respondDeleted(['message' => 'Prequalification attachment deleted successfully']);
        }

        return $this->failServerError('Failed to delete prequalification attachment.');
    }

    public function create()
    {
        $file = $this->request->getFile('file');
        $prequalification_id = $this->request->getPost('prequalification_id');
        $file_name = $this->request->getPost('file_name');

        if (!$file->isValid()) {
            return $this->fail('No file uploaded or file upload failed.', 400);
        }

        $newName = $file->getRandomName();
        $file->move(WRITEPATH . 'uploads', $newName);
        $filePath = WRITEPATH . 'uploads' . $newName;

        $fileUrl = base_url('uploads/' . $newName);
        // propagate to db
        $attachment = array(
            "prequalification_id" => $prequalification_id,
            "file_name" => $file_name,
            "file_path" => $fileUrl,
            "file_type" => ".pdf",
        );
        if ($this->model->save($attachment)) {
            $newId = $this->model->getInsertID();
            // Fetch the newly created prequalificationStatus for the response
            $newAttachment = $this->model->find($newId);
            $response = [
                "status" => true,
                "message" => "Prequalification Attachment is uploaded successfully",
                "prequalificationAttachment" => $newAttachment,
            ];
            return $this->respondCreated($response);
        }

        return $this->failServerError('Failed to upload an attachment.');

    }
}
