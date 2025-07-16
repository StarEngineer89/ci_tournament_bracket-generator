<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddMaxGroupSizeColumnToTournamentsTable extends Migration
{
    public function up()
    {
        $this->forge->addColumn('tournaments', [
            'max_group_size' => [
                'type' => 'tinyint',
                'null' => true,
                'default' => 0
            ],
            'advance_count' => [
                'type' => 'tinyint',
                'null' => true,
                'default' => 0
            ],
            'participant_manage_metrics' => [
                'type' => 'tinyint',
                'null' => false,
                'default' => 0
            ],
            'host_manage_metrics' => [
                'type' => 'tinyint',
                'null' => false,
                'default' => 0
            ],
            'allow_metric_edits' => [
                'type' => 'tinyint',
                'null' => false,
                'default' => 0
            ],
            'scoring_method' => [
                'type' => 'varchar',
                'constraint' => 1,
                'default' => TOURNAMENT_SCORE_MANUAL_ENTRY,
                'after' => "score_enabled"
            ],
            'score_manual_override' => [
                'type' => 'tinyint',
                'null' => false,
                'default' => 0,
                'after' => "scoring_method"
            ],
            'timer_option' => [
                'type' => 'varchar',
                'constraint' => 1,
                'default' => AUTOMATIC,
            ],
            'timer_auto_advance' => [
                'type' => 'tinyint',
                'null' => false,
                'default' => 0,
            ],
            'timer_require_scores' => [
                'type' => 'tinyint',
                'null' => false,
                'default' => 0,
            ],
            'timer_start_manually' => [
                'type' => 'tinyint',
                'null' => false,
                'default' => 0,
            ],
            'timer_start_option' => [
                'type' => 'varchar',
                'constraint' => 1,
                'default' => AUTOMATIC,
            ],
            'round_score_editing' => [
                'type' => 'tinyint',
                'null' => false,
                'default' => 0,
            ],
            'round_advance_method' => [
                'type' => 'varchar',
                'constraint' => 1,
                'default' => AUTOMATIC,
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('tournaments', ['max_group_size', 'advance_count', 'participant_manage_metrics', 'host_manage_metrics', 'allow_metric_edits', 'scoring_method', 'score_manual_override', 'timer_option', 'timer_auto_advance', 'timer_require_scores', 'timer_start_manually', 'timer_start_option', 'round_score_editing', 'round_advance_method']);
    }
}