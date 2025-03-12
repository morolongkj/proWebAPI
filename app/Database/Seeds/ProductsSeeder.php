<?php
namespace App\Database\Seeds;

use App\Models\CategoryModel;
use App\Models\ProductModel;
use CodeIgniter\Database\Seeder;

class ProductsSeeder extends Seeder
{
    public function run()
    {
        $categoryModel = new CategoryModel();
        $csvFile       = fopen("data/products.csv", "r");
        // It will automatically read file from /public/data folder.

        $firstline = true;
        while (($data = fgetcsv($csvFile, 2000, ",")) !== false) {
            if (! $firstline) {
                $object   = new ProductModel();
                $category = $categoryModel->getCategoryByTitle($data['3']);

                echo "Inserting " . $data['1'] . " ... \n";
                $object->insert([
                    "id"          => uuid_v4(),
                    "code"        => $data['0'],
                    "title"       => $data['1'],
                    "description" => $data['2'],
                    "category_id" => ! empty($category) ? $category['id'] : null, // Allow null if category is empty
                ]);
            }
            $firstline = false;
        }
        // while (($data = fgetcsv($csvFile, 2000, ",")) !== false) {
        //     if (!$firstline) {
        //         $object = new ProductModel();
        //         $category = $categoryModel->getCategoryByTitle($data['3']);
        //         if (!empty($category)) {
        //             echo "Inserting ".$data['1']." ... \n";
        //             $object->insert([
        //                 "id" => uuid_v4(),
        //                 "code" => $data['0'],
        //                 "title" => $data['1'],
        //                 "description" => $data['2'],
        //                 "category_id" => $category['id'],
        //             ]);
        //         }
        //     }
        //     $firstline = false;
        // }

        fclose($csvFile);
    }
}
