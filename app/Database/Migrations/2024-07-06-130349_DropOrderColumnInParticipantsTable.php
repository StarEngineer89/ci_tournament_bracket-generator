<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class DropOrderColumnInParticipantsTable extends Migration
{
    public function up()
    {
        $this->forge->dropColumn('participants', 'order');
    }

    public function down()
    {
        $this->forge->addColumn('participants', [
            'order' => [
                'type' => 'INT',
                'constraint' => 3,
                'null' => true,
            ],
        ]);
    }
}