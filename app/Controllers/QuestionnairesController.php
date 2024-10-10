<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;

class QuestionnairesController extends ResourceController
{
    // Load the model
    protected $modelName = 'App\Models\QuestionnaireModel';
    protected $format = 'json';

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
        $data = $this->model->getQuestionnairesWithDocuments($page, $perPage, $filters);

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
}
