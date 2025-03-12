<?php

namespace App\Models;

use CodeIgniter\Model;

class QuestionnaireModel extends Model
{
    // Table name
    protected $table = 'questionnaires';
    // Primary key
    protected $primaryKey = 'id';
    protected $useAutoIncrement = false;
  // Specify the date format for the timestamps
    protected $dateFormat = 'datetime';
    protected $useSoftDeletes = true; // Enable soft deletes
    // Allowed fields
    protected $allowedFields = [
        'id', 'title', 'description', 'status', 'is_open_forever',
        'open_from', 'open_until', 'created_at', 'updated_at',
    ];

    // Return type
    protected $returnType = 'array';

    // Timestamps
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    protected $beforeInsert = ['generateUuid'];

    // Validation rules
    protected $validationRules = [
        'title' => 'required|min_length[3]|max_length[255]',
        'description' => 'permit_empty|string',
        'open_from' => 'permit_empty',
        'open_until' => 'permit_empty|check_open_period[open_from,open_until]',
    ];

    // Custom error messages
    protected $validationMessages = [
        'id' => [
            'required' => 'The ID is required.',
            'is_unique' => 'The ID must be unique.',
            'alpha_numeric' => 'The ID should only contain alphanumeric characters.',
            'min_length' => 'The ID must be at least 8 characters long.',
            'max_length' => 'The ID can be up to 255 characters long.',
        ],
        'title' => [
            'required' => 'The questionnaire title is required.',
            'is_unique' => 'The questionnaire title must be unique.',
            'min_length' => 'The title must be at least 3 characters long.',
            'max_length' => 'The title can be up to 255 characters long.',
        ],
        'open_from' => [
            'valid_date' => 'The open from date must be a valid date (Y-m-d).',
        ],
        'open_until' => [
            'valid_date' => 'The open until date must be a valid date (Y-m-d).',
            'check_open_period' => 'The open until date must be after the open from date.',
        ],
    ];

    // Validation for open period
    protected $skipValidation = false;

    // Custom validation function to check the date range
    protected function check_open_period($field, $data)
    {
        // Check if open_from and open_until are provided and valid
        if (isset($data['open_from']) && isset($data['open_until'])) {
            return strtotime($data['open_from']) <= strtotime($data['open_until']);
        }
        return true;
    }

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
     * Fetches questionnaires along with their associated documents, with filters passed as an object/array.
     */
    public function getQuestionnairesWithDetails($page, $perPage, $filters = [])
    {
        $where = [];

        // Apply filters
        if (!empty($filters['title'])) {
            $where['title like'] = '%' . $filters['title'] . '%';
        }
        if (!empty($filters['description'])) {
            $where['description like'] = '%' . $filters['description'] . '%';
        }

        // Apply status condition
        if (!empty($filters['status'])) {
            if ($filters['status'] == 'open') {
                // Apply additional conditions for "open" status
                $where['status'] = 'open';
                // $this->groupStart()
                //     ->where('open_until >=', date('Y-m-d'))
                //     ->orWhere('is_open_forever', 1)
                //     ->groupEnd();
            } else {
                $where['status like'] = '%' . $filters['status'] . '%';
            }
        }

        // Get total count of questionnaires
        $totalQuestionnaires = $this->selectCount('id')
            ->where($where)
            ->get()
            ->getRowArray()['id'];

        // Get paginated questionnaires
        $questionnaires = $this->where($where)
            ->orderBy('created_at', 'DESC')
            ->paginate($perPage, 'questionnaires', $page);

        // Fetch documents for each questionnaire and append
        foreach ($questionnaires as &$questionnaire) {
            $questionnaire['documents'] = $this->getDocumentsByQuestionnaireId($questionnaire['id']);
            $questionnaire['products'] = $this->getProductsByQuestionnaireId($questionnaire['id']);
        }

        // Return the data in an expected structure
        return [
            'status' => true,
            'data' => [
                'questionnaires' => $questionnaires,
                'total' => $totalQuestionnaires,
            ],
        ];
    }

    /**
     * Helper function to get documents by questionnaire ID.
     */
    private function getDocumentsByQuestionnaireId($questionnaireId)
    {
        return $this->db->table('questionnaire_documents')
            ->select('*')
            ->where('questionnaire_id', $questionnaireId)
            ->get()
            ->getResultArray();
    }

    /**
     * Helper function to get documents by questionnaire ID.
     */
    private function getProductsByQuestionnaireId($questionnaireId)
    {
        return $this->db->table('questionnaire_products')
            ->select('questionnaire_products.*, products.code as code, products.title as title')
            ->join('products', 'products.id = questionnaire_products.product_id')
            ->where('questionnaire_products.questionnaire_id', $questionnaireId)
            ->get()
            ->getResultArray();
    }

    /**
 * Fetches a single questionnaire with its associated documents and products by ID.
 *
 * @param string $id
 * @return array
 */
public function getQuestionnaireWithDetailsById($id)
{
    // Fetch the questionnaire details
    $questionnaire = $this->where('id', $id)->first();

    if (!$questionnaire) {
        return [];
    }

    // Fetch associated documents
    $questionnaire['documents'] = $this->getDocumentsByQuestionnaireId($id);

    // Fetch associated products
    $questionnaire['products'] = $this->getProductsByQuestionnaireId($id);

    return $questionnaire;
}

}
