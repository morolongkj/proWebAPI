<?php

namespace App\Controllers;

use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\RESTful\ResourceController;

class TenderAttachmentsController extends ResourceController
{
    // Load the model
    protected $modelName = 'App\Models\TenderAttachmentModel';
    protected $format = 'json';

    /**
     * Get a list of all tenderAttachments (index)
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
        $where = [];
        if ($tender_id) {
            $where['tender_id like'] = '%' . $tender_id . '%';
        }

        $totalTenderAttachments = $this->model->selectCount('id')->where($where)->get()->getRowArray()['id'];
        $tenderAttachments = $this->model->where($where)->orderBy('created_at', 'DESC')->paginate($perPage, 'tender_attachments', $page);
        $data = [
            'status' => true,
            'data' => [
                'tenderAttachments' => $tenderAttachments,
                'total' => $totalTenderAttachments,
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
        $tenderAttachment = $this->model->find($id);

        if (!$tenderAttachment) {
            return $this->failNotFound("Tender Attachment not found with ID: $id");
        }

        return $this->respond($tenderAttachment);
    }

    /**
     * Delete a specific tenderAttachment (delete)
     * @param string $id
     * @return JSON
     */
    public function delete($id = null)
    {
        // Find the tenderAttachment
        $tenderAttachment = $this->model->find($id);
        if (!$tenderAttachment) {
            return $this->failNotFound("Tender attachment not found with ID: $id");
        }
        // Delete tenderAttachment
        if ($this->model->delete($id)) {
            return $this->respondDeleted(['message' => 'Tender attachment deleted successfully']);
        }

        return $this->failServerError('Failed to delete tender attachment.');
    }

    public function create()
    {
        $file = $this->request->getFile('file');
        $tender_id = $this->request->getPost('tender_id');
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
            "tender_id" => $tender_id,
            "file_name" => $file_name,
            "file_path" => $fileUrl,
            "file_type" => ".pdf",
        );
        if ($this->model->save($attachment)) {
            $newId = $this->model->getInsertID();
            // Fetch the newly created tenderStatus for the response
            $newAttachment = $this->model->find($newId);
            $response = [
                "status" => true,
                "message" => "Tender Attachment is uploaded successfully",
                "tenderAttachment" => $newAttachment,
            ];
            return $this->respondCreated($response);
        }

        return $this->failServerError('Failed to upload an attachment.');

    }
}
