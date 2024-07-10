<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateLogActionsTable extends Migration
{
    public function up()
    {
        $attributes = ($this->db->getPlatform() === 'MySQLi') ? ['ENGINE' => 'InnoDB'] : [];

        $this->forge->addField([
            'id'             => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'user_id'        => ['type' => 'int', 'constraint' => 11, 'null' => 0],
            'tournament_id'  => ['type' => 'int', 'constraint' => 11, 'null' => 0, 'default' => 0],
            'action'         => ['type' => 'tinyint', 'constraint' => 1, 'null' => 0],
            'params'         => ['type' => 'varchar', 'constraint' => 128, 'null' => 0],
            'created_at'     => ['type' => 'datetime', 'null' => false],
            'updated_at'     => ['type' => 'datetime', 'null' => false],
            'deleted_at'     => ['type' => 'datetime', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->createTable('log_actions', false, $attributes);
    }

    public function down()
    {
        $this->db->disableForeignKeyChecks();

        $this->forge->dropTable('log_actions', true);
    }
}