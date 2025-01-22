<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddWinnerMusicForEveryoneInTournamentsTable extends Migration
{
    public function up()
    {
        $this->forge->addColumn('tournaments', [
            'winner_music_everyone' => [
                'type' => 'tinyint',
                'null' => true, 
                'default' => null
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('tournaments', 'winner_music_everyone');
    }
}