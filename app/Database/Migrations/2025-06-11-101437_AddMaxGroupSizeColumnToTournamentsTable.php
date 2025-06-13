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
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('tournaments', ['max_group_size', 'advance_count']);
    }
}