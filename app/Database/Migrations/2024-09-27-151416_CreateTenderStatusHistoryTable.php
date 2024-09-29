<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTenderStatusHistoryTable extends Migration
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
            'tender_id' => [
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
        $this->forge->addForeignKey('tender_id', 'tenders', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('status_id', 'tender_status', 'id', 'RESTRICT', 'RESTRICT');
        $this->forge->addForeignKey('changed_by', 'users', 'id', 'RESTRICT', 'RESTRICT');

        // Creating the table
        $this->forge->createTable('tender_status_history');
    }

    public function down()
    {
        // Dropping the table
        $this->forge->dropTable('tender_status_history');
    }
}
