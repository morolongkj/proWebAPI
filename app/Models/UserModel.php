<?php

declare (strict_types = 1);

namespace App\Models;

use CodeIgniter\Shield\Entities\User;
use CodeIgniter\Shield\Models\GroupModel as ShieldGroupModel;
use CodeIgniter\Shield\Models\UserModel as ShieldUserModel;

class UserModel extends ShieldUserModel
{
    protected function initialize(): void
    {
        parent::initialize();

        $this->allowedFields = [
             ...$this->allowedFields,

            'title',
            'avator',
            'first_name',
            'last_name',
            'date_of_birth',
            'phone_number',
            'gender',
            'reset_token',
            'reset_token_expires_at',
        ];
    }

    public function findUserById(int $id)
    {
        $user = $this
            ->asArray()
            ->where(['id' => $id])
            ->first();

        if (!$user) {
            return [];
            // throw new Exception('User does not exist for specified id!');
        }

        $userEntityObject = new User($user);

        $groupModel = new ShieldGroupModel();
        $user['groups'] = $groupModel->getForUser($userEntityObject);

        return $user;
    }

    public function findByUsername($username)
    {
        return $this
            ->asArray()
            ->where('username', $username)
            ->first();
    }

    public function setResetToken($userId, $token)
    {
        $this->update($userId, ['reset_token' => $token, 'reset_token_expires_at' => date('Y-m-d H:i:s', strtotime('+1 hour'))]);
    }

    public function findByToken($token)
    {
        return $this
            ->asArray()
            ->where('reset_token', $token)
            ->where('reset_token_expires_at >', date('Y-m-d H:i:s'))
            ->first();
    }

    public function resetPassword($userId, $newPassword)
    {
        $users = auth()->getProvider();

        $user = $users->findById($userId);
        $user->fill([
            'password' => 'secret123',
        ]);
        $users->save($user);

        // $this->update($userId, ['password' => password_hash($newPassword, PASSWORD_DEFAULT), 'reset_token' => null, 'reset_token_expires_at' => null]);
    }

    public function getUsers($limit, $offset, $userParams = [])
    {
        $builder = $this->builder();

        if (!empty($userParams['first_name'])) {
            $builder->like('first_name', $userParams['first_name']);
        }

        if (!empty($userParams['last_name'])) {
            $builder->like('last_name', $userParams['last_name']);
        }

        if (!empty($userParams['email'])) {
            $builder->like('email', $userParams['email']);
        }

        // return $builder->get($limit, $offset)->getResult();
        $users = $builder->get($limit, $offset)->getResult();

        foreach ($users as $user) {
            $single_user = array(
                'id' => $user->id,
                'username' => $user->username,
            );
            $userEntityObject = new User($single_user);

            $groupModel = new ShieldGroupModel();
            $user->groups = $groupModel->getForUser($userEntityObject);

        }

        return $users;
    }

    public function countUsers($userParams = [])
    {
        $builder = $this->builder();
        if (!empty($userParams['first_name'])) {
            $builder->like('first_name', $userParams['first_name']);
        }

        if (!empty($userParams['last_name'])) {
            $builder->like('last_name', $userParams['last_name']);
        }

        if (!empty($userParams['email'])) {
            $builder->like('email', $userParams['email']);
        }

        return $builder->countAllResults();
    }

    public function getAdmins()
    {
        $builder = $this->builder();

        $builder->select('users.*')
            ->join('auth_groups_users', 'users.id = auth_groups_users.user_id')
            ->where('auth_groups_users.group', 'superadmin');

        $admins = $builder->get()->getResult();

        return $admins;
    }
}