<?php
namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateBidsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'                => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => false,
                'unique'     => true,
            ],
            'tender_id'         => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => false,
            ],
            'submission_date'   => [
                'type' => 'DATETIME',
                'null' => false,
            ],
            'current_status_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'company_id'        => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => false,
            ],
            'created_at datetime default current_timestamp',
            'updated_at datetime default current_timestamp on update current_timestamp',
            'deleted_at'        => [
                'type' => 'datetime',
                'null' => true,
            ],
        ]);

        // Define Primary Key
        $this->forge->addPrimaryKey('id');

        $this->forge->addForeignKey('tender_id', 'tenders', 'id', 'RESTRICT', 'RESTRICT');
        $this->forge->addForeignKey('company_id', 'companies', 'id', 'RESTRICT', 'RESTRICT');
        $this->forge->addForeignKey('current_status_id', 'status', 'id', 'SET NULL', 'CASCADE');

        // Create the Table
        $this->forge->createTable('bids', true);
    }

    public function down()
    {
        // Drop the Table
        $this->forge->dropTable('bids', true);
    }
}
