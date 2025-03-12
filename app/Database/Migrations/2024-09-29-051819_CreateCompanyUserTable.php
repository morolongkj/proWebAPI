<?php
namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCompanyUserTable extends Migration
{
    public function up()
    {
        // Define the structure of the company_user table
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
                'null'       => false,
            ],
            'user_id'    => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'created_at datetime default current_timestamp',
            'updated_at datetime default current_timestamp on update current_timestamp',
            'deleted_at' => [
                'type' => 'datetime',
                'null' => true,
            ],
        ]);

        // Add the primary key
        $this->forge->addPrimaryKey('id');

        // Add foreign keys for company_id and user_id
        $this->forge->addForeignKey('company_id', 'companies', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');

        // Add a unique constraint to prevent duplicate entries
        $this->forge->addUniqueKey(['company_id', 'user_id']);

        // Create the table
        $this->forge->createTable('company_users');
    }

    public function down()
    {
        // Drop the company_user table
        $this->forge->dropTable('company_users');
    }
}
