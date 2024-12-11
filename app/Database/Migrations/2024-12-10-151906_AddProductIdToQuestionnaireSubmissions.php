<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddProductIdToQuestionnaireSubmissions extends Migration
{
    public function up()
    {
        // Adding the product_id field
        $this->forge->addColumn('questionnaire_submissions', [
            'product_id' => [
                'type' => 'VARCHAR',
                'constraint' => '255',
                'null' => true,
                'after' => 'current_status_id', // Place it after current_status_id
            ],
        ]);

        // Adding the foreign key constraint
        $this->forge->addForeignKey('product_id', 'products', 'id', 'RESTRICT', 'CASCADE');
    }

    public function down()
    {
        // Dropping the foreign key constraint
        $this->forge->dropForeignKey('questionnaire_submissions', 'questionnaire_submissions_product_id_foreign');

        // Dropping the product_id field
        $this->forge->dropColumn('questionnaire_submissions', 'product_id');
    }
}
