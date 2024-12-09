<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateQuestionnaireSubmissionStatusHistoryTable extends Migration
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
            'submission_id' => [
                'type' => 'VARCHAR',
                'constraint' => '255',
                'null' => false,
            ],
            'status_id' => [
                'type' => 'VARCHAR',
                'constraint' => '255',
                'null' => false,
            ],
            'changed_by' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => false,
            ],
            'change_date datetime default current_timestamp',
            'remarks' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'created_at datetime default current_timestamp',
            'updated_at datetime default current_timestamp on update current_timestamp',
        ]);

        // Adding primary key
        $this->forge->addPrimaryKey('id');

        // Adding foreign key constraints
        $this->forge->addForeignKey('submission_id', 'questionnaire_submissions', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('status_id', 'status', 'id', 'RESTRICT', 'CASCADE');
        $this->forge->addForeignKey('changed_by', 'users', 'id', 'RESTRICT', 'CASCADE');

        // Creating the table
        $this->forge->createTable('questionnaire_submission_status_history');
    }

    public function down()
    {
        // Dropping the table
        $this->forge->dropTable('questionnaire_submission_status_history');
    }
}
