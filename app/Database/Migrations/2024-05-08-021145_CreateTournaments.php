<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTournaments extends Migration
{
    public function up()
    {
        $attributes = ($this->db->getPlatform() === 'MySQLi') ? ['ENGINE' => 'InnoDB'] : [];

        $this->forge->addField([
            'id'             => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'name'       => ['type' => 'varchar', 'constraint' => 30, 'null' => 0],
            'user_by'         => ['type' => 'int', 'constraint' => 11, 'null' => 0],
            'type'         => ['type' => 'tinyint', 'constraint' => 1, 'null' => 0, 'default' => 1],
            'status'         => ['type' => 'tinyint', 'constraint' => 1, 'null' => 0, 'default' => 1],
            'created_at'     => ['type' => 'datetime', 'null' => false],
            'updated_at'     => ['type' => 'datetime', 'null' => false],
            'deleted_at'     => ['type' => 'datetime', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->createTable('tournaments', false, $attributes);

        $this->forge->addField([
            'id'             => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'path'       => ['type' => 'varchar', 'constraint' => 128, 'null' => 0],
            'source'   => ['type' => 'varchar', 'constraint' => 1, 'null' => 0, 'default' => 'f'],
            'tournament_id'         => ['type' => 'int', 'constraint' => 11, 'null' => 0],
            'user_by'         => ['type' => 'int', 'constraint' => 11, 'null' => 0],
            'type'         => ['type' => 'tinyint', 'constraint' => 1, 'null' => 0, 'default' => 1],
            'duration'         => ['type' => 'varchar', 'constraint' => 8, 'null' => 0, 'default' => 1],
            'start'         => ['type' => 'varchar', 'constraint' => 8, 'null' => 0, 'default' => 1],
            'end'         => ['type' => 'varchar', 'constraint' => 8, 'null' => 0, 'default' => 1],
            'created_at'     => ['type' => 'datetime', 'null' => false],
            'updated_at'     => ['type' => 'datetime', 'null' => false],
            'deleted_at'     => ['type' => 'datetime', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('user_id');
        $this->forge->addForeignKey('tournament_id', 'tournaments', 'id', '', 'CASCADE');
        $this->forge->createTable('music_settings', false, $attributes);
    }

    public function down()
    {
        $this->db->disableForeignKeyChecks();

        $this->forge->dropTable('tournaments', true);
        $this->forge->dropTable('music_settings', true);
    }
}
