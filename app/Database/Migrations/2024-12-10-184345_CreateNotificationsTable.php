<?php
namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateNotificationsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'         => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'null'       => false,
                'unique'     => true,
            ],
            'company_id' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'null'       => true,
            ],
            'subject'    => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => false,
            ],
            'message'    => [
                'type' => 'TEXT',
                'null' => false,
            ],
            'is_read'    => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
                'null'       => false,
            ],
            'created_at datetime default current_timestamp',
            'updated_at datetime default current_timestamp on update current_timestamp',
            'deleted_at' => [
                'type' => 'datetime',
                'null' => true,
            ],
        ]);

        // Add primary key
        $this->forge->addPrimaryKey('id');

        // Add foreign key for company_id
        $this->forge->addForeignKey('company_id', 'companies', 'id', 'CASCADE', 'CASCADE');

        // Create the table
        $this->forge->createTable('notifications', true);
    }

    public function down()
    {
        // Drop the table
        $this->forge->dropTable('notifications', true);
    }
}
