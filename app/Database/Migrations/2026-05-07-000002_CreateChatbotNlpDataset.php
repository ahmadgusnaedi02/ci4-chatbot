<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateChatbotNlpDataset extends Migration
{
    public function up()
    {
        $this->db->query("
            CREATE TABLE IF NOT EXISTS chatbot_intents (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(120) NOT NULL,
                response TEXT NOT NULL,
                status VARCHAR(30) NOT NULL DEFAULT 'active',
                priority INT NOT NULL DEFAULT 0,
                source VARCHAR(50) NOT NULL DEFAULT 'manual',
                created_at DATETIME NULL,
                updated_at DATETIME NULL,
                UNIQUE KEY uq_chatbot_intents_name (name),
                INDEX idx_chatbot_intents_status_priority (status, priority)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
        ");

        $this->db->query("
            CREATE TABLE IF NOT EXISTS chatbot_training_phrases (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                intent_id INT UNSIGNED NOT NULL,
                phrase TEXT NOT NULL,
                source VARCHAR(50) NOT NULL DEFAULT 'manual',
                created_at DATETIME NULL,
                updated_at DATETIME NULL,
                INDEX idx_chatbot_training_phrases_intent (intent_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
        ");

        $this->db->query("
            CREATE TABLE IF NOT EXISTS chatbot_keywords (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                intent_id INT UNSIGNED NOT NULL,
                keyword VARCHAR(120) NOT NULL,
                created_at DATETIME NULL,
                updated_at DATETIME NULL,
                UNIQUE KEY uq_chatbot_keywords_intent_keyword (intent_id, keyword),
                INDEX idx_chatbot_keywords_intent (intent_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
        ");

        $this->db->query("
            CREATE TABLE IF NOT EXISTS chatbot_stopwords (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                word VARCHAR(80) NOT NULL,
                created_at DATETIME NULL,
                updated_at DATETIME NULL,
                UNIQUE KEY uq_chatbot_stopwords_word (word)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
        ");

        $this->db->query("
            CREATE TABLE IF NOT EXISTS chatbot_suffixes (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                suffix VARCHAR(30) NOT NULL,
                created_at DATETIME NULL,
                updated_at DATETIME NULL,
                UNIQUE KEY uq_chatbot_suffixes_suffix (suffix)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
        ");

        $this->db->query("
            CREATE TABLE IF NOT EXISTS chatbot_synonyms (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                word VARCHAR(120) NOT NULL,
                normalized_word VARCHAR(120) NOT NULL,
                created_at DATETIME NULL,
                updated_at DATETIME NULL,
                UNIQUE KEY uq_chatbot_synonyms_word (word)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
        ");
    }

    public function down()
    {
        $this->forge->dropTable('chatbot_synonyms', true);
        $this->forge->dropTable('chatbot_suffixes', true);
        $this->forge->dropTable('chatbot_stopwords', true);
        $this->forge->dropTable('chatbot_keywords', true);
        $this->forge->dropTable('chatbot_training_phrases', true);
        $this->forge->dropTable('chatbot_intents', true);
    }
}
