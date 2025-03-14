<?php
namespace App\Controllers;

use App\Models\CompanyModel;
use App\Models\NotificationModel;
use App\Models\PrequalifiedCompanyModel;
use App\Models\ProductModel;
use App\Models\QuestionnaireSubmissionAttachmentModel;
use App\Models\QuestionnaireSubmissionModel;
use App\Models\QuestionnaireSubmissionProductModel;
use App\Models\QuestionnaireSubmissionStatusHistoryModel;
use App\Models\StatusModel;
use CodeIgniter\RESTful\ResourceController;

class QuestionnairesController extends ResourceController
{
    // Load the model
    protected $modelName = 'App\Models\QuestionnaireModel';
    protected $format    = 'json';
    protected $questionnaireSubmissionModel;
    protected $questionnaireSubmissionAttachmentModel;
    protected $statusModel;
    protected $questionnaireSubmissionStatusHistoryModel;
    protected $companyModel;
    protected $productModel;
    protected $prequalifiedCompanyModel;
    protected $notificationModel;
    protected $questionnaireSubmissionProductModel;

    public function __construct()
    {
        $this->questionnaireSubmissionModel              = new QuestionnaireSubmissionModel();
        $this->questionnaireSubmissionAttachmentModel    = new QuestionnaireSubmissionAttachmentModel();
        $this->statusModel                               = new StatusModel();
        $this->companyModel                              = new CompanyModel();
        $this->productModel                              = new ProductModel();
        $this->questionnaireSubmissionStatusHistoryModel = new QuestionnaireSubmissionStatusHistoryModel();
        $this->prequalifiedCompanyModel                  = new PrequalifiedCompanyModel();
        $this->notificationModel                         = new NotificationModel();
        $this->questionnaireSubmissionProductModel       = new QuestionnaireSubmissionProductModel();

        helper(['form', 'filesystem']);
    }

    /**
     * Get a list of all questionnaires (index)
     * @return JSON
     */
    // public function index()
    // {
    //     $page = $this->request->getVar('page') ?? 1;
    //     $perPage = $this->request->getVar('perPage');
    //     if (!$perPage) {
    //         $perPage = null;
    //     }
    //     $title = $this->request->getVar('title');
    //     $description = $this->request->getVar('description');
    //     $status = $this->request->getVar('status');

    //     $where = [];
    //     if ($title) {
    //         $where['title like'] = '%' . $title . '%';
    //     }
    //     if ($description) {
    //         $where['description like'] = '%' . $description . '%';
    //     }

    //     if ($status) {
    //         if ($status == 'open') {
    //             // Apply additional conditions for "open" status
    //             $where['status'] = 'open';
    //             // $this->model->groupStart() // Start a group for OR conditions
    //             //     ->where('open_until >=', date('Y-m-d'))
    //             //     ->orWhere('is_open_forever', 1)
    //             //     ->groupEnd(); // End group
    //         } else {
    //             $where['status like'] = '%' . $status . '%';
    //         }
    //     }

    //     $totalQuestionnaires = $this->model->selectCount('id')->where($where)->get()->getRowArray()['id'];
    //     $questionnaires = $this->model->where($where)->orderBy('created_at', 'DESC')->paginate($perPage, 'questionnaires', $page);
    //     $data = [
    //         'status' => true,
    //         'data' => [
    //             'questionnaires' => $questionnaires,
    //             'total' => $totalQuestionnaires,
    //         ],
    //     ];

    //     return $this->respond($data);

    // }

    public function index()
    {
        $page    = $this->request->getVar('page') ?? 1;
        $perPage = $this->request->getVar('perPage') ?? null;

        // Get the filters from the request and pass them as an array/object
        $filters = [
            'title'       => $this->request->getVar('title'),
            'description' => $this->request->getVar('description'),
            'status'      => $this->request->getVar('status'),
        ];

        // Fetch the questionnaires and documents using the model, passing the filters
        $data = $this->model->getQuestionnairesWithDetails($page, $perPage, $filters);

        return $this->respond($data);
    }

    /**
     * Get a specific questionnaire by ID (show)
     * @param string $id
     * @return JSON
     */
    public function show($id = null)
    {
        $questionnaire = $this->model->find($id);

        if (! $questionnaire) {
            return $this->failNotFound("Questionnaire not found with ID: $id");
        }

        return $this->respond($questionnaire);
    }

    /**
     * Create a new questionnaire (create)
     * @return JSON
     */
    public function create()
    {
        $data = $this->request->getJSON(true);

        // Validate data before inserting
        if (! $this->validate($this->model->validationRules)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        if ($this->model->save($data)) {
            $newId = $this->model->getInsertID();
            // Fetch the newly created questionnaire for the response
            $newQuestionnaire = $this->model->find($newId);
            $response         = [
                "status"        => true,
                "message"       => "Questionnaire created successfully",
                "questionnaire" => $newQuestionnaire,
            ];
            return $this->respondCreated($response);
        }

        return $this->failServerError('Failed to create questionnaire.');
    }

    /**
     * Update a specific questionnaire (update)
     * @param string $id
     * @return JSON
     */
    public function update($id = null)
    {
        $data                  = $this->request->getJSON(true);
        $existingQuestionnaire = $this->model->find($id);
        if ($existingQuestionnaire) {
            if ($this->model->update($id, $data)) {
                $updatedQuestionnaire = $this->model->find($id);
                return $this->respondUpdated($updatedQuestionnaire);
            }
        } else {
            return $this->failNotFound('Questionnaire not found');
        }
        return $this->failServerError('Failed to update questionnaire.');
    }

    /**
     * Delete a specific questionnaire (delete)
     * @param string $id
     * @return JSON
     */
    public function delete($id = null)
    {
        // Find the questionnaire
        $questionnaire = $this->model->find($id);
        if (! $questionnaire) {
            return $this->failNotFound("Questionnaire not found with ID: $id");
        }
        // Delete questionnaire
        if ($this->model->delete($id)) {
            return $this->respondDeleted(['message' => 'Questionnaire deleted successfully']);
        }

        return $this->failServerError('Failed to delete questionnaire.');
    }

//     /**
//      * Submit a questionnaire with optional attachments.
//      */
//     public function submit()
//     {
//         $user_id = auth()->id();
//         // $data = $this->request->getJSON(true);
//         $data = $this->request->getPost();
//         $data['current_status_id'] = $this->statusModel->getStatusIdByTitle('Submitted');

// // Validate data before inserting
//         if (!$this->validate($this->questionnaireSubmissionModel->validationRules)) {
//             return $this->failValidationErrors($this->validator->getErrors());
//         }

//         if ($this->questionnaireSubmissionModel->save($data)) {
//             $newId = $this->questionnaireSubmissionModel->getInsertID();
//             // Fetch the newly created document for the response
//             $newSubmission = $this->questionnaireSubmissionModel->find($newId);

//             if ($this->request->getFiles() && !empty($newSubmission)) {
//                 $uploadStatus = $this->addAttachments($newId);
//                 // return $this->respond($uploadStatus);
//                 if (!$uploadStatus['success']) {
//                     return $this->failServerError($uploadStatus['message']);
//                 }
//             }

//             // update status history
//             $status_history = array(
//                 'submission_id' => $newId,
//                 'status_id' => $data['current_status_id'],
//                 'changed_by' => $user_id
//             );

//             $this->questionnaireSubmissionStatusHistoryModel->save($status_history);

//             $response = [
//                 "status" => true,
//                 "message" => "Submitted successfully",
//                 "submission" => $newSubmission,
//             ];
//             return $this->respondCreated($response);
//         }

//         return $this->failServerError('Failed to submit a questionnaire.');

//     }

/**
 * Submit a questionnaire with optional attachments.
 */
    public function submit()
    {
        // Retrieve the current user's ID
        $userId = auth()->id();

        if (! $userId) {
            return $this->failUnauthorized('User is not authenticated.');
        }

        $data                      = $this->request->getPost();
        $data['current_status_id'] = $this->statusModel->getStatusIdByTitle('Submitted');
        $products                  = $data['products'];
        unset($data['products']);

        // return $this->respond($products);

        // Validate data before inserting
        if (! $this->validate($this->questionnaireSubmissionModel->validationRules)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        // Start transaction
        $db = \Config\Database::connect();
        $db->transBegin();

        try {
            if (! $this->questionnaireSubmissionModel->save($data)) {
                throw new \Exception('Failed to save questionnaire submission.');
            }

            $newId         = $this->questionnaireSubmissionModel->getInsertID();
            $newSubmission = $this->questionnaireSubmissionModel->find($newId);

            // Validate products array before processing
            if (! empty($products) && is_array($products)) {
                $productDataArray = [];

                foreach ($products as $product) {
                    $productDataArray[] = [
                        "id"                => uuid_v4(),
                        "submission_id"     => $newSubmission['id'],
                        "product_id"        => $product,
                        "current_status_id" => $this->statusModel->getStatusIdByTitle('Submitted'),

                    ];
                }
// return $this->respond($productDataArray);
                // Batch insert for better performance
                if (! empty($productDataArray)) {
                    $this->questionnaireSubmissionProductModel->insertBatch($productDataArray);
                }
            }

            // Handle attachments if provided
            if ($this->request->getFiles() && ! empty($newSubmission)) {
                $uploadStatus = $this->addAttachments($newId);
                if (! $uploadStatus['success']) {
                    throw new \Exception($uploadStatus['message']);
                }
            }

            // Update status history
            $status_history = [
                'submission_id' => $newId,
                'status_id'     => $data['current_status_id'],
                'changed_by'    => $userId,
            ];

            // Add this before save to check the data being passed
            if (! $this->questionnaireSubmissionStatusHistoryModel->validate($status_history)) {
                return $this->failValidationErrors($this->questionnaireSubmissionStatusHistoryModel->errors());
            }

            if (! $this->questionnaireSubmissionStatusHistoryModel->save($status_history)) {
                throw new \Exception('Failed to save status history.');
            }

            // Commit the transaction if all operations are successful
            $db->transCommit();

            // Prepare and return success response
            $response = [
                "status"     => true,
                "message"    => "Submitted successfully",
                "submission" => $newSubmission,
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
 * @param string $submissionId
 * @return array
 */
    private function addAttachments(string $submissionId)
    {
        $files         = $this->request->getFiles();
        $uploadedFiles = [];
        $uploadDir     = WRITEPATH . 'uploads/submissions/' . $submissionId . '/';

        // Ensure the upload directory exists
        if (! is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // Check if files exist under 'attachments'
        if (isset($files['attachments'])) {
            $attachments     = $files['attachments'];
            $attachmentNames = $this->request->getPost('attachment_names');

            // Ensure $attachments is treated as an array for multiple files
            $attachments     = is_array($attachments) ? $attachments : [$attachments];
            $attachmentNames = is_array($attachmentNames) ? $attachmentNames : [$attachmentNames];

            foreach ($attachments as $index => $file) {
                if ($file->isValid() && ! $file->hasMoved()) {
                    try {
                        // Fetch file details before moving
                        $mimeType      = $file->getClientMimeType();
                        $originalName  = $file->getClientName();
                        $fileExtension = $file->getExtension();
                        $fileSize      = $file->getSize();

                        // Generate a new random name and move the file
                        $newName = $file->getRandomName();
                        // $filePath = $uploadDir . $newName;
                        $filePath = base_url('uploads/submissions/' . $submissionId . '/' . $newName);
                        $file->move($uploadDir, $newName);

                        $attachmentName = $attachmentNames[$index] ?? $originalName;

                        // Prepare data for database insertion
                        $attachmentData = [
                            'submission_id'   => $submissionId,
                            'file_name'       => $originalName,
                            'file_path'       => $filePath,
                            'file_type'       => $mimeType,
                            'file_size'       => $fileSize,
                            'attachment_name' => $attachmentName,
                        ];

                        // Save each file's data to the database
                        if (! $this->questionnaireSubmissionAttachmentModel->insert($attachmentData)) {
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

    public function listSubmissions()
    {
        // Get pagination parameters from the query string
        $page    = $this->request->getVar('page') ?? 1;
        $perPage = $this->request->getVar('perPage');
        if (! $perPage) {
            $perPage = null; // Use default perPage if not provided
        }

        // Get filter parameters from the query string
        $companyId       = $this->request->getVar('company_id');
        $questionnaireId = $this->request->getVar('questionnaire_id');

        $searchTerm = $this->request->getVar('searchTerm');

        // Start building the query
        $query = $this->questionnaireSubmissionModel;

        $query = $query->select('questionnaire_submissions.*');
        if ($companyId) {
            $query = $query->where('company_id', $companyId);
        }

        if ($questionnaireId) {
            $query = $query->where('questionnaire_id', $questionnaireId);
        }

        $query = $query->orderBy('created_at', 'DESC');

        $totalSubmissions = $query->countAllResults(false);
        $submissions      = $query->paginate($perPage, 'questionnaire_submissions', $page);
        foreach ($submissions as &$submission) {
            $submission['questionnaire']  = $this->model->getQuestionnaireWithDetailsById($submission['questionnaire_id']);
            $submission['attachments']    = $this->questionnaireSubmissionAttachmentModel->getRecordsBySubmissionId($submission['id']);
            $submission['status_history'] = $this->questionnaireSubmissionStatusHistoryModel->getRecordsBySubmissionId($submission['id']);
            $submission['products']       = $this->questionnaireSubmissionProductModel->getRecordsBySubmissionId($submission['id']);
            $submission['status']         = $this->statusModel->findById($submission['current_status_id']);
            $submission['company']        = $this->companyModel->findById($submission['company_id']);
            $submission['product']        = $this->productModel->findById($submission['product_id']);
        }

        $response = [
            'data' => [
                'submissions' => $submissions,
                'total'       => $totalSubmissions,
            ],
        ];

        return $this->respond($response);
    }

    public function getSubmissionById($id)
    {
        // Validate the ID
        if (! $id) {
            return $this->failNotFound('Submission ID is required.');
        }

        // Fetch the submission
        $submission = $this->questionnaireSubmissionModel->find($id);

        if (! $submission) {
            return $this->failNotFound('Submission not found.');
        }

        // Add related data to the submission
        $submission['questionnaire']  = $this->model->getQuestionnaireWithDetailsById($submission['questionnaire_id']);
        $submission['attachments']    = $this->questionnaireSubmissionAttachmentModel->getRecordsBySubmissionId($submission['id']);
        $submission['status_history'] = $this->questionnaireSubmissionStatusHistoryModel->getRecordsBySubmissionId($submission['id']);
        $submission['status']         = $this->statusModel->findById($submission['current_status_id']);
        $submission['company']        = $this->companyModel->findById($submission['company_id']);
        $submission['product']        = $this->productModel->findById($submission['product_id']);

        // Prepare the response
        $response = [
            'data' => $submission,
        ];

        return $this->respond($response);
    }

/**
 * Update a specific questionnaire submission status and record history.
 *
 * @param string|null $id
 * @return JSON
 */
    public function updateSubmissionStatus($id = null)
    {
        $userId = auth()->id();

        if (! $userId) {
            return $this->failUnauthorized('User is not authenticated.');
        }

        $data = $this->request->getJSON(true);

        // Find the existing submission
        $existingQuestionnaireSubmission = $this->questionnaireSubmissionModel->find($id);
        if (! $existingQuestionnaireSubmission) {
            return $this->failNotFound('Submission not found');
        }

        // Get the new status ID
        $status_id = $this->statusModel->getStatusIdByTitle($data['status']);
        if (! $status_id) {
            return $this->failValidationError('Invalid status provided');
        }

        // Start a database transaction
        $db = \Config\Database::connect();
        $db->transStart();

        // Update the current status in the submission
        $submissionUpdateSuccess = $this->questionnaireSubmissionModel->update($id, ['current_status_id' => $status_id]);

        if (! $submissionUpdateSuccess) {
            $db->transRollback();
            return $this->failServerError('Failed to update submission.');
        }

        // Update status history
        $status_history = [
            'submission_id' => $id,
            'status_id'     => $status_id,
            'changed_by'    => $userId,
        ];

// Add this before save to check the data being passed
        if (! $this->questionnaireSubmissionStatusHistoryModel->validate($status_history)) {
            return $this->failValidationErrors($this->questionnaireSubmissionStatusHistoryModel->errors());
        }

        if (! $this->questionnaireSubmissionStatusHistoryModel->save($status_history)) {
            throw new \Exception('Failed to save status history.');
        }

        // Check if the status is "Qualified" and insert into prequalified companies table
        if ($data['status'] === 'Qualified') {
            $prequalifiedData = [
                'company_id' => $existingQuestionnaireSubmission['company_id'],
                'product_id' => $existingQuestionnaireSubmission['product_id'],
            ];

            // return $this->respond($prequalifiedData);

            // Add this before save to check the data being passed
            if (! $this->prequalifiedCompanyModel->validate($prequalifiedData)) {
                return $this->failValidationErrors($this->prequalifiedCompanyModel->errors());
            }

            if (! $this->prequalifiedCompanyModel->save($prequalifiedData)) {
                throw new \Exception('Failed to insert qualified company record.');
            }

        }

        $this->notificationModel->sendNotification($existingQuestionnaireSubmission['company_id'], $data['subject'], $data['message']);

        // Commit the transaction
        $db->transComplete();

        if ($db->transStatus() === false) {
            return $this->failServerError('Transaction failed.');
        }

        // Fetch and return the updated submission data
        $updatedQuestionnaireSubmission = $this->questionnaireSubmissionModel->find($id);
        return $this->respondUpdated($updatedQuestionnaireSubmission);
    }

    public function updateStatus($id = null)
    {
        $userId = auth()->id();

        if (! $userId) {
            return $this->failUnauthorized('User is not authenticated.');
        }

        $data = $this->request->getJSON(true);

        // Validate request data
        if (
            empty($data['product_ids']) ||
            ! is_array($data['product_ids']) ||
            empty($data['status'])
        ) {
            return $this->failValidationErrors('Invalid data provided.');
        }

        $submissionId = $id;
        $productIds   = $data['product_ids'];
        $status       = $data['status'];

        // Check if the submission exists
        $submission = $this->questionnaireSubmissionModel->find($submissionId);
        if (! $submission) {
            return $this->failNotFound('Submission not found.');
        }

        // Get the status ID from status title
        $statusId = $this->statusModel->getStatusIdByTitle($status);
        if (! $statusId) {
            return $this->failValidationErrors('Invalid status provided.');
        }

        $db = \Config\Database::connect();
        $db->transStart();

        foreach ($productIds as $productId) {
            // Update status for each product in the submission
            $updateSuccess = $this->questionnaireSubmissionProductModel
                ->where('submission_id', $submissionId)
                ->where('product_id', $productId)
                ->set(['current_status_id' => $statusId])
                ->update();

            if (! $updateSuccess) {
                $db->transRollback();
                return $this->failServerError("Failed to update status for product ID: $productId");
            }

            // If status is "Qualified", insert into prequalified company table
            if ($status === 'Qualified') {
                $prequalifiedData = [
                    'company_id' => $submission['company_id'],
                    'product_id' => $productId,
                ];

                if (! $this->prequalifiedCompanyModel->insert($prequalifiedData)) {
                    $db->transRollback();
                    return $this->failServerError("Failed to insert prequalified company record for product ID: $productId");
                }
            }
        }

        $this->notificationModel->sendNotification($submission['company_id'], $data['subject'], $data['message']);

        $db->transComplete();

        if ($db->transStatus() === false) {
            return $this->failServerError('Transaction failed.');
        }

        // Fetch updated data to return
        $updatedSubmissions = $this->questionnaireSubmissionProductModel
            ->where('submission_id', $submissionId)
            ->findAll();

        return $this->respondUpdated([
            'status'  => true,
            'message' => 'Status updated successfully.',
            'data'    => $updatedSubmissions,
        ]);
    }

}
