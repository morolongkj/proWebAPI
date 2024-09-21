<?php

namespace App\Database\Seeds;

use App\Models\UserModel;
use CodeIgniter\Database\Seeder;
use CodeIgniter\Shield\Entities\User;

class UserSeeder extends Seeder
{
    public function run()
    {
        $userModel = new UserModel();

        $users = [
            [
                "username" => "khabomorolong@gmail.com",
                "email" => "khabomorolong@gmail.com",
                "first_name" => "Khabo",
                "last_name" => "Morolong",
                "gender" => "male",
                "password" => "user12345",
                "isAdmin" => true,
            ],
            [
                "username" => "morolongkj@gmail.com",
                "email" => "morolongkj@gmail.com",
                "first_name" => "General",
                "last_name" => "Morolong",
                "gender" => "male",
                "password" => "user12345",
                "isAdmin" => false,
            ],
        ];

        foreach ($users as $user) {
            // Check if the user exists based on email (or any unique identifier)
            $existingUser = $userModel->where('username', $user['username'])->first();
            if (!$existingUser) {
                $userEntityObject = new User($user);
                $userModel->save($userEntityObject);
                $newUser = $userModel->findById($userModel->getInsertID());
                $userModel->addToDefaultGroup($newUser);
                if ($user['isAdmin']) {
                    $newUser->addGroup('admin');
                }
            }
        }
    }
}