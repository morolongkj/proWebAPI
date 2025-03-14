<?php
namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTenderStatusTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'          => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'null'       => false,
                'unique'     => true,
            ],
            'status'      => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
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

        // Adding primary key
        $this->forge->addPrimaryKey('id');

        // Creating the table
        $this->forge->createTable('tender_status');
    }

    public function down()
    {
        // Dropping the table
        $this->forge->dropTable('tender_status');
    }
}
