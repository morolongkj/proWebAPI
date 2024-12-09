<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateStatusTable extends Migration
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
            'title' => [
                'type' => 'VARCHAR',
                'constraint' => '100',
                'null' => false,
            ],
            'description' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'created_at datetime default current_timestamp',
            'updated_at datetime default current_timestamp on update current_timestamp',
        ]);

        // Adding primary key
        $this->forge->addPrimaryKey('id');

        // Creating the table
        $this->forge->createTable('status');
    }

    public function down()
    {
        // Dropping the table
        $this->forge->dropTable('status');
    }
}
