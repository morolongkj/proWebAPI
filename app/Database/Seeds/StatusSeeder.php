<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class StatusSeeder extends Seeder
{
    public function run()
    {
        $data = [
            ['title' => 'Initiated'],
            ['title' => 'Draft'],
            ['title' => 'Submitted'],
            ['title' => 'Verified'],
            ['title' => 'Approved'],
            ['title' => 'Rejected'],
            ['title' => 'Published'],
            ['title' => 'Closed'],
            ['title' => 'Completed'],
            ['title' => 'Sample Passed'],
            ['title' => 'Sample Failed'],
            ['title' => 'Qualified'],
        ];

        // Load the database connection
        $db = \Config\Database::connect();
        $builder = $db->table('status');

        foreach ($data as $status) {
            // Check if the title already exists in the table
            $exists = $builder->where('title', $status['title'])->countAllResults();

            if ($exists == 0) {
                // Add UUID and insert if not exists
                $status['id'] = uuid_v4();
                $builder->insert($status);
            }
        }
    }
}
