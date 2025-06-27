<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTournamentRoundRankingsTable extends Migration
{
    public function up()
    {
        $attributes = ($this->db->getPlatform() === 'MySQLi') ? ['ENGINE' => 'InnoDB'] : [];

        // Groups Table
        $this->forge->addField([
            'id'            => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'tournament_id' => ['type' => 'int', 'constraint' => 11, 'unsigned' => true],
            'round_no'      => ['type' => 'int', 'constraint' => 11, 'null' => true],
            'bracket_id'    => ['type' => 'int', 'constraint' => 11, 'unsigned' => true],
            'participant_id' => ['type' => 'int', 'constraint' => 11, 'unsigned' => true],
            'ranking'       => ['type' => 'int', 'constraint' => 11, 'unsigned' => true],
            'created_by'    => ['type' => 'int', 'constraint' => 11, 'unsigned' => true],
            'created_at'    => ['type' => 'datetime', 'null' => false],
            'updated_at'    => ['type' => 'datetime', 'null' => false],
            'deleted_at'    => ['type' => 'datetime', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addForeignKey('tournament_id', 'tournaments', 'id', '', 'CASCADE');
        $this->forge->addForeignKey('participant_id', 'participants', 'id', '', 'CASCADE');
        $this->forge->addForeignKey('created_by', 'users', 'id', '', 'CASCADE');
        $this->forge->createTable('tournament_round_rankings', false, $attributes);
    }

    public function down()
    {
        $this->forge->dropTable('tournament_round_rankings', true);
    }
}