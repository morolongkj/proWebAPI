<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Forge;
use CodeIgniter\Database\Migration;

class AddMoreAttributesToUsers extends Migration
{
    /**
     * @var string[]
     */
    private array $tables;

    public function __construct(?Forge $forge = null)
    {
        parent::__construct($forge);

        /** @var \Config\Auth $authConfig */
        $authConfig = config('Auth');
        $this->tables = $authConfig->tables;
        $this->attributes = ($this->db->getPlatform() === 'MySQLi') ? ['ENGINE' => 'InnoDB'] : [];
    }

    public function up()
    {
        $fields = [
            'title' => [
                'type' => 'VARCHAR',
                'constraint' => '10',
                'null' => true,
            ],
            'avatar' => [
                'type' => 'VARCHAR',
                'constraint' => '255',
                'null' => true,
            ],
            'first_name' => [
                'type' => 'VARCHAR',
                'constraint' => '100',
                'null' => false,
            ],
            'last_name' => [
                'type' => 'VARCHAR',
                'constraint' => '100',
                'null' => false,
            ],
            'date_of_birth' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'phone_number' => [
                'type' => 'VARCHAR',
                'constraint' => '20',
                'null' => true,
            ],
            'gender' => [
                'type' => 'VARCHAR',
                'constraint' => '20',
                'null' => true,
            ],
            'extra_data' => [
                'type' => 'JSON',
                'null' => true,
            ],
            'company_id' => [
                'type' => 'VARCHAR',
                'constraint' => '255',
                'null' => true,
            ],
            'reset_token' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'reset_token_expires_at datetime default current_timestamp',
        ];
        $this->forge->addColumn($this->tables['users'], $fields);
        $this->forge->addForeignKey('company_id', 'companies', 'id', 'CASCADE', 'SET NULL');
    }

    public function down()
    {
        $fields = [
            'title',
            'avatar',
            'first_name',
            'last_name',
            'date_of_birth',
            'phone_number',
            'gender',
            'extra_data',
            'company_id',
            'reset_token',
            'reset_token_expires_at',
        ];
        $this->forge->dropColumn($this->tables['users'], $fields);
    }
}
