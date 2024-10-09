<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTendersTable extends Migration
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
            'reference_number' => [
                'type' => 'VARCHAR',
                'constraint' => '100',
                'null' => false,
            ],
            'title' => [
                'type' => 'VARCHAR',
                'constraint' => '255',
            ],
            'description' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'created_by' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'current_status_id' => [
                'type' => 'VARCHAR',
                'constraint' => '255',
                'null' => true,
            ],
            'opening_date' => [
                'type' => 'DATE',
                'null' => false,
            ],
            'opening_time' => [
                'type' => 'TIME',
                'null' => false,
            ],
            'closing_date' => [
                'type' => 'DATE',
                'null' => false,
            ],
            'closing_time' => [
                'type' => 'TIME',
                'null' => false,
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
        $this->forge->addForeignKey('created_by', 'users', 'id', 'RESTRICT', 'RESTRICT');
        $this->forge->addForeignKey('current_status_id', 'tender_status', 'id', 'RESTRICT', 'RESTRICT');

        // Creating the table
        $this->forge->createTable('tenders');
    }

    public function down()
    {
        // Dropping the table
        $this->forge->dropTable('tenders');
    }
}
