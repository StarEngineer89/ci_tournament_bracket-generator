<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateNotificationsTable extends Migration
{
    public function up()
    {
        $attributes = ($this->db->getPlatform() === 'MySQLi') ? ['ENGINE' => 'InnoDB'] : [];

        $this->forge->addField([
            'id'            => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'user_id'       => ['type' => 'int', 'constraint' => 11],
            'user_to'       => ['type' => 'int', 'constraint' => 11],
            'message'       => ['type' => 'varchar', 'constraint' => 128],
            'type'          => ['type' => 'varchar', 'constraint' => 3],
            'link'          => ['type' => 'varchar', 'constraint' => 255],
            'mark_as_read'  => ['type' => 'tinyint', 'constraint' => 1, 'default' => 0],
            'created_at'    => ['type' => 'datetime', 'null' => false],
            'updated_at'    => ['type' => 'datetime', 'null' => false],
            'deleted_at'    => ['type' => 'datetime', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->createTable('notifications', false, $attributes);
    }

    public function down()
    {
        $this->db->disableForeignKeyChecks();

        $this->forge->dropTable('notifications', true);
    }
}