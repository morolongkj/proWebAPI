<?php
namespace App\Models;

use CodeIgniter\Model;

class QuestionnaireSubmissionProductModel extends Model
{
    protected $table      = 'questionnaire_submission_products'; // The table name
    protected $primaryKey = 'id';                                // The primary key of the table

    protected $useAutoIncrement = false; // Since 'id' is VARCHAR, no auto-increment

    protected $returnType     = 'array'; // Return results as array
    protected $useSoftDeletes = false;   // No soft deletes for this table

    protected $allowedFields = [
        'id',
        'submission_id',
        'product_id',
        'current_status_id',
        'created_at',
        'updated_at',
    ];

    protected $validationRules = [
        'submission_id' => 'required|max_length[255]',
        'product_id'    => 'required|max_length[255]',
    ];

    protected $validationMessages = [
        'submission_id' => [
            'required'   => 'The submission ID is required.',
            'max_length' => 'The submission ID cannot exceed 255 characters.',
        ],
        'product_id'    => [
            'required'   => 'The file name is required.',
            'max_length' => 'The file name cannot exceed 255 characters.',
        ],
    ];

    protected $beforeInsert = ['generateUuid'];

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
     * Retrieve a single attachment record by its ID.
     *
     * @param string $id
     * @return array|null
     */
    public function getRecordById(string $id): ?array
    {
        return $this->where('id', $id)->first();
    }

    /**
     * Retrieve a list of attachments for a specific submission ID.
     *
     * @param string $submissionId
     * @return array
     */
    public function getRecordsBySubmissionId(string $submissionId): array
    {
        return $this->select('
            questionnaire_submission_products.*,
            products.title AS product_name,
            products.description AS product_description,
            status.title AS status
        ')
            ->join('products', 'products.id = questionnaire_submission_products.product_id', 'inner')
            ->join('status', 'status.id = questionnaire_submission_products.current_status_id', 'inner')
            ->where('questionnaire_submission_products.submission_id', $submissionId)
            ->orderBy('questionnaire_submission_products.created_at', 'ASC')
            ->findAll();
    }

    // public function getRecordsBySubmissionId(string $submissionId): array
    // {
    //     return $this->where('submission_id', $submissionId)
    //         ->orderBy('created_at', 'ASC') // Optional: order by creation time
    //         ->findAll();
    // }
}
