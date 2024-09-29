<?php

namespace App\Database\Seeds;

use App\Models\DocumentModel;
use CodeIgniter\Database\Seeder;

class DocumentsSeeder extends Seeder
{
    public function run()
    {
        $csvFile = fopen("data/documents.csv", "r");
        // It will automatically read file from /public/data folder.

        $firstline = true;
        while (($data = fgetcsv($csvFile, 2000, ",")) !== false) {
            if (!$firstline) {
                $object = new DocumentModel();
                $object->insert([
                    "id" => uuid_v4(),
                    "title" => $data['0'],
                    "description" => $data['1'],
                ]);
            }
            $firstline = false;
        }

        fclose($csvFile);
    }
}