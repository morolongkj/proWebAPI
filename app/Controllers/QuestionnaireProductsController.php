<?php
namespace App\Controllers;

use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\RESTful\ResourceController;

class QuestionnaireProductsController extends ResourceController
{
    protected $modelName = 'App\Models\QuestionnaireProductModel';
    protected $format    = 'json';

    /**
     * Return an array of resource objects, themselves in array format.
     *
     * @return ResponseInterface
     */
    public function index()
    {
        $page    = $this->request->getVar('page') ?? 1;
        $perPage = $this->request->getVar('perPage');
        if (! $perPage) {
            $perPage = null;
        }
        $questionnaire_id = $this->request->getVar('questionnaire_id');
        $product_id       = $this->request->getVar('product_id');
        $where            = [];
        if ($questionnaire_id) {
            $where['questionnaire_id like'] = '%' . $questionnaire_id . '%';
        }
        if ($product_id) {
            $where['product_id like'] = '%' . $product_id . '%';
        }
        $totalQuestionnaireProducts = $this->model->selectCount('id')->where($where)->get()->getRowArray()['id'];
        $questionnaireProducts      = $this->model->where($where)->orderBy('created_at', 'DESC')->paginate($perPage, 'questionnaire_products', $page);
        $data                       = [
            'status' => true,
            'data'   => [
                'questionnaireProducts' => $questionnaireProducts,
                'total'                 => $totalQuestionnaireProducts,
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
        $questionnaireProduct = $this->model->find($id);

        if (! $questionnaireProduct) {
            return $this->failNotFound("Questionnaire Product not found with ID: $id");
        }

        return $this->respond($questionnaireProduct);
    }

    // /**
    //  * Create a new questionnaireProduct (create)
    //  * @return JSON
    //  */
    // public function create()
    // {
    //     $data = $this->request->getJSON(true);

    //     // Validate data before inserting
    //     if (!$this->validate($this->model->validationRules)) {
    //         return $this->failValidationErrors($this->validator->getErrors());
    //     }

    //     if ($this->model->save($data)) {
    //         $newId = $this->model->getInsertID();
    //         // Fetch the newly created questionnaireProduct for the response
    //         $newQuestionnaireProduct = $this->model->findWithDetails($newId);
    //         $response = [
    //             "status" => true,
    //             "message" => "Questionnaire Product created successfully",
    //             "questionnaireProduct" => $newQuestionnaireProduct,
    //         ];
    //         return $this->respondCreated($response);
    //     }

    //     return $this->failServerError('Failed to create questionnaire product.');
    // }
    /**
     * Create new questionnaire products (create multiple)
     * @return JSON
     */
    public function create()
    {
        $data = $this->request->getJSON(true);

        // Validate data
        if (! isset($data['questionnaire_id']) || ! isset($data['product_ids']) || ! is_array($data['product_ids'])) {
            return $this->failValidationErrors('Invalid input. `questionnaire_id` and `product_ids` are required.');
        }

        $questionnaireId = $data['questionnaire_id'];
        $productIds      = $data['product_ids'];

        // Prepare data for batch insert
        $insertData = [];
        foreach ($productIds as $productId) {
            $insertData[] = [
                'id' => uuid_v4(),
                'questionnaire_id' => $questionnaireId,
                'product_id'       => $productId,
            ];
        }

        if ($this->model->insertBatch($insertData)) {
            // Fetch the newly created records for the response
            $newQuestionnaireProducts = $this->model
                ->where('questionnaire_id', $questionnaireId)
                ->findAll();

            foreach($newQuestionnaireProducts as $key => $value) {
                $newQuestionnaireProducts[$key] = $this->model->findWithDetails($value['id']);
            }

            $response = [
                'status'                => true,
                'message'               => 'Questionnaire products created successfully',
                'questionnaireProducts' => $newQuestionnaireProducts,
            ];
            return $this->respondCreated($response);
        }

        return $this->failServerError('Failed to create questionnaire products.');
    }

    /**
     * Update a specific questionnaireProduct (update)
     * @param string $id
     * @return JSON
     */
    public function update($id = null)
    {
        $data                         = $this->request->getJSON(true);
        $existingQuestionnaireProduct = $this->model->find($id);
        if ($existingQuestionnaireProduct) {
            if ($this->model->update($id, $data)) {
                $updatedQuestionnaireProduct = $this->model->find($id);
                return $this->respondUpdated($updatedQuestionnaireProduct);
            }
        } else {
            return $this->failNotFound('Questionnaire Product not found');
        }
        return $this->failServerError('Failed to update questionnaire product.');
    }

    /**
     * Delete a specific questionnaireProduct (delete)
     * @param string $id
     * @return JSON
     */
    public function delete($id = null)
    {
        // Find the questionnaireProduct
        $questionnaireProduct = $this->model->find($id);
        if (! $questionnaireProduct) {
            return $this->failNotFound("Questionnaire Product not found with ID: $id");
        }
        // Delete questionnaireProduct
        if ($this->model->delete($id)) {
            return $this->respondDeleted(['message' => 'Questionnaire Product deleted successfully']);
        }

        return $this->failServerError('Failed to delete questionnaire product.');
    }
}
