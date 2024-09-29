<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class TenderStatusSeeder extends Seeder
{
    public function run()
    {
        // Array of tender statuses to be seeded
        $data = [
            [
                'id' => uuid_v4(),
                'status' => 'Initiated',
                'description' => 'Tender process has been initiated.',
            ],
            [
                'id' => uuid_v4(),
                'status' => 'Draft',
                'description' => 'Tender is currently in draft status.',
            ],
            [
                'id' => uuid_v4(),
                'status' => 'Verified',
                'description' => 'Tender has been verified and sent for approvals.',
            ],
            [
                'id' => uuid_v4(),
                'status' => 'Approved',
                'description' => 'Tender has been approved for publishing.',
            ],
            [
                'id' => uuid_v4(),
                'status' => 'Rejected',
                'description' => 'Tender has been rejected.',
            ],
            [
                'id' => uuid_v4(),
                'status' => 'Published',
                'description' => 'Tender is currently published and open for bids.',
            ],
            [
                'id' => uuid_v4(),
                'status' => 'Closed',
                'description' => 'Tender process has been closed.',
            ],
            [
                'id' => uuid_v4(),
                'status' => 'Completed',
                'description' => 'Tender process has been closed and completed.',
            ],
        ];

        // Inserting data into the tender_status table
        foreach ($data as $status) {
            $this->db->table('tender_status')->insert($status);
        }
    }
}
