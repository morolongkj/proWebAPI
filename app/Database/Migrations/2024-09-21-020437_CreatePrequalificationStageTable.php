<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePrequalificationStageTable extends Migration
{
    public function up()
    {
        // Define the structure for the prequalification_stages table
        $this->forge->addField([
            'id' => [
                'type' => 'VARCHAR',
                'constraint' => '255',
                'null' => false,
                'unique' => true,
            ],
            'title' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'unique' => true,
            ],
            'description' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'created_at DATETIME DEFAULT CURRENT_TIMESTAMP',
            'updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP',
        ]);

        // Add primary key
        $this->forge->addPrimaryKey('id');
        // Create the table
        $this->forge->createTable('prequalification_stages', true);
    }

    public function down()
    {
        // Drop the prequalification_stages table if it exists
        $this->forge->dropTable('prequalification_stages', true);
    }
}
