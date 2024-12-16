<?php

namespace App\Controllers;

use App\Models\BidAttachmentModel;
use App\Models\BidProductModel;
use App\Models\BidStatusHistoryModel;
use App\Models\CompanyModel;
use App\Models\NotificationModel;
use App\Models\ProductModel;
use App\Models\StatusModel;
use App\Models\TenderModel;
use CodeIgniter\RESTful\ResourceController;

class BidsController extends ResourceController
{
    // Load the model
    protected $modelName = 'App\Models\BidModel';
    protected $format = 'json';
    protected $bidAttachmentModel;
    protected $statusModel;
    protected $bidStatusHistoryModel;
    protected $companyModel;
    protected $productModel;
    protected $bidProductModel;
    protected $notificationModel;
    protected $tenderModel;

    public function __construct()
    {
        // $this->model = new BidModel();
        $this->bidAttachmentModel = new BidAttachmentModel();
        $this->statusModel = new StatusModel();
        $this->companyModel = new CompanyModel();
        $this->productModel = new ProductModel();
        $this->bidStatusHistoryModel = new BidStatusHistoryModel();
        $this->bidProductModel = new BidProductModel();
        $this->notificationModel = new NotificationModel();
        $this->tenderModel = new TenderModel();

        helper(['form', 'filesystem']);
    }

    public function index()
    {
        // Get pagination parameters from the query string
        $page = $this->request->getVar('page') ?? 1;
        $perPage = $this->request->getVar('perPage');
        if (!$perPage) {
            $perPage = null; // Use default perPage if not provided
        }

        // Get filter parameters from the query string
        $companyId = $this->request->getVar('company_id');
        $tenderId = $this->request->getVar('tender_id');

        $searchTerm = $this->request->getVar('searchTerm');

        // Start building the query
        $query = $this->model;

        // $query = $query->select('bids.*');
        $query = $query->select('bids.*, tenders.title, tenders.reference_number, companies.company_name')
            ->join('tenders', 'tenders.id = bids.tender_id', 'left')
            ->join('companies', 'companies.id = bids.company_id', 'left');

        if ($companyId) {
            $query = $query->where('company_id', $companyId);
        }

        if ($tenderId) {
            $query = $query->where('tender_id', $tenderId);
        }

        // Apply search term filter
        if ($searchTerm) {
            $query = $query->groupStart()
                ->like('bids.submission_date', $searchTerm)
                ->orLike('companies.company_name', $searchTerm)
                ->orLike('tenders.title', $searchTerm)
                ->orLike('tenders.reference_number', $searchTerm)
                ->groupEnd();
        }

        $query = $query->orderBy('created_at', 'DESC');

        $totalBids = $query->countAllResults(false);
        $bids = $query->paginate($perPage, 'bids', $page);
        foreach ($bids as &$bid) {
            $bid['attachments'] = $this->bidAttachmentModel->getRecordsByBidId($bid['id']);
            $bid['status_history'] = $this->bidStatusHistoryModel->getRecordsByBidId($bid['id']);
            $bid['status'] = $this->statusModel->findById($bid['current_status_id']);
            $bid['company'] = $this->companyModel->findById($bid['company_id']);
            $bid['products'] = $this->bidProductModel->getRecordsByBidId($bid['id']);
            $bid['tender'] = $this->tenderModel->getTenderWithDetails($bid['tender_id']);
        }

        $response = [
            'data' => [
                'bids' => $bids,
                'total' => $totalBids,
            ],
        ];

        return $this->respond($response);
    }

/**
 * Submit a bid with optional attachments.
 */
    public function create()
    {
        // Retrieve the current user's ID
        $userId = auth()->id();

        if (!$userId) {
            return $this->failUnauthorized('User is not authenticated.');
        }

        $data = $this->request->getPost();
        $products = $data['products'] ?? []; // Retrieve products from the request
        unset($data['products']); // Remove products from main data array

        $data['current_status_id'] = $this->statusModel->getStatusIdByTitle('Submitted');

        // Validate data before inserting
        if (!$this->validate($this->model->validationRules)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        // Start transaction
        $db = \Config\Database::connect();
        $db->transBegin();

        try {
            // Save the main submission data
            if (!$this->model->save($data)) {
                throw new \Exception('Failed to save bid submission.');
            }

            $newId = $this->model->getInsertID();
            $newSubmission = $this->model->find($newId);

            // Handle attachments if provided
            if ($this->request->getFiles() && !empty($newSubmission)) {
                $uploadStatus = $this->addAttachments($newId);
                if (!$uploadStatus['success']) {
                    throw new \Exception($uploadStatus['message']);
                }
            }

            // Insert products into bid_products table
            if (!empty($products)) {
                foreach ($products as $product) {
                    $product['bid_id'] = $newId; // Associate product with the bid ID

                    // Validate product data before insertion
                    if (!$this->bidProductModel->validate($product)) {
                        return $this->failValidationErrors($this->bidProductModel->errors());
                    }

                    if (!$this->bidProductModel->save($product)) {
                        throw new \Exception('Failed to save product: ' . json_encode($product));
                    }
                }
            }

            // Update status history
            $status_history = [
                'bid_id' => $newId,
                'status_id' => $data['current_status_id'],
                'changed_by' => $userId,
            ];

            if (!$this->bidStatusHistoryModel->validate($status_history)) {
                return $this->failValidationErrors($this->bidStatusHistoryModel->errors());
            }

            if (!$this->bidStatusHistoryModel->save($status_history)) {
                throw new \Exception('Failed to save status history.');
            }

            // Commit the transaction if all operations are successful
            $db->transCommit();

            // Prepare and return success response
            $response = [
                "status" => true,
                "message" => "Submitted successfully",
                "bid" => $newSubmission,
            ];
            return $this->respondCreated($response);

        } catch (\Exception $e) {
            // Rollback transaction if any operation fails
            $db->transRollback();
            return $this->failServerError($e->getMessage());
        }
    }

    /**
     * Add attachments for a given questionnaire submission.
     *
     * @param string $bidId
     * @return array
     */
    private function addAttachments(string $bidId)
    {
        $files = $this->request->getFiles();
        $uploadedFiles = [];
        $uploadDir = WRITEPATH . 'uploads/bids/' . $bidId . '/';

        // Ensure the upload directory exists
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // Check if files exist under 'attachments'
        if (isset($files['attachments'])) {
            $attachments = $files['attachments'];
            $attachmentNames = $this->request->getPost('attachment_names');

            // Ensure $attachments is treated as an array for multiple files
            $attachments = is_array($attachments) ? $attachments : [$attachments];
            $attachmentNames = is_array($attachmentNames) ? $attachmentNames : [$attachmentNames];

            foreach ($attachments as $index => $file) {
                if ($file->isValid() && !$file->hasMoved()) {
                    try {
                        // Fetch file details before moving
                        $mimeType = $file->getClientMimeType();
                        $originalName = $file->getClientName();
                        $fileExtension = $file->getExtension();
                        $fileSize = $file->getSize();

                        // Generate a new random name and move the file
                        $newName = $file->getRandomName();
                        // $filePath = $uploadDir . $newName;
                        $filePath = base_url('uploads/bids/' . $bidId . '/' . $newName);
                        $file->move($uploadDir, $newName);

                        $attachmentName = $attachmentNames[$index] ?? $originalName;

                        // Prepare data for database insertion
                        $attachmentData = [
                            'bid_id' => $bidId,
                            'file_name' => $originalName,
                            'file_path' => $filePath,
                            'file_type' => $mimeType,
                            'file_size' => $fileSize,
                            'attachment_name' => $attachmentName,
                        ];

                        // Save each file's data to the database
                        if (!$this->bidAttachmentModel->insert($attachmentData)) {
                            return ['success' => false, 'message' => 'Failed to save attachment in database.'];
                        }

                        // Add each file's data to the result array
                        $uploadedFiles[] = $attachmentData;

                    } catch (\Exception $e) {
                        return ['success' => false, 'message' => 'Error while processing file: ' . $e->getMessage()];
                    }
                } else {
                    return ['success' => false, 'message' => 'Invalid or inaccessible file.'];
                }
            }

            // Return success with all uploaded files
            return ['success' => true, 'uploadedFiles' => $uploadedFiles];
        }

        return ['success' => false, 'message' => 'No attachments found in the request.'];
    }

    public function show($id = null)
    {
        // Validate the ID
        if (!$id) {
            return $this->failNotFound('Bid ID is required.');
        }

        // Fetch the bid
        $bid = $this->model->find($id);

        if (!$bid) {
            return $this->failNotFound('Bid not found.');
        }

        $bid['attachments'] = $this->bidAttachmentModel->getRecordsByBidId($bid['id']);
        $bid['status_history'] = $this->bidStatusHistoryModel->getRecordsByBidId($bid['id']);
        $bid['status'] = $this->statusModel->findById($bid['current_status_id']);
        $bid['company'] = $this->companyModel->findById($bid['company_id']);
        $bid['products'] = $this->bidProductModel->getRecordsByBidId($bid['id']);
        $bid['tender'] = $this->tenderModel->getTenderWithDetails($bid['tender_id']);

        // Prepare the response
        $response = [
            'data' => $bid,
        ];

        return $this->respond($response);
    }
}
