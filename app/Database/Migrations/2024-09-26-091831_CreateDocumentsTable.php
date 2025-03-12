<?php
namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateDocumentsTable extends Migration
{
    public function up()
    {
        // Define the structure of the documents table
        $this->forge->addField([
            'id'          => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'null'       => false,
                'unique'     => true,
            ],
            'title'       => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => false,
            ],
            'description' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'created_at datetime default current_timestamp',
            'updated_at datetime default current_timestamp on update current_timestamp',
            'deleted_at'  => [
                'type' => 'datetime',
                'null' => true,
            ],
        ]);

        // Set the primary key
        $this->forge->addPrimaryKey('id');

        // Create the table
        $this->forge->createTable('documents');
    }

    public function down()
    {
        // Drop the documents table
        $this->forge->dropTable('documents');
    }
}
