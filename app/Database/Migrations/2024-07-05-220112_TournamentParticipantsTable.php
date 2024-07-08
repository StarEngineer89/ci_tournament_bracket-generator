<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class TournamentParticipantsTable extends Migration
{
    public function up()
    {
        $attributes = ($this->db->getPlatform() === 'MySQLi') ? ['ENGINE' => 'InnoDB'] : [];

        $this->forge->addField([
            'id'            => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'tournament_id'       => ['type' => 'int', 'constraint' => 11],
            'participant_id'       => ['type' => 'int', 'constraint' => 11],
            'bracket_id'       => ['type' => 'int', 'constraint' => 11, 'null' => true],
            'order'       => ['type' => 'tinyint', 'constraint' => 3],
            'created_at'    => ['type' => 'datetime', 'null' => false],
            'updated_at'    => ['type' => 'datetime', 'null' => false],
            'deleted_at'    => ['type' => 'datetime', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->createTable('tournament_participants', false, $attributes);
    }

    public function down()
    {
        $this->db->disableForeignKeyChecks();

        $this->forge->dropTable('tournament_participants', true);
    }
}