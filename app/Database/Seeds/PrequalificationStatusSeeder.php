<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class PrequalificationStatusSeeder extends Seeder
{
    public function run()
    {
        // Array of prequalification statuses to be seeded
        $data = [
            [
                'id' => uuid_v4(),
                'status' => 'Initiated',
                'description' => 'Prequalification process has been initiated.',
            ],
            [
                'id' => uuid_v4(),
                'status' => 'Draft',
                'description' => 'Prequalification is currently in draft status.',
            ],
            [
                'id' => uuid_v4(),
                'status' => 'Submitted',
                'description' => 'Prequalification has been submitted and sent for evaluation.',
            ],
            [
                'id' => uuid_v4(),
                'status' => 'In Review',
                'description' => 'Prequalification has been verified and sent for approvals.',
            ],
            [
                'id' => uuid_v4(),
                'status' => 'Rejected',
                'description' => 'Prequalification has been rejected.',
            ],
                        [
                'id' => uuid_v4(),
                'status' => 'Awaiting Sample',
                'description' => '',
            ],
            [
                'id' => uuid_v4(),
                'status' => 'Testing Sample',
                'description' => '',
            ],
            [
                'id' => uuid_v4(),
                'status' => 'Sample Failed',
                'description' => '',
            ],
            [
                'id' => uuid_v4(),
                'status' => 'Completed',
                'description' => 'Prequalification process has been closed and completed.',
            ],
        ];

        // Inserting data into the prequalification_status table
        foreach ($data as $status) {
            $this->db->table('prequalification_status')->insert($status);
        }
    }
}
