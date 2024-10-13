<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePrequalificationsTable extends Migration
{
    public function up()
    {
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
                'null' => true,
            ],
            'company_id' => [
                'type' => 'VARCHAR',
                'constraint' => '255',
                'null' => true,
            ],
            'current_status_id' => [
                'type' => 'VARCHAR',
                'constraint' => '255',
                'null' => true,
            ],
            'extra_data' => [
                'type' => 'JSON',
                'null' => true,
            ],
            'created_at datetime default current_timestamp',
            'updated_at datetime default current_timestamp on update current_timestamp',
        ]);

        // Adding primary key
        $this->forge->addPrimaryKey('id');

        // Adding foreign keys
        $this->forge->addForeignKey('questionnaire_id', 'questionnaires', 'id', 'RESTRICT', 'RESTRICT');
        $this->forge->addForeignKey('company_id', 'companies', 'id', 'RESTRICT', 'RESTRICT');
        $this->forge->addForeignKey('current_status_id', 'tender_status', 'id', 'RESTRICT', 'RESTRICT');

        // Creating the table
        $this->forge->createTable('prequalifications');
    }

    public function down()
    {
        // Dropping the table
        $this->forge->dropTable('prequalifications');
    }
}
