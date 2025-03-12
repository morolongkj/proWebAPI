<?php
namespace App\Models;

use CodeIgniter\Model;

class QuestionnaireProductModel extends Model
{
    protected $table      = 'questionnaire_products'; // Name of the table
    protected $primaryKey = 'id';                     // Primary key of the table

    // Specify that the primary key is not auto-incrementing (since it's a VARCHAR)
    protected $useAutoIncrement = false;

    // Return type of the results (you can use 'object' if you prefer)
    protected $returnType = 'array';

    // Enable automatic timestamps for created_at and updated_at fields
    protected $useTimestamps = true;

    // Specify the date format for the timestamps
    protected $dateFormat     = 'datetime';
    protected $useSoftDeletes = true; // Enable soft deletes
                                      // List of fields that are allowed to be set during insert/update
    protected $allowedFields = [
        'id',
        'questionnaire_id',
        'product_id',
        'created_at',
        'updated_at',
    ];

    // Validation rules for the fields
    protected $validationRules = [
        'questionnaire_id' => 'required|max_length[255]',
        'product_id'       => 'required|max_length[255]',
    ];

    // Validation messages for custom error messages
    protected $validationMessages = [
        'questionnaire_id' => [
            'required'            => 'Questionnaire ID is required',
            'alpha_numeric_space' => 'Questionnaire ID can only contain alphanumeric characters and spaces',
            'max_length'          => 'Questionnaire ID cannot exceed 255 characters',
        ],
        'product_id'       => [
            'required'            => 'Product ID is required',
            'alpha_numeric_space' => 'Product ID can only contain alphanumeric characters and spaces',
            'max_length'          => 'Product ID cannot exceed 255 characters',
        ],
    ];

    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';

    // Disables validation rules if set to true
    protected $skipValidation = false;

    // If you want to implement custom methods, you can do so below
    public function getProductsByQuestionnaireId($questionnaireId)
    {
        return $this->where('questionnaire_id', $questionnaireId)->findAll();
    }

    public function getQuestionnairesByProductId($productId)
    {
        return $this->where('product_id', $productId)->findAll();
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

    protected $beforeInsert = ['generateUuid'];

    public function findWithDetails(string $id)
    {
        return $this
            ->select('questionnaire_products.*, products.title, products.code, products.description') // Select columns you need
            ->join('products', 'products.id = questionnaire_products.product_id')
            ->where('questionnaire_products.id', $id)
            ->first();
    }
}
