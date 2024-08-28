<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class DropForeignkeys extends Migration
{
    public function up()
    {
        //
        
        $this->forge->dropForeignKey('tournaments', 'tournaments_user_id_foreign');
        
        $this->forge->dropForeignKey('share_settings', 'share_settings_user_id_foreign');
    }

    public function down()
    {
        //
    }
}
