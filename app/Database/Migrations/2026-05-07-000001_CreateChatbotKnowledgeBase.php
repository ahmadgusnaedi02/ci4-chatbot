<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateChatbotKnowledgeBase extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('chatbot_knowledge_base')) {
            return;
        }

        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'pertanyaan' => [
                'type' => 'TEXT',
                'null' => false,
            ],
            'intent' => [
                'type' => 'VARCHAR',
                'constraint' => 120,
            ],
            'keyword' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'response' => [
                'type' => 'TEXT',
                'null' => false,
            ],
            'status' => [
                'type' => 'VARCHAR',
                'constraint' => 30,
                'default' => 'active',
            ],
            'priority' => [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 0,
            ],
            'source' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'default' => 'manual',
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['status', 'priority'], false, false, 'idx_chatbot_kb_status_priority');
        $this->forge->addKey('intent', false, false, 'idx_chatbot_kb_intent');
        $this->forge->createTable('chatbot_knowledge_base', true, [
            'ENGINE' => 'InnoDB',
            'CHARACTER SET' => 'utf8mb4',
            'COLLATE' => 'utf8mb4_general_ci',
        ]);
    }

    public function down()
    {
        $this->forge->dropTable('chatbot_knowledge_base', true);
    }
}
