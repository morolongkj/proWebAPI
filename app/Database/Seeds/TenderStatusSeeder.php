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
                'status' => 'Initiated',
                'description' => 'Tender process has been initiated.',
            ],
            [
                'status' => 'Draft',
                'description' => 'Tender is currently in draft status.',
            ],
            [
                'status' => 'Submitted',
                'description' => 'Tender has been submitted and sent for verification.',
            ],
            [
                'status' => 'Verified',
                'description' => 'Tender has been verified and sent for approvals.',
            ],
            [
                'status' => 'Approved',
                'description' => 'Tender has been approved for publishing.',
            ],
            [
                'status' => 'Rejected',
                'description' => 'Tender has been rejected.',
            ],
            [
                'status' => 'Published',
                'description' => 'Tender is currently published and open for bids.',
            ],
            [
                'status' => 'Closed',
                'description' => 'Tender process has been closed.',
            ],
            [
                'status' => 'Completed',
                'description' => 'Tender process has been closed and completed.',
            ],
        ];

        // Load the database connection
        $db = \Config\Database::connect();
        $builder = $db->table('tender_status');

        // Insert data only if status doesn't already exist
        foreach ($data as $status) {
            $exists = $builder->where('status', $status['status'])->countAllResults();

            if ($exists == 0) {
                $status['id'] = uuid_v4(); // Add UUID only before insertion
                $builder->insert($status);
            }
        }
    }
}
