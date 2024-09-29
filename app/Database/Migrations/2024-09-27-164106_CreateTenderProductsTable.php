<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTenderProductsTable extends Migration
{
    public function up()
    {
        // Define the structure of the tender_products table
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
                'null' => false,
            ],
            'product_id' => [
                'type' => 'VARCHAR',
                'constraint' => '255',
                'null' => false,
            ],
            'quantity' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => true,
                'default' => 1,
            ],
            'created_at datetime default current_timestamp',
            'updated_at datetime default current_timestamp on update current_timestamp',
        ]);

        // Add primary key
        $this->forge->addPrimaryKey('id');

        // Add foreign keys
        $this->forge->addForeignKey('tender_id', 'tenders', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('product_id', 'products', 'id', 'CASCADE', 'CASCADE');

        // Create the tender_products table
        $this->forge->createTable('tender_products');
    }

    public function down()
    {
        // Drop the tender_products table
        $this->forge->dropTable('tender_products');
    }
}
