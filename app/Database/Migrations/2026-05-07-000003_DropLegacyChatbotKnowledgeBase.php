<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class DropLegacyChatbotKnowledgeBase extends Migration
{
    public function up()
    {
        if (!$this->db->tableExists('chatbot_knowledge_base')) {
            return;
        }

        if ($this->db->tableExists('chatbot_intents')) {
            $this->migrateLegacyRows();
        }

        $this->forge->dropTable('chatbot_knowledge_base', true);
    }

    public function down()
    {
        $this->db->query("
            CREATE TABLE IF NOT EXISTS chatbot_knowledge_base (
                id INT AUTO_INCREMENT PRIMARY KEY,
                pertanyaan TEXT NOT NULL,
                intent VARCHAR(120) NOT NULL,
                keyword TEXT NULL,
                response TEXT NOT NULL,
                status VARCHAR(30) NOT NULL DEFAULT 'active',
                priority INT NOT NULL DEFAULT 0,
                source VARCHAR(50) NOT NULL DEFAULT 'manual',
                created_at DATETIME NULL,
                updated_at DATETIME NULL,
                INDEX idx_chatbot_kb_status_priority (status, priority),
                INDEX idx_chatbot_kb_intent (intent)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
        ");
    }

    private function migrateLegacyRows(): void
    {
        $rows = $this->db->table('chatbot_knowledge_base')
            ->orderBy('priority', 'DESC')
            ->orderBy('id', 'ASC')
            ->get()
            ->getResultArray();

        foreach ($rows as $row) {
            $intentId = $this->upsertIntent($row);
            $this->insertTrainingPhrases($intentId, (string) $row['pertanyaan'], $row['source'] ?? 'legacy');
            $this->insertKeywords($intentId, (string) ($row['keyword'] ?? ''));
        }
    }

    private function upsertIntent(array $row): int
    {
        $intentName = trim((string) $row['intent']);
        $existing = $this->db->table('chatbot_intents')->where('name', $intentName)->get()->getRowArray();

        if ($existing) {
            return (int) $existing['id'];
        }

        $now = date('Y-m-d H:i:s');
        $this->db->table('chatbot_intents')->insert([
            'name' => $intentName,
            'response' => $row['response'],
            'status' => $row['status'] ?? 'active',
            'priority' => (int) ($row['priority'] ?? 0),
            'source' => $row['source'] ?? 'legacy',
            'created_at' => $row['created_at'] ?? $now,
            'updated_at' => $row['updated_at'] ?? $now,
        ]);

        return (int) $this->db->insertID();
    }

    private function insertTrainingPhrases(int $intentId, string $value, string $source): void
    {
        $now = date('Y-m-d H:i:s');

        foreach ($this->splitLines($value) as $phrase) {
            if ($this->db->table('chatbot_training_phrases')
                ->where('intent_id', $intentId)
                ->where('phrase', $phrase)
                ->countAllResults()) {
                continue;
            }

            $this->db->table('chatbot_training_phrases')->insert([
                'intent_id' => $intentId,
                'phrase' => $phrase,
                'source' => $source,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    private function insertKeywords(int $intentId, string $value): void
    {
        $now = date('Y-m-d H:i:s');

        foreach ($this->splitKeywords($value) as $keyword) {
            if ($this->db->table('chatbot_keywords')
                ->where('intent_id', $intentId)
                ->where('keyword', $keyword)
                ->countAllResults()) {
                continue;
            }

            $this->db->table('chatbot_keywords')->insert([
                'intent_id' => $intentId,
                'keyword' => $keyword,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    private function splitLines(string $value): array
    {
        return array_values(array_unique(array_filter(array_map('trim', preg_split('/\R+/', $value) ?: []))));
    }

    private function splitKeywords(string $value): array
    {
        return array_values(array_unique(array_filter(array_map('trim', preg_split('/(?:[,;]|\R)+/', $value) ?: []))));
    }
}
