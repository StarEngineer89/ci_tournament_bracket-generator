<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AlterTournamentRoundSettingsTable extends Migration
{
    public function up()
    {
        $this->forge->addColumn('tournament_round_settings', [
            'timer_start' => ['type' => 'varchar', 'constraint' => 11, 'default' => null, 'after' => "round_name"],
            'duration' => ['type' => 'varchar', 'constraint' => 11, 'default' => null, 'after' => "timer_start"],
            'status' => ['type' => 'tinyint', 'constraint' => 1, 'default' => null, 'after' => "duration"]
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('tournament_round_settings', ['timer_start', 'duration', 'status']);
    }
}