<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class StatusSeeder extends Seeder
{
    public function run()
    {
        // Array of tender statuses to be seeded
        $data = [
            [
                'id' => uuid_v4(),
                'title' => 'Initiated',
            ],
            [
                'id' => uuid_v4(),
                'title' => 'Draft',
            ],
            [
                'id' => uuid_v4(),
                'title' => 'Submitted',
            ],
            [
                'id' => uuid_v4(),
                'title' => 'Verified',
            ],
            [
                'id' => uuid_v4(),
                'title' => 'Approved',
            ],
            [
                'id' => uuid_v4(),
                'title' => 'Rejected',
            ],
            [
                'id' => uuid_v4(),
                'title' => 'Published',
            ],
            [
                'id' => uuid_v4(),
                'title' => 'Closed',
            ],
            [
                'id' => uuid_v4(),
                'title' => 'Completed',
            ],
        ];

        // Inserting data into the tender_status table
        foreach ($data as $status) {
            $this->db->table('status')->insert($status);
        }
    }
}
