<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCompaniesTable extends Migration
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
            'company_name' => [
                'type' => 'VARCHAR',
                'constraint' => '100',
                'null' => false,
            ],
            'year_established' => [
                'type' => 'VARCHAR',
                'constraint' => '4',
                'null' => false,
            ],
            'company_form' => [
                'type' => 'VARCHAR',
                'constraint' => '50',
                'null' => true,
            ],
            'specify_company_form' => [
                'type' => 'VARCHAR',
                'constraint' => '100',
                'null' => true,
            ],
            'legal_status' => [
                'type' => 'VARCHAR',
                'constraint' => '100',
                'null' => true,
            ],
            'trade_registration_number' => [
                'type' => 'VARCHAR',
                'constraint' => '100',
                'null' => false,
            ],
            'vat_number' => [
                'type' => 'VARCHAR',
                'constraint' => '100',
                'null' => false,
            ],
            'address' => [
                'type' => 'VARCHAR',
                'constraint' => '100',
                'null' => false,
            ],
            'country' => [
                'type' => 'VARCHAR',
                'constraint' => '100',
                'null' => false,
            ],
            'telephone' => [
                'type' => 'VARCHAR',
                'constraint' => '100',
                'null' => false,
            ],
            'email' => [
                'type' => 'VARCHAR',
                'constraint' => '100',
                'null' => false,
            ],
            'website' => [
                'type' => 'VARCHAR',
                'constraint' => '100',
                'null' => true,
            ],
            'telefax' => [
                'type' => 'VARCHAR',
                'constraint' => '100',
                'null' => true,
            ],
            'telex' => [
                'type' => 'VARCHAR',
                'constraint' => '100',
                'null' => true,
            ],
            'created_at datetime default current_timestamp',
            'updated_at datetime default current_timestamp on update current_timestamp',
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->createTable('companies');
    }

    public function down()
    {
        $this->forge->dropTable('companies');
    }
}
