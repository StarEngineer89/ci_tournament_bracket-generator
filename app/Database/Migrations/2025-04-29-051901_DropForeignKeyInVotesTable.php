<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class DropForeignKeyInVotesTable extends Migration
{
    public function up()
    {
        $this->forge->dropForeignKey('votes', 'votes_participant_id_foreign');
    }

    public function down()
    {
        //
    }
}