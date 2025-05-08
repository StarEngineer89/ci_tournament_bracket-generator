<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddVoteDisplayingIntoTournamentsTable extends Migration
{
    public function up()
    {
        $this->forge->addColumn('tournaments', [
            'vote_displaying' => ['type' => 'varchar', 'constraint' => 1, 'default' => VOTE_DISPLAYING_IN_POINT],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('tournaments', 'vote_displaying');
    }
}