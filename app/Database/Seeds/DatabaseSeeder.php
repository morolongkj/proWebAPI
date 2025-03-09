<?php
namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $this->call('UserSeeder');
        $this->call('CategoriesSeeder');
        $this->call('DocumentsSeeder');
        $this->call('PrequalificationStatusSeeder');
        $this->call('StatusSeeder');
        $this->call('TenderStatusSeeder');
    }
}
