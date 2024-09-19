<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddVoteColumnInParticipantsTable extends Migration
{
    public function up()
    {
        $this->forge->addColumn('participants', ['votes' => ['type' => 'int', 'default' => 0]]);
    }

    public function down()
    {
        $this->forge->dropColumn('participants', 'votes');
    }
}