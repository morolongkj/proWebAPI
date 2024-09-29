<?php

namespace App\Database\Seeds;

use App\Models\CategoryModel;
use CodeIgniter\Database\Seeder;

class CategoriesSeeder extends Seeder
{
    public function run()
    {
        $csvFile = fopen("data/categories.csv", "r");
        // It will automatically read file from /public/data folder.

        $firstline = true;
        while (($data = fgetcsv($csvFile, 2000, ",")) !== false) {
            if (!$firstline) {
                $object = new CategoryModel();
                $object->insert([
                    "id" => uuid_v4(),
                    "code" => $data['0'],
                    "title" => $data['1'],
                    "description" => $data['2'],
                ]);
            }
            $firstline = false;
        }

        fclose($csvFile);
    }
}