<?php
namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateBidAttachmentsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'              => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'null'       => false,
                'unique'     => true,
            ],
            'bid_id'          => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'null'       => false,
            ],
            'file_name'       => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'null'       => false,
            ],
            'file_path'       => [
                'type' => 'TEXT',
                'null' => false,
            ],
            'file_type'       => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
                'null'       => false,
            ],
            'file_size'       => [
                'type' => 'BIGINT',
                'null' => true,
            ],
            'attachment_name' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'null'       => true,
            ],
            'created_at datetime default current_timestamp',
            'updated_at datetime default current_timestamp on update current_timestamp',
            'deleted_at'      => [
                'type' => 'datetime',
                'null' => true,
            ],
        ]);

        // Adding primary key
        $this->forge->addPrimaryKey('id');

        // Adding foreign key
        $this->forge->addForeignKey('bid_id', 'bids', 'id', 'CASCADE', 'CASCADE');

        // Creating the table
        $this->forge->createTable('bid_attachments');
    }

    public function down()
    {
        // Dropping the table
        $this->forge->dropTable('bid_attachments');
    }
}
