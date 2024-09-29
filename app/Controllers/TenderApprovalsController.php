<?php

namespace App\Controllers;

use App\Models\TenderStatusHistoryModel;
use App\Models\TenderStatusModel;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\RESTful\ResourceController;

class TenderApprovalsController extends ResourceController
{

    protected $modelName = 'App\Models\TenderApprovalModel';
    protected $format = 'json';

    /**
     * Return an array of resource objects, themselves in array format.
     *
     * @return ResponseInterface
     */
    public function index()
    {
        $page = $this->request->getVar('page') ?? 1;
        $perPage = $this->request->getVar('perPage');
        if (!$perPage) {
            $perPage = null;
        }
        $approval_type = $this->request->getVar('approval_type');

        $where = [];
        if ($approval_type) {
            $where['approval_type like'] = '%' . $approval_type . '%';
        }

        $totalApprovals = $this->model->selectCount('id')->where($where)->get()->getRowArray()['id'];
        $tenderApprovals = $this->model->where($where)->orderBy('created_at', 'DESC')->paginate($perPage, 'tender_approvals', $page);
        $data = [
            'status' => true,
            'data' => [
                'tenderApprovals' => $tenderApprovals,
                'total' => $totalApprovals,
            ],
        ];

        return $this->respond($data);

    }

    /**
     * Create a new resource object, from "posted" parameters.
     *
     * @return ResponseInterface
     */
    public function create()
    {
        $user_id = auth()->id();
        $tenderStatusModel = new TenderStatusModel();
        $tenderStatusHistoryModel = new TenderStatusHistoryModel();
        $data = $this->request->getJSON(true);

// Validate data before inserting
        if (!$this->validate($this->model->validationRules)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        if ($this->model->save($data)) {
            $newId = $this->model->getInsertID();

            if ($newId) {
                // retrieve approvals and rejections by tender
                // Count the number of approved approvals
                $countApproved = $this->model->where('approval_type', 'approved')->countAllResults();
                // Count the number of rejected approvals
                $countRejected = $this->model->where('approval_type', 'rejected')->countAllResults();
                if ($countApproved >= 3) {
                    // update status to approved
                    $tenderStatus = $tenderStatusModel->where('status', 'Approved')->first();
                    if ($tenderStatus) {
                        $tenderStatusId = $tenderStatus['id'];
                        // update status
                        if ($tenderStatusHistoryModel->addHistory($data['tender_id'], $tenderStatusId, $user_id, $data['remarks'])) {
// send notification
                            $response = [
                                "status" => true,
                                "message" => "Tender is approved",
                            ];
                            return $this->respondCreated($response);
                        } else {
                            return $this->failServerError('Failed to cast your vote.');
                        }
                    }

                }

                if ($countRejected >= 3) {
                    // update status to rejected
                    $tenderStatus = $tenderStatusModel->where('status', 'Rejected')->first();
                    if ($tenderStatus) {
                        $tenderStatusId = $tenderStatus['id'];
                        // update status
                        if ($tenderStatusHistoryModel->addHistory($data['tender_id'], $tenderStatusId, $user_id, $data['remarks'])) {
// send notification
                            $response = [
                                "status" => true,
                                "message" => "Tender is rejected",
                            ];

                            return $this->respondCreated($response);
                        } else {
                            return $this->failServerError('Failed to cast your vote.');
                        }
                    }

                }

                $response = [
                    "status" => true,
                    "message" => "Your vote is casted",
                ];
                return $this->respondCreated($response);
            }
        }

        return $this->failServerError('Failed to cast your vote.');

    }

}
