<?php

namespace App\Models;

use CodeIgniter\Model;

class ChatbotKnowledgeModel extends Model
{
    protected $table = 'chatbot_knowledge_base';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = [
        'pertanyaan',
        'intent',
        'keyword',
        'response',
        'status',
        'priority',
        'source',
        'created_at',
        'updated_at',
    ];

    public function ensureTable(): void
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

    public function seedDefaultRows(): void
    {
        $this->ensureTable();

        if ((int) $this->countAllResults() > 0) {
            return;
        }

        $now = date('Y-m-d H:i:s');
        $this->insertBatch([
            [
                'pertanyaan' => 'kapan pendaftaran ppdb',
                'intent' => 'jadwal_ppdb',
                'keyword' => 'kapan, pendaftaran, ppdb, jadwal, dibuka, mulai',
                'response' => 'Pendaftaran PPDB dibuka pada bulan Juni setiap tahunnya.',
                'status' => 'active',
                'priority' => 10,
                'source' => 'default_seed',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'pertanyaan' => 'syarat ppdb',
                'intent' => 'syarat_ppdb',
                'keyword' => 'syarat, persyaratan, ppdb, daftar, dokumen, ijazah, kartu keluarga, pas foto',
                'response' => 'Syarat PPDB adalah fotokopi ijazah, kartu keluarga, dan pas foto.',
                'status' => 'active',
                'priority' => 10,
                'source' => 'default_seed',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'pertanyaan' => 'alamat sekolah',
                'intent' => 'alamat_sekolah',
                'keyword' => 'alamat, lokasi, dimana, sekolah, tempat',
                'response' => 'Alamat sekolah berada di Jl. Contoh No. 10 Kediri.',
                'status' => 'active',
                'priority' => 10,
                'source' => 'default_seed',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }

    public function getActiveKnowledge(): array
    {
        $this->seedDefaultRows();

        return $this->where('status', 'active')
            ->orderBy('priority', 'DESC')
            ->orderBy('id', 'ASC')
            ->findAll();
    }
}
