<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateGroupsTable extends Migration
{
    public function up()
    {
        $attributes = ($this->db->getPlatform() === 'MySQLi') ? ['ENGINE' => 'InnoDB'] : [];

        // Groups Table
        $this->forge->addField([
            'id'            => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'group_name'    => ['type' => 'varchar', 'constraint' => 64, 'null' => false],
            'image_path'    => ['type' => 'varchar', 'constraint' => 128, 'null' => true],
            'created_at'    => ['type' => 'datetime', 'null' => false],
            'updated_at'    => ['type' => 'datetime', 'null' => false],
            'deleted_at'    => ['type' => 'datetime', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->createTable('groups', false, $attributes);

        // Groups participants Table
        $this->forge->addField([
            'id'            => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'group_id'      => ['type' => 'int', 'constraint' => 11, 'unsigned' => true],
            'participant_id'=> ['type' => 'int', 'constraint' => 11, 'unsigned' => true],
            'created_at'    => ['type' => 'datetime', 'null' => false],
            'updated_at'    => ['type' => 'datetime', 'null' => false],
            'deleted_at'    => ['type' => 'datetime', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('group_id');
        $this->forge->addForeignKey('group_id', 'groups', 'id', '', 'CASCADE');
        $this->forge->addKey('participant_id');
        $this->forge->addForeignKey('participant_id', 'participants', 'id', '', 'CASCADE');
        $this->forge->createTable('grouped_participants', false, $attributes);
    }

    public function down()
    {
        $this->db->disableForeignKeyChecks();

        $this->forge->dropTable('groups', true);
        $this->forge->dropTable('grouped_participants', true);
        
        $this->db->enableForeignKeyChecks();
    }
}