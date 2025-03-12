<?php
namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePrequalifiedCompaniesTable extends Migration
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
                'null'       => false,
            ],
            'product_id' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'null'       => false,
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

        $this->forge->addForeignKey('company_id', 'companies', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('product_id', 'products', 'id', 'CASCADE', 'CASCADE');

        // Add a unique constraint to prevent duplicate entries
        $this->forge->addUniqueKey(['company_id', 'product_id']);

        // Create the table
        $this->forge->createTable('prequalified_companies');
    }

    public function down()
    {
        // Drop the company_user table
        $this->forge->dropTable('prequalified_companies');
    }
}
