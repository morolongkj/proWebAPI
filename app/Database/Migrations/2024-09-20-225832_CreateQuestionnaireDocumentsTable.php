<?php
namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateQuestionnaireDocumentsTable extends Migration
{
    public function up()
    {
        // Define the structure of the `questionnaire_documents` table
        $this->forge->addField([
            'id'               => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'null'       => false,
                'unique'     => true,
            ],
            'questionnaire_id' => [
                'type'       => 'VARCHAR',
                'constraint' => '255', // UUID length
                'null'       => false,
            ],
            'file_path'        => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'null'       => false,
            ],
            'file_name'        => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'null'       => false,
            ],
            'file_type'        => [
                'type'       => 'VARCHAR',
                'constraint' => '50',
                'null'       => false,
            ],
            'file_size'        => [
                'type' => 'BIGINT', // Size in bytes
                'null' => false,
            ],
            'created_at DATETIME DEFAULT CURRENT_TIMESTAMP',
            'updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP',
            'deleted_at'       => [
                'type' => 'datetime',
                'null' => true,
            ],
        ]);

        // Add primary key
        $this->forge->addPrimaryKey('id');
        // Add foreign key constraint
        $this->forge->addForeignKey('questionnaire_id', 'questionnaires', 'id', 'CASCADE', 'CASCADE');

        // Create the table
        $this->forge->createTable('questionnaire_documents');
    }

    public function down()
    {
        // Drop the table if it exists
        $this->forge->dropTable('questionnaire_documents', true);
    }
}
