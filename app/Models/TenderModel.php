<?php

namespace App\Models;

use CodeIgniter\Model;

class TenderModel extends Model
{
    protected $table = 'tenders'; // The name of the table
    protected $primaryKey = 'id'; // Primary key field
    protected $useAutoIncrement = false; // 'id' is a VARCHAR, so auto increment is set to false
    protected $returnType = 'array'; // Return type of results (array or object)
    protected $allowedFields = [
        'id',
        'reference_number',
        'title',
        'description',
        'created_by',
        'current_status_id',
        'opening_date',
        'opening_time',
        'closing_date',
        'closing_time',
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
        'reference_number' => 'required|max_length[100]',
        'title' => 'required|max_length[255]',
        'current_status_id' => 'permit_empty|max_length[255]',
        'opening_date' => 'required|valid_date',
        'opening_time' => 'required|valid_time[opening_time]',
        'closing_date' => 'required|valid_date|check_open_period[opening_date,closing_date]',
        'closing_time' => 'required|valid_time[closing_time]',
    ];

    // Set custom error messages
    protected $validationMessages = [
        'id' => [
            'required' => 'The ID is required.',
            'alpha_numeric' => 'The ID must contain only alphanumeric characters.',
            'max_length' => 'The ID cannot exceed 255 characters.',
        ],
        'reference_number' => [
            'required' => 'The reference number is required.',
            'alpha_numeric_space' => 'The reference number must contain only alphanumeric characters and spaces.',
            'max_length' => 'The reference number cannot exceed 100 characters.',
            'is_unique' => 'The reference number already exists in the database.',
        ],
        'title' => [
            'required' => 'The title is required.',
            'max_length' => 'The title cannot exceed 255 characters.',
        ],
        'created_by' => [
            'required' => 'The created by field is required.',
            'integer' => 'The created by field must be an integer.',
        ],
        'opening_date' => [
            'required' => 'The opening date is required.',
            'valid_date' => 'The opening date must be a valid date.',
        ],
        'opening_time' => [
            'required' => 'The opening time is required.',
            'valid_time' => 'The opening time must be a valid time.',
        ],
        'closing_date' => [
            'required' => 'The closing date is required.',
            'valid_date' => 'The closing date must be a valid date.',
            'Validation.check_open_period' => 'The closing date must be after the opening date.',
        ],
        'closing_time' => [
            'required' => 'The closing time is required.',
            'valid_time' => 'The closing time must be a valid time.',
        ],
    ];

    // Disable callbacks
    protected $skipValidation = false;
    protected $beforeInsert = ['generateUuid'];
    protected $afterInsert = [];
    protected $beforeUpdate = [];
    protected $afterUpdate = [];
    protected $beforeDelete = [];
    protected $afterDelete = [];
    // Custom validation function to check the date range
    // protected function check_open_period($field, $data)
    // {
    //     // Check if open_from and open_until are provided and valid
    //     if (isset($data['opening_date']) && isset($data['closing_date'])) {
    //         return strtotime($data['opening_date']) <= strtotime($data['closing_date']);
    //     }
    //     return true;
    // }

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
     * Save the tender data to the database and add an entry to the status history.
     *
     * @param array $data
     * @return array|bool Returns the newly created tender data or false on failure.
     */
    public function saveTender(array $data)
    {
        $db = \Config\Database::connect();
        $db->transStart(); // Start the transaction

        // Validate data before saving
        if (!$this->validate($data)) {
            return $this->errors();
        }
// Retrieve the current_status_id from the tender_status table where status is 'Initiated'
        $tenderStatusModel = new \App\Models\TenderStatusModel();
        $initiatedStatus = $tenderStatusModel->where('status', 'Draft')->first();

        if (!$initiatedStatus) {
            $db->transRollback(); // Rollback transaction if status is not found
            return false;
        }

        $data['current_status_id'] = $initiatedStatus['id']; // Assuming the column name for the ID is 'id'

        // Save the tender data
        if (!$this->save($data)) {
            $db->transRollback(); // Rollback transaction on failure
            return false;
        }

        // Get the ID of the newly created tender
        $newTenderId = $this->getInsertID();

        // Prepare the status history data
        $statusHistoryData = [
            'tender_id' => $newTenderId,
            'status_id' => $data['current_status_id'],
            'changed_by' => $data['created_by'], // Assuming created_by is the user who changes the status
            'changed_date' => date('Y-m-d H:i:s'),
        ];

        // Insert the status history record
        $tenderStatusHistoryModel = new \App\Models\TenderStatusHistoryModel();
        if (!$tenderStatusHistoryModel->insert($statusHistoryData)) {
            $db->transRollback(); // Rollback transaction on failure
            return false;
        }

        // Check if the products array is present in the payload
        if (isset($data['products']) && is_array($data['products'])) {
            $tenderProductModel = new \App\Models\TenderProductModel();

            foreach ($data['products'] as $product) {
                // Prepare the product data
                $productData = [
                    'tender_id' => $newTenderId,
                    'product_id' => $product['product_id'], // Assuming product data has an 'id'
                    'quantity' => $product['quantity'], // Assuming quantity is provided
                ];

                // Insert the product record
                if (!$tenderProductModel->save($productData)) {
                    $db->transRollback(); // Rollback transaction on failure
                    return false;
                }
            }
        }

        // Check if the attachments array is present in the payload
        if (isset($data['attachments']) && is_array($data['attachments'])) {
            $tenderAttachmentModel = new \App\Models\TenderAttachmentModel();

            foreach ($data['attachments'] as $attachment) {
                // Prepare the attachment data
                $attachmentData = [
                    'tender_id' => $newTenderId,
                    'file_name' => $attachment['file_name'], // Assuming attachment data has an 'id'
                    'file_path' => $attachment['file_path'],
                    'file_type' => $attachment['file_type'],
                ];

                // Insert the attachment record
                if (!$tenderAttachmentModel->save($attachmentData)) {
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

        // Return the newly created tender data
        return $this->find($newTenderId);
    }

    /**
     * Get a tender with its current status, products, attachments, and status history.
     *
     * @param int $id
     * @return array|null
     */
    public function getTenderWithDetails($id)
    {
        // Get the main tender with its current status
        $tender = $this->select('tenders.*, tender_status.status as current_status, CONCAT(users.first_name," ",users.last_name) as created_by_name')
            ->join('tender_status', 'tender_status.id = tenders.current_status_id', 'left')
            ->join('users', 'users.id = tenders.created_by', 'left')
            ->where('tenders.id', $id)
            ->first();

        if (!$tender) {
            return null; // Return null if no tender is found
        }

        // Load related tender products
        $tenderProductModel = new \App\Models\TenderProductModel();
        $tender['products'] = $tenderProductModel->where('tender_id', $id)->findAll();

        // Load related tender attachments
        $tenderAttachmentModel = new \App\Models\TenderAttachmentModel();
        $tender['attachments'] = $tenderAttachmentModel->where('tender_id', $id)->findAll();

        // Load tender status history
        $tenderStatusHistoryModel = new \App\Models\TenderStatusHistoryModel();
        $tender['status_history'] = $tenderStatusHistoryModel
            ->select('tender_status_history.*, tender_status.status as status, CONCAT(users.first_name," ",users.last_name) as changed_by_name')
            ->join('tender_status', 'tender_status.id = tender_status_history.status_id', 'left')
            ->join('users', 'users.id = tender_status_history.changed_by', 'left')
            ->where('tender_status_history.tender_id', $id)
            ->findAll();

        return $tender;
    }

    /**
     * Get paginated tenders with their details, including products, attachments, and status history.
     *
     * @param array $filters
     * @param int|null $perPage
     * @param int $page
     * @return array
     */
    public function getTendersWithDetails(array $filters, ?int $perPage, int $page)
    {
        // Building the query with filters
        $query = $this->select('tenders.*, tender_status.status as current_status, CONCAT(users.first_name," ",users.last_name) as created_by_name')
            ->join('tender_status', 'tender_status.id = tenders.current_status_id', 'left')
            ->join('users', 'users.id = tenders.created_by', 'left')
            ->orderBy('tenders.created_at', 'DESC');

        $tenderStatusModel = new \App\Models\TenderStatusModel();

        if (!empty($filters['reference_number'])) {
            $query->like('tenders.reference_number', $filters['reference_number']);
        }
        if (!empty($filters['title'])) {
            $query->like('tenders.title', $filters['title']);
        }
        if (!empty($filters['description'])) {
            $query->like('tenders.description', $filters['description']);
        }
        if (!empty($filters['opening_date'])) {
            $query->like('tenders.opening_date', $filters['opening_date']);
        }
        if (!empty($filters['closing_date'])) {
            $query->like('tenders.closing_date', $filters['closing_date']);
        }
        if (!empty($filters['current_status_id'])) {
            $query->like('tenders.current_status_id', $filters['current_status_id']);
        }
        if (!empty($filters['status'])) {
            $status = $tenderStatusModel->where('status', $filters['status'])->first();
            if ($status) {
                $query->like('tenders.current_status_id', $status['id']);
            }
        }

        // Fetch paginated results
        $totalTenders = $query->countAllResults(false);
        $tenders = $query->paginate($perPage, 'tenders', $page);
        // $totalTenders = $query->countAllResults(false); // Count all results without resetting query

        // Load related entities for each tender
        $tenderProductModel = new \App\Models\TenderProductModel();
        $tenderAttachmentModel = new \App\Models\TenderAttachmentModel();
        $tenderStatusHistoryModel = new \App\Models\TenderStatusHistoryModel();
        $tenderApprovalModel = new \App\Models\TenderApprovalModel();

        foreach ($tenders as &$tender) {
            // Load related tender products
            // $tender['products'] = $tenderProductModel->where('tender_id', $tender['id'])->findAll();
            $tender['products'] = $tenderProductModel
                ->select('tender_products.*, products.title, products.code, products.description') // Select columns you need
                ->join('products', 'products.id = tender_products.product_id')
                ->where('tender_products.tender_id', $tender['id'])
                ->findAll();

            // Load related tender attachments
            $tender['attachments'] = $tenderAttachmentModel->where('tender_id', $tender['id'])->findAll();

            // Load tender status history
            $tender['status_history'] = $tenderStatusHistoryModel
                ->select('tender_status_history.*, tender_status.status as status, CONCAT(users.first_name," ",users.last_name) as changed_by_name')
                ->join('tender_status', 'tender_status.id = tender_status_history.status_id', 'left')
                ->join('users', 'users.id = tender_status_history.changed_by', 'left')
                ->where('tender_status_history.tender_id', $tender['id'])
                ->findAll();

            // Count the number of approved approvals
            $tender['total_approvals'] = $tenderApprovalModel->where(['approval_type' => 'approved', 'tender_id' => $tender['id']])->countAllResults();
// Count the number of rejected approvals
            $tender['total_rejections'] = $tenderApprovalModel->where(['approval_type' => 'rejected', 'tender_id' => $tender['id']])->countAllResults();

        }

        // Return the result with pagination data
        return [
            'tenders' => $tenders,
            'total' => $totalTenders,
            'perPage' => $perPage,
            'currentPage' => $page,
            'totalPages' => ($perPage > 0) ? ceil($totalTenders / $perPage) : 1,
        ];
    }

    // Method to update tender status and log status change
    public function updateStatus($tenderId, $statusName)
    {
        // Start a database transaction
        $db = \Config\Database::connect();
        $db->transStart();
        $tenderStatusModel = new \App\Models\TenderStatusModel();
        $status = $tenderStatusModel->getStatusByName($statusName);
        // Update the tender status
        $this->update($tenderId, ['current_status_id' => $status['id']]);

        // Log the status history
        $tenderStatusHistoryModel = new \App\Models\TenderStatusHistoryModel();
        $statusHistoryData = [
            'tender_id' => $tenderId,
            'status_id' => $status['id'],
            'changed_by' => user()->id ?? null, // Use the authenticated user or set to null if not available
            'changed_date' => date('Y-m-d H:i:s'),
        ];

        $tenderStatusHistoryModel->insert($statusHistoryData);

        // Complete the transaction
        $db->transComplete();

        return $db->transStatus(); // Return true if successful, false otherwise
    }

}
