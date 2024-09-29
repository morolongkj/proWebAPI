<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTenderAttachmentsTable extends Migration
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
                'null' => true,
            ],
            'file_name' => [
                'type' => 'VARCHAR',
                'constraint' => '255',
                'null' => false,
            ],
            'file_path' => [
                'type' => 'VARCHAR',
                'constraint' => '255',
                'null' => false,
            ],
            'file_type' => [
                'type' => 'VARCHAR',
                'constraint' => '50',
                'null' => false,
            ],
            'created_at datetime default current_timestamp',
            'updated_at datetime default current_timestamp on update current_timestamp',
        ]);

        // Adding primary key
        $this->forge->addPrimaryKey('id');

        // Adding foreign key constraint for `tender_id`
        $this->forge->addForeignKey('tender_id', 'tenders', 'id', 'CASCADE', 'CASCADE');

        // Creating the table
        $this->forge->createTable('tender_attachments');
    }

    public function down()
    {
        // Dropping the table
        $this->forge->dropTable('tender_attachments');
    }
}
