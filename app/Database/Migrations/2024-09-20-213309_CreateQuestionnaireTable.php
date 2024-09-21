<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateQuestionnaireTable extends Migration
{
    public function up()
    {
        // Define the structure of the `questionnaires` table
        $this->forge->addField([
             'id' => [
                'type' => 'VARCHAR',
                'constraint' => '255',
                'null' => false,
                'unique' => true,
            ],
            'title' => [
                'type' => 'VARCHAR',
                'constraint' => '255',
                'unique' => true,
            ],
            'description' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'status' => [
                'type' => 'ENUM',
                'constraint' => ['open', 'closed'],
                'default' => 'closed',
            ],
            'is_open_forever' => [
                'type' => 'BOOLEAN',
                'default' => false,
            ],
            'open_from' => [
                'type' => 'DATE',
                'null' => true, // Allow null if `is_open_forever` is true
            ],
            'open_until' => [
                'type' => 'DATE',
                'null' => true, // Allow null if `is_open_forever` is true
            ],
            'created_at datetime default current_timestamp',
            'updated_at datetime default current_timestamp on update current_timestamp',
        ]);

        // Add primary key
        $this->forge->addPrimaryKey('id');
        // Create the table
        $this->forge->createTable('questionnaires');
    }

    public function down()
    {
        // Drop the table if it exists
        $this->forge->dropTable('questionnaires', true);
    }
}
