<?php

namespace App\Models;

use CodeIgniter\Model;

class PrequalificationModel extends Model
{
    protected $table = 'prequalifications'; // The name of the table
    protected $primaryKey = 'id'; // Primary key field
    protected $useAutoIncrement = false; // 'id' is a VARCHAR, so auto increment is set to false
    protected $returnType = 'array'; // Return type of results (array or object)
    protected $allowedFields = [
        'id',
        'questionnaire_id',
        'company_id',
        'created_by',
        'current_status_id',
        'extra_data',
        'created_at',
        'updated_at',
    ]; // List of fields that can be inserted or updated

    // Enable automatic timestamps management for created_at and updated_at
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    // Optionally you can use soft deletes
    // protected $useSoftDeletes = true;
    // protected $deletedField  = 'deleted_at';

    // Set validation rules
    protected $validationRules = [

    ];

    // Set custom error messages
    protected $validationMessages = [

    ];

    // Disable callbacks
    protected $skipValidation = false;
    protected $beforeInsert = ['generateUuid'];
    protected $afterInsert = [];
    protected $beforeUpdate = [];
    protected $afterUpdate = [];
    protected $beforeDelete = [];
    protected $afterDelete = [];

    /**
     * Automatically generate UUID v4 for the `id` field if it's not provided.
     *
     * @param array $data
     * @return array
     */
    protected function generateUuid(array $data)
    {
        if (empty($data['data']['id'])) {
            $data['data']['id'] = uuid_v4(); // Generate and set UUID v4
        }
        return $data;
    }

    /**
     * Save the prequalification data to the database and add an entry to the status history.
     *
     * @param array $data
     * @return array|bool Returns the newly created prequalification data or false on failure.
     */
    public function savePrequalification(array $data)
    {
        $db = \Config\Database::connect();
        $db->transStart(); // Start the transaction

        // Validate data before saving
        if (!$this->validate($data)) {
            return $this->errors();
        }
// Retrieve the current_status_id from the prequalification_status table where status is 'Initiated'
        $prequalificationStatusModel = new \App\Models\PrequalificationStatusModel();
        $initiatedStatus = $prequalificationStatusModel->where('status', 'Submitted')->first();

        if (!$initiatedStatus) {
            $db->transRollback(); // Rollback transaction if status is not found
            return false;
        }

        $data['current_status_id'] = $initiatedStatus['id']; // Assuming the column name for the ID is 'id'

        // Save the prequalification data
        if (!$this->save($data)) {
            $db->transRollback(); // Rollback transaction on failure
            return false;
        }

        // Get the ID of the newly created prequalification
        $newPrequalificationId = $this->getInsertID();

        // Prepare the status history data
        $statusHistoryData = [
            'prequalification_id' => $newPrequalificationId,
            'status_id' => $data['current_status_id'],
            'changed_by' => $data['created_by'], // Assuming created_by is the user who changes the status
            'changed_date' => date('Y-m-d H:i:s'),
        ];

        // Insert the status history record
        $prequalificationStatusHistoryModel = new \App\Models\PrequalificationStatusHistoryModel();
        if (!$prequalificationStatusHistoryModel->insert($statusHistoryData)) {
            $db->transRollback(); // Rollback transaction on failure
            return false;
        }


        // Check if the attachments array is present in the payload
        if (isset($data['attachments']) && is_array($data['attachments'])) {
            $prequalificationAttachmentModel = new \App\Models\PrequalificationAttachmentModel();

            foreach ($data['attachments'] as $attachment) {
                // Prepare the attachment data
                $attachmentData = [
                    'prequalification_id' => $newPrequalificationId,
                    'file_name' => $attachment['file_name'], // Assuming attachment data has an 'id'
                    'file_path' => $attachment['file_path'],
                    'file_type' => $attachment['file_type'],
                ];

                // Insert the attachment record
                if (!$prequalificationAttachmentModel->save($attachmentData)) {
                    $db->transRollback(); // Rollback transaction on failure
                    return false;
                }
            }
        }

        // Commit the transaction if everything is successful
        $db->transComplete();

        // Check if the transaction was successful
        if ($db->transStatus() === false) {
            return false;
        }

        // Return the newly created prequalification data
        return $this->find($newPrequalificationId);
    }

    /**
     * Get a prequalification with its current status, products, attachments, and status history.
     *
     * @param int $id
     * @return array|null
     */
    public function getPrequalificationWithDetails($id)
    {
        // Get the main prequalification with its current status
        $prequalification = $this->select('prequalifications.*, prequalification_status.status as current_status, CONCAT(users.first_name," ",users.last_name) as created_by_name')
            ->join('prequalification_status', 'prequalification_status.id = prequalifications.current_status_id', 'left')
            ->join('users', 'users.id = prequalifications.created_by', 'left')
            ->where('prequalifications.id', $id)
            ->first();

        if (!$prequalification) {
            return null; // Return null if no prequalification is found
        }

        // Load related prequalification products
        $prequalificationProductModel = new \App\Models\PrequalificationProductModel();
        $prequalification['products'] = $prequalificationProductModel->where('prequalification_id', $id)->findAll();

        // Load related prequalification attachments
        $prequalificationAttachmentModel = new \App\Models\PrequalificationAttachmentModel();
        $prequalification['attachments'] = $prequalificationAttachmentModel->where('prequalification_id', $id)->findAll();

        // Load prequalification status history
        $prequalificationStatusHistoryModel = new \App\Models\PrequalificationStatusHistoryModel();
        $prequalification['status_history'] = $prequalificationStatusHistoryModel
            ->select('prequalification_status_history.*, prequalification_status.status as status, CONCAT(users.first_name," ",users.last_name) as changed_by_name')
            ->join('prequalification_status', 'prequalification_status.id = prequalification_status_history.status_id', 'left')
            ->join('users', 'users.id = prequalification_status_history.changed_by', 'left')
            ->where('prequalification_status_history.prequalification_id', $id)
            ->findAll();

        return $prequalification;
    }

    /**
     * Get paginated prequalifications with their details, including products, attachments, and status history.
     *
     * @param array $filters
     * @param int|null $perPage
     * @param int $page
     * @return array
     */
    public function getPrequalificationsWithDetails(array $filters, ?int $perPage, int $page)
    {
        // Building the query with filters
        $query = $this->select('prequalifications.*, prequalification_status.status as current_status, CONCAT(users.first_name," ",users.last_name) as created_by_name')
            ->join('prequalification_status', 'prequalification_status.id = prequalifications.current_status_id', 'left')
            ->join('users', 'users.id = prequalifications.created_by', 'left')
            ->orderBy('prequalifications.created_at', 'DESC');

        $prequalificationStatusModel = new \App\Models\PrequalificationStatusModel();

        if (!empty($filters['reference_number'])) {
            $query->like('prequalifications.reference_number', $filters['reference_number']);
        }
        if (!empty($filters['title'])) {
            $query->like('prequalifications.title', $filters['title']);
        }
        if (!empty($filters['description'])) {
            $query->like('prequalifications.description', $filters['description']);
        }
        if (!empty($filters['opening_date'])) {
            $query->like('prequalifications.opening_date', $filters['opening_date']);
        }
        if (!empty($filters['closing_date'])) {
            $query->like('prequalifications.closing_date', $filters['closing_date']);
        }
        if (!empty($filters['current_status_id'])) {
            $query->like('prequalifications.current_status_id', $filters['current_status_id']);
        }
        if (!empty($filters['status'])) {
            $status = $prequalificationStatusModel->where('status', $filters['status'])->first();
            if ($status) {
                $query->like('prequalifications.current_status_id', $status['id']);
            }
        }

        // Fetch paginated results
        $totalPrequalifications = $query->countAllResults(false);
        $prequalifications = $query->paginate($perPage, 'prequalifications', $page);
        // $totalPrequalifications = $query->countAllResults(false); // Count all results without resetting query

        // Load related entities for each prequalification
        $prequalificationProductModel = new \App\Models\PrequalificationProductModel();
        $prequalificationAttachmentModel = new \App\Models\PrequalificationAttachmentModel();
        $prequalificationStatusHistoryModel = new \App\Models\PrequalificationStatusHistoryModel();
        $prequalificationApprovalModel = new \App\Models\PrequalificationApprovalModel();

        foreach ($prequalifications as &$prequalification) {
            // Load related prequalification products
            // $prequalification['products'] = $prequalificationProductModel->where('prequalification_id', $prequalification['id'])->findAll();
            $prequalification['products'] = $prequalificationProductModel
                ->select('prequalification_products.*, products.title, products.code, products.description') // Select columns you need
                ->join('products', 'products.id = prequalification_products.product_id')
                ->where('prequalification_products.prequalification_id', $prequalification['id'])
                ->findAll();

            // Load related prequalification attachments
            $prequalification['attachments'] = $prequalificationAttachmentModel->where('prequalification_id', $prequalification['id'])->findAll();

            // Load prequalification status history
            $prequalification['status_history'] = $prequalificationStatusHistoryModel
                ->select('prequalification_status_history.*, prequalification_status.status as status, CONCAT(users.first_name," ",users.last_name) as changed_by_name')
                ->join('prequalification_status', 'prequalification_status.id = prequalification_status_history.status_id', 'left')
                ->join('users', 'users.id = prequalification_status_history.changed_by', 'left')
                ->where('prequalification_status_history.prequalification_id', $prequalification['id'])
                ->findAll();

            // Count the number of approved approvals
            $prequalification['total_approvals'] = $prequalificationApprovalModel->where(['approval_type' => 'approved', 'prequalification_id' => $prequalification['id']])->countAllResults();
// Count the number of rejected approvals
            $prequalification['total_rejections'] = $prequalificationApprovalModel->where(['approval_type' => 'rejected', 'prequalification_id' => $prequalification['id']])->countAllResults();

        }

        // Return the result with pagination data
        return [
            'prequalifications' => $prequalifications,
            'total' => $totalPrequalifications,
            'perPage' => $perPage,
            'currentPage' => $page,
            'totalPages' => ($perPage > 0) ? ceil($totalPrequalifications / $perPage) : 1,
        ];
    }

    // Method to update prequalification status and log status change
    public function updateStatus($prequalificationId, $statusName)
    {
        // Start a database transaction
        $db = \Config\Database::connect();
        $db->transStart();
        $prequalificationStatusModel = new \App\Models\PrequalificationStatusModel();
        $status = $prequalificationStatusModel->getStatusByName($statusName);
        // Update the prequalification status
        $this->update($prequalificationId, ['current_status_id' => $status['id']]);

        // Log the status history
        $prequalificationStatusHistoryModel = new \App\Models\PrequalificationStatusHistoryModel();
        $statusHistoryData = [
            'prequalification_id' => $prequalificationId,
            'status_id' => $status['id'],
            'changed_by' => user()->id ?? null, // Use the authenticated user or set to null if not available
            'changed_date' => date('Y-m-d H:i:s'),
        ];

        $prequalificationStatusHistoryModel->insert($statusHistoryData);

        // Complete the transaction
        $db->transComplete();

        return $db->transStatus(); // Return true if successful, false otherwise
    }

}
