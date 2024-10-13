<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateQuestionnaireProductsTable extends Migration
{
    public function up()
    {
        // Define the structure of the tender_products table
        $this->forge->addField([
            'id' => [
                'type' => 'VARCHAR',
                'constraint' => '255',
                'null' => false,
                'unique' => true,
            ],
            'questionnaire_id' => [
                'type' => 'VARCHAR',
                'constraint' => '255',
                'null' => false,
            ],
            'product_id' => [
                'type' => 'VARCHAR',
                'constraint' => '255',
                'null' => false,
            ],
            'created_at datetime default current_timestamp',
            'updated_at datetime default current_timestamp on update current_timestamp',
        ]);

        // Add primary key
        $this->forge->addPrimaryKey('id');

        // Add foreign keys
        $this->forge->addForeignKey('questionnaire_id', 'questionnaires', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('product_id', 'products', 'id', 'CASCADE', 'CASCADE');

        // Create the questionnaire_products table
        $this->forge->createTable('questionnaire_products');
    }

    public function down()
    {
        // Drop the questionnaire_products table
        $this->forge->dropTable('questionnaire_products');
    }
}
