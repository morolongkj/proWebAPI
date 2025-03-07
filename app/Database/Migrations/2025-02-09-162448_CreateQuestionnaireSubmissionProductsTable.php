<?php
namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateQuestionnaireSubmissionProductsTable extends Migration
{
    public function up()
    {
        // Define the structure of the tender_products table
        $this->forge->addField([
            'id'                => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'null'       => false,
                'unique'     => true,
            ],
            'submission_id'     => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'null'       => false,
            ],
            'product_id'        => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'null'       => false,
            ],
            'current_status_id' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'null'       => true,
            ],
            'extra_data'        => [
                'type' => 'JSON',
                'null' => true,
            ],
            'created_at datetime default current_timestamp',
            'updated_at datetime default current_timestamp on update current_timestamp',
        ]);

        // Add primary key
        $this->forge->addPrimaryKey('id');

        // Add foreign keys
        $this->forge->addForeignKey('submission_id', 'questionnaire_submissions', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('product_id', 'products', 'id', 'CASCADE', 'CASCADE');

        $this->forge->createTable('questionnaire_submission_products');
    }

    public function down()
    {
        $this->forge->dropTable('questionnaire_submission_products');
    }
}
