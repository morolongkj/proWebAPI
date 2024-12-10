<?php

namespace App\Controllers;

use App\Models\QuestionnaireSubmissionAttachmentModel;
use App\Models\QuestionnaireSubmissionModel;
use App\Models\QuestionnaireSubmissionStatusHistoryModel;
use App\Models\StatusModel;
use App\Models\CompanyModel;
use CodeIgniter\RESTful\ResourceController;

class QuestionnairesController extends ResourceController
{
    // Load the model
    protected $modelName = 'App\Models\QuestionnaireModel';
    protected $format = 'json';
    protected $questionnaireSubmissionModel;
    protected $questionnaireSubmissionAttachmentModel;
    protected $statusModel;
    protected $questionnaireSubmissionStatusHistoryModel;
    protected $companyModel;

    public function __construct()
    {
        $this->questionnaireSubmissionModel = new QuestionnaireSubmissionModel();
        $this->questionnaireSubmissionAttachmentModel = new QuestionnaireSubmissionAttachmentModel();
        $this->statusModel = new StatusModel();
        $this->companyModel = new CompanyModel();
        $this->questionnaireSubmissionStatusHistoryModel = new QuestionnaireSubmissionStatusHistoryModel();

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
        $page = $this->request->getVar('page') ?? 1;
        $perPage = $this->request->getVar('perPage') ?? null;

        // Get the filters from the request and pass them as an array/object
        $filters = [
            'title' => $this->request->getVar('title'),
            'description' => $this->request->getVar('description'),
            'status' => $this->request->getVar('status'),
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

        if (!$questionnaire) {
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
        if (!$this->validate($this->model->validationRules)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        if ($this->model->save($data)) {
            $newId = $this->model->getInsertID();
            // Fetch the newly created questionnaire for the response
            $newQuestionnaire = $this->model->find($newId);
            $response = [
                "status" => true,
                "message" => "Questionnaire created successfully",
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
        $data = $this->request->getJSON(true);
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
        if (!$questionnaire) {
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

        if (!$userId) {
            return $this->failUnauthorized('User is not authenticated.');
        }

        $data = $this->request->getPost();
        $data['current_status_id'] = $this->statusModel->getStatusIdByTitle('Submitted');

        // Validate data before inserting
        if (!$this->validate($this->questionnaireSubmissionModel->validationRules)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        // Start transaction
        $db = \Config\Database::connect();
        $db->transBegin();

        try {
            if (!$this->questionnaireSubmissionModel->save($data)) {
                throw new \Exception('Failed to save questionnaire submission.');
            }

            $newId = $this->questionnaireSubmissionModel->getInsertID();
            $newSubmission = $this->questionnaireSubmissionModel->find($newId);

            // Handle attachments if provided
            if ($this->request->getFiles() && !empty($newSubmission)) {
                $uploadStatus = $this->addAttachments($newId);
                if (!$uploadStatus['success']) {
                    throw new \Exception($uploadStatus['message']);
                }
            }

            // Update status history
            $status_history = [
                'submission_id' => $newId,
                'status_id' => $data['current_status_id'],
                'changed_by' => $userId,
            ];

            // Add this before save to check the data being passed
            if (!$this->questionnaireSubmissionStatusHistoryModel->validate($status_history)) {
                return $this->failValidationErrors($this->questionnaireSubmissionStatusHistoryModel->errors());
            }

            if (!$this->questionnaireSubmissionStatusHistoryModel->save($status_history)) {
                throw new \Exception('Failed to save status history.');
            }

            // Commit the transaction if all operations are successful
            $db->transCommit();

            // Prepare and return success response
            $response = [
                "status" => true,
                "message" => "Submitted successfully",
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
 * Add attachments for a given questionnaire.
 *
 * @param string $submissionId
 * @return array
 */
// private function addAttachments(string $submissionId)
// {
//     $files = $this->request->getFiles();
//     $uploadedFiles = [];
//     $uploadDir = WRITEPATH . 'uploads/submissions/' . $submissionId . '/';

//     // Ensure the upload directory exists
//     if (!is_dir($uploadDir)) {
//         mkdir($uploadDir, 0755, true);
//     }

//     // Check if files exist under 'attachments'
//     if (isset($files['attachments'])) {
//         // Handle single or multiple file uploads
//         $attachments = is_array($files['attachments']) ? $files['attachments'] : [$files['attachments']];

//         foreach ($attachments as $file) {
//             if ($file->isValid() && !$file->hasMoved()) {
//                 try {
//                     // Fetch MIME type and other details before moving the file
//                     $mimeType = $file->getClientMimeType();
//                     $originalName = $file->getClientName();
//                     $fileExtension = $file->getExtension();

//                     // Move the file to the designated directory
//                     $newName = $file->getRandomName();
//                     $filePath = $uploadDir . $newName;
//                     $file->move($uploadDir, $newName);

//                     // Prepare data for database insertion
//                     $attachmentData = [
//                         'submission_id' => $submissionId,
//                         'file_name' => $originalName,
//                         'file_path' => $filePath,
//                         'file_type' => $mimeType,
//                     ];

//                     // return json_encode($attachmentData);

//                     // Save to the database
//                     if (!$this->questionnaireSubmissionAttachmentModel->insert($attachmentData)) {
//                         return ['success' => false, 'message' => 'Failed to save attachment in database.'];
//                     }

//                     $uploadedFiles[] = $attachmentData;

//                 } catch (\Exception $e) {
//                     return ['success' => false, 'message' => 'Error while processing file: ' . $e->getMessage()];
//                 }
//             } else {
//                 return ['success' => false, 'message' => 'Invalid or inaccessible file.'];
//             }
//         }

//         return ['success' => true, 'uploadedFiles' => $uploadedFiles];
//     }

//     return ['success' => false, 'message' => 'No attachments found in the request.'];
// }

/**
 * Add attachments for a given questionnaire submission.
 *
 * @param string $submissionId
 * @return array
 */
    private function addAttachments(string $submissionId)
    {
        $files = $this->request->getFiles();
        $uploadedFiles = [];
        $uploadDir = WRITEPATH . 'uploads/submissions/' . $submissionId . '/';

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
                        $filePath = base_url('uploads/submissions/' . $submissionId . '/' . $newName);
                        $file->move($uploadDir, $newName);

                        $attachmentName = $attachmentNames[$index] ?? $originalName;

                        // Prepare data for database insertion
                        $attachmentData = [
                            'submission_id' => $submissionId,
                            'file_name' => $originalName,
                            'file_path' => $filePath,
                            'file_type' => $mimeType,
                            'file_size' => $fileSize,
                            'attachment_name' => $attachmentName,
                        ];

                        // Save each file's data to the database
                        if (!$this->questionnaireSubmissionAttachmentModel->insert($attachmentData)) {
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
        $page = $this->request->getVar('page') ?? 1;
        $perPage = $this->request->getVar('perPage');
        if (!$perPage) {
            $perPage = null; // Use default perPage if not provided
        }

        // Get filter parameters from the query string
        $companyId = $this->request->getVar('company_id');
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
        $submissions = $query->paginate($perPage, 'questionnaire_submissions', $page);
        foreach ($submissions as &$submission) {
            $submission['questionnaire'] = $this->model->getQuestionnaireWithDetailsById($submission['questionnaire_id']);
            $submission['attachments'] = $this->questionnaireSubmissionAttachmentModel->getRecordsBySubmissionId($submission['id']);
            $submission['status_history'] = $this->questionnaireSubmissionStatusHistoryModel->getRecordsBySubmissionId($submission['id']);
            $submission['status'] = $this->statusModel->findById($submission['current_status_id']);
            $submission['company'] = $this->companyModel->findById($submission['company_id']);
        }

        $response = [
            'data' => [
                'submissions' => $submissions,
                'total' => $totalSubmissions,
            ],
        ];

        return $this->respond($response);
    }

}
