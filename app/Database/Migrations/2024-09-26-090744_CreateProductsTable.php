<?php
namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateProductsTable extends Migration
{
    public function up()
    {
        // Define the structure of the products table
        $this->forge->addField([
            'id'          => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'null'       => false,
                'unique'     => true,
            ],
            'code'        => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
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
            'category_id' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'null'       => true,
            ],
            'extra_data'  => [
                'type' => 'JSON',
                'null' => true,
            ],
            'created_at datetime default current_timestamp',
            'updated_at datetime default current_timestamp on update current_timestamp',
            'deleted_at'  => [
                'type' => 'datetime',
                'null' => true,
            ],
        ]);

        // Add primary key
        $this->forge->addPrimaryKey('id');

        // Add foreign key for category_id
        $this->forge->addForeignKey('category_id', 'categories', 'id', 'CASCADE', 'SET NULL');

        // Create the products table
        $this->forge->createTable('products');
    }

    public function down()
    {
        // Drop the products table
        $this->forge->dropTable('products');
    }
}
