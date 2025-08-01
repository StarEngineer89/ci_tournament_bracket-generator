<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddColumnsForCustomTimer extends Migration
{
    public function up()
    {
        $this->forge->addColumn('tournaments', [
            'round_time_type' => ['type' => 'varchar', 'constraint' => 1, 'default' => TOURNAMENT_CUSTOM_TIMER_SAME],
            'round_duration' => ['type' => 'varchar', 'constraint' => 11, 'null' => true]
        ]);
        $this->forge->addColumn('tournament_round_settings', ['duration' => ['type' => 'varchar', 'constraint' => 11, 'default' => null, 'after' => "round_name"]]);
    }

    public function down()
    {
        $this->forge->dropColumn('tournaments', 'round_time_type');
        $this->forge->dropColumn('tournaments', 'round_duration');
        $this->forge->dropColumn('tournament_round_settings', 'duration');
    }
}