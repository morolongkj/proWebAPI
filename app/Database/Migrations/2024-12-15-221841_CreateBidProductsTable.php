<?php
namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateBidProductsTable extends Migration
{
    public function up()
    {
        // Define the structure of the tender_products table
        $this->forge->addField([
            'id'                            => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'null'       => false,
                'unique'     => true,
            ],
            'bid_id'                        => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'null'       => false,
            ],
            'product_id'                    => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'null'       => false,
            ],
            'unit_pack'                     => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => true,
            ],
            'quantity_offered'              => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => true,
            ],
            'unit_price'                    => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
                'null'       => true,
            ],
            'total_price'                   => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
                'null'       => true,
            ],
            'lead_time'                     => [
                'type'       => 'INT',
                'constraint' => 5,
                'null'       => true,
            ],
            'manufacture'                   => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'country_of_origin'             => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'registration_number'           => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'medicine_regulatory_authority' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'shipment_weight'               => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
                'null'       => true,
            ],
            'shipment_volume'               => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
                'null'       => true,
            ],
            'comments'                      => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'created_at datetime default current_timestamp',
            'updated_at datetime default current_timestamp on update current_timestamp',
            'deleted_at'                    => [
                'type' => 'datetime',
                'null' => true,
            ],
        ]);

        // Add primary key
        $this->forge->addPrimaryKey('id');

        // Add foreign keys
        $this->forge->addForeignKey('bid_id', 'bids', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('product_id', 'products', 'id', 'CASCADE', 'CASCADE');

        $this->forge->createTable('bid_products');
    }

    public function down()
    {
        $this->forge->dropTable('bid_products');
    }
}
