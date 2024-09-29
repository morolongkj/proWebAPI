<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTenderApprovalsTable extends Migration
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
            'approval_type' => [
                'type' => 'ENUM',
                'constraint' => ['approved', 'rejected'],
                'default' => 'approved',
            ],
            'tender_id' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
            ],
            'user_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'comment' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'created_at datetime default current_timestamp',
            'updated_at datetime default current_timestamp on update current_timestamp',
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('tender_id', 'tenders', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('user_id', 'users', 'id', 'RISTRICT', 'RISTRICT');
        $this->forge->createTable('tender_approvals');
    }

    public function down()
    {
        $this->forge->dropTable('tender_approvals');
    }
}
