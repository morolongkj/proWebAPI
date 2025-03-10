<?php
namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\Shield\Models\GroupModel as ShieldGroupModel;

class UsersController extends ResourceController
{
    protected $modelName = 'App\Models\UserModel';

    // public function index()
    // {
    //     $users = $this->model->findAll();
    //     return $this->respond($users);
    // }
    public function index()
    {
        // Get current page from request
        $page = $this->request->getVar('page') ?? 1;

// Get perPage from request or set default to fetch all
        $perPage = $this->request->getVar('perPage');

// If perPage is not defined, set it to null to fetch all records
        if (! $perPage) {
            $perPage = null;
        }

        $firstName = $this->request->getVar('first_name');
        $lastName  = $this->request->getVar('last_name');
        $email     = $this->request->getVar('email');
        $gender    = $this->request->getVar('gender');

// Build where condition
        $where = [];
        if ($firstName) {
            $where['first_name like'] = '%' . $firstName . '%';
        }
        if ($lastName) {
            $where['last_name like'] = '%' . $lastName . '%';
        }
        if ($email) {
            $where['username like'] = '%' . $email . '%';
        }

        if ($gender) {
            $where['gender like'] = '%' . $gender . '%';
        }

        // Get total users count
        $totalUsers = $this->model->selectCount('id')->where($where)->get()->getRowArray()['id'];
        // Get users with pagination
        $users = $this->model->where($where)->paginate($perPage, 'users', $page);
        foreach ($users as $user) {
            $groupModel = new ShieldGroupModel();
            // $user->unBan();
            $user->roles = $groupModel->getForUser($user);
        }
        // Get data to be passed to the view
        $data = [
            'status' => true,
            'data'   => [
                'users' => $users,
                'total' => $totalUsers,
            ],
        ];

        return $this->respond($data);
    }

    public function show($id = null)
    {
        $user = $this->model->find($id);

        if ($user) {
            $groupModel  = new ShieldGroupModel();
            $user->roles = $groupModel->getForUser($user);
            return $this->respond($user);
        } else {
            return $this->failNotFound('User not found');
        }
    }

    public function create()
    {
        $data = $this->request->getJSON(true);
        $user = $this->model->save($data);
        if ($user) {
            // return $this->respondCreated($user);
            $response = [
                "status"  => true,
                "message" => "User created successfully",
                "user"    => $user,
            ];
            return $this->respondCreated($response);

        } else {
            return $this->failValidationErrors($this->model->getValidationErrors());
        }
    }

    public function update($id = null)
    {
        $user = $this->model->find($id);

        if ($user) {
            $data = $this->request->getJSON(true);

            if ($this->model->update($id, $data)) {
                $roles = $this->request->getVar("roles") ?? [];
                if ($roles) {
                    $groupModel  = new ShieldGroupModel();
                    $user->roles = $groupModel->getForUser($user);
                    foreach ($user->roles as $role) {
                        $user->removeGroup($role);
                    }
                    foreach ($roles as $role) {
                        $user->addGroup($role);
                    }
                }
                $updatedUser = $this->model->find($id);

                return $this->respond([
                    "status"  => true,
                    "message" => "User is updated successfully",
                    "user"    => $updatedUser,
                ], 200);
            } else {
                return $this->fail('Failed to update user');
            }
        } else {
            return $this->failNotFound('User not found');
        }
    }

    public function delete($id = null)
    {
        $user = $this->model->find($id);
        if ($user) {
            $this->model->delete($id);
            return $this->respondDeleted(['message' => 'User deleted']);
        } else {
            return $this->failNotFound('User not found');
        }
    }

    public function ban($id = null)
    {
        $user = $this->model->find($id);
        if ($user) {
            $user->ban();
            return $this->respond(['message' => 'User banned']);
        } else {
            return $this->failNotFound('User not found');
        }
    }

    public function unban($id = null)
    {
        $user = $this->model->find($id);
        if ($user) {
            $user->unBan();
            return $this->respond(['message' => 'User unbanned']);
        } else {
            return $this->failNotFound('User not found');
        }
    }

    public function addRole($id = null)
    {
        $user  = $this->model->find($id);
        $group = $this->request->getVar("role");
        if ($user && $group) {
            if (! $user->inGroup($group)) {
                $user->addGroup($group);
                return $this->respond(['message' => 'User added to the group']);
            }
            return $this->respond(['message' => 'User already in group']);
        } else {
            return $this->failNotFound('User/Role not found');
        }
    }

    public function removeRole($id = null)
    {
        $user  = $this->model->find($id);
        $group = $this->request->getVar("role");
        if ($user && $group) {
            if ($user->inGroup($group)) {
                $user->removeGroup($group);
                return $this->respond(['message' => 'User is removed from the group']);
            }
            return $this->respond(['message' => 'User is not in a specified group']);
        } else {
            return $this->failNotFound('User/Role not found');
        }
    }

}
