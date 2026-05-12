<?php

namespace App\Models;

use CodeIgniter\Model;

class ChatbotIntentModel extends Model
{
    protected $table = 'chatbot_intents';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = [
        'name',
        'response',
        'status',
        'priority',
        'source',
        'created_at',
        'updated_at',
    ];

    private bool $schemaReady = false;
    private bool $ensuringSchema = false;

    public function ensureSchema(): void
    {
        if ($this->schemaReady || $this->ensuringSchema) {
            return;
        }

        $this->ensuringSchema = true;

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

        $this->seedNlpDefaults();
        $this->seedDefaultIntents();
        $this->seedPpdbTrainingPhrases();

        $this->schemaReady = true;
        $this->ensuringSchema = false;
    }

    public function getActiveTrainingDataset(): array
    {
        $this->ensureSchema();

        $intents = $this->where('status', 'active')
            ->orderBy('priority', 'DESC')
            ->orderBy('id', 'ASC')
            ->findAll();

        return array_map(function (array $intent): array {
            $intent['training_phrases'] = $this->getTrainingPhrases((int) $intent['id']);
            $intent['keywords'] = $this->getKeywords((int) $intent['id']);

            return $intent;
        }, $intents);
    }

    public function getNlpRules(): array
    {
        $this->ensureSchema();

        $stopWords = array_column($this->db->table('chatbot_stopwords')->select('word')->get()->getResultArray(), 'word');
        $suffixes = array_column($this->db->table('chatbot_suffixes')->select('suffix')->get()->getResultArray(), 'suffix');
        $synonymRows = $this->db->table('chatbot_synonyms')->select('word, normalized_word')->get()->getResultArray();
        $synonyms = [];

        foreach ($synonymRows as $row) {
            $synonyms[$row['word']] = $row['normalized_word'];
        }

        return [
            'stopWords' => $stopWords,
            'suffixes' => $suffixes,
            'synonyms' => $synonyms,
        ];
    }

    public function getNlpRuleDataset(): array
    {
        $this->ensureSchema();

        return [
            'stopwords' => $this->db->table('chatbot_stopwords')->orderBy('word', 'ASC')->get()->getResultArray(),
            'suffixes' => $this->db->table('chatbot_suffixes')->orderBy('suffix', 'ASC')->get()->getResultArray(),
            'synonyms' => $this->db->table('chatbot_synonyms')->orderBy('word', 'ASC')->get()->getResultArray(),
        ];
    }

    public function createNlpRule(string $type, array $data): void
    {
        $this->ensureSchema();
        $config = $this->nlpRuleConfig($type);
        $payload = $this->nlpRulePayload($type, $data);
        $now = date('Y-m-d H:i:s');

        if ($this->db->table($config['table'])->where($config['unique'], $payload[$config['unique']])->countAllResults()) {
            throw new \InvalidArgumentException($config['label'] . ' sudah ada.');
        }

        $this->db->table($config['table'])->insert(array_merge($payload, [
            'created_at' => $now,
            'updated_at' => $now,
        ]));
    }

    public function updateNlpRule(string $type, int $id, array $data): void
    {
        $this->ensureSchema();
        $config = $this->nlpRuleConfig($type);
        $payload = $this->nlpRulePayload($type, $data);
        $existing = $this->db->table($config['table'])->where('id', $id)->get()->getRowArray();

        if (!$existing) {
            throw new \InvalidArgumentException($config['label'] . ' tidak ditemukan.');
        }

        $duplicate = $this->db->table($config['table'])
            ->where($config['unique'], $payload[$config['unique']])
            ->where('id !=', $id)
            ->countAllResults();

        if ($duplicate) {
            throw new \InvalidArgumentException($config['label'] . ' sudah ada.');
        }

        $this->db->table($config['table'])->where('id', $id)->update(array_merge($payload, [
            'updated_at' => date('Y-m-d H:i:s'),
        ]));
    }

    public function deleteNlpRule(string $type, int $id): void
    {
        $this->ensureSchema();
        $config = $this->nlpRuleConfig($type);
        $this->db->table($config['table'])->where('id', $id)->delete();
    }

    public function getIntentWithRelations(int $id): ?array
    {
        $this->ensureSchema();
        $intent = $this->find($id);

        if (!$intent) {
            return null;
        }

        $intent['training_phrases'] = $this->getTrainingPhrases($id);
        $intent['keywords'] = $this->getKeywords($id);

        return $intent;
    }

    public function getSimpleIntents(): array
    {
        $this->ensureSchema();

        return $this->select('id, name')
            ->orderBy('name', 'ASC')
            ->findAll();
    }

    public function getIntentRows(string $keyword = '', string $status = ''): array
    {
        $this->ensureSchema();

        $builder = $this->orderBy('priority', 'DESC')->orderBy('id', 'DESC');

        if ($status !== '') {
            $builder->where('status', $status);
        }

        if ($keyword !== '') {
            $builder->groupStart()
                ->like('name', $keyword)
                ->orLike('response', $keyword)
                ->orLike('source', $keyword)
                ->groupEnd();
        }

        return $builder->findAll();
    }

    public function createIntent(array $data): void
    {
        $this->ensureSchema();
        $payload = $this->intentPayload($data);

        if ($this->where('name', $payload['name'])->first()) {
            throw new \InvalidArgumentException('Intent sudah ada.');
        }

        $this->insert($payload);
    }

    public function updateIntentRow(int $id, array $data): void
    {
        $this->ensureSchema();

        if (!$this->find($id)) {
            throw new \InvalidArgumentException('Intent tidak ditemukan.');
        }

        $payload = $this->intentPayload($data);
        $duplicate = $this->where('name', $payload['name'])
            ->where('id !=', $id)
            ->first();

        if ($duplicate) {
            throw new \InvalidArgumentException('Intent sudah ada.');
        }

        $this->update($id, $payload);
    }

    public function deleteIntentRow(int $id): void
    {
        $this->deleteIntentDataset($id);
    }

    public function getTrainingPhraseRows(string $keyword = '', int $intentId = 0): array
    {
        $this->ensureSchema();
        $builder = $this->db->table('chatbot_training_phrases p')
            ->select('p.*, i.name AS intent_name')
            ->join('chatbot_intents i', 'i.id = p.intent_id')
            ->orderBy('i.name', 'ASC')
            ->orderBy('p.id', 'DESC');

        if ($intentId > 0) {
            $builder->where('p.intent_id', $intentId);
        }

        if ($keyword !== '') {
            $builder->groupStart()
                ->like('p.phrase', $keyword)
                ->orLike('i.name', $keyword)
                ->groupEnd();
        }

        return $builder->get()->getResultArray();
    }

    public function createTrainingPhrase(array $data): void
    {
        $this->ensureSchema();
        $payload = $this->trainingPhrasePayload($data);
        $now = date('Y-m-d H:i:s');

        $this->db->table('chatbot_training_phrases')->insert(array_merge($payload, [
            'created_at' => $now,
            'updated_at' => $now,
        ]));
    }

    public function updateTrainingPhrase(int $id, array $data): void
    {
        $this->ensureSchema();
        $payload = $this->trainingPhrasePayload($data);

        if (!$this->db->table('chatbot_training_phrases')->where('id', $id)->countAllResults()) {
            throw new \InvalidArgumentException('Training phrase tidak ditemukan.');
        }

        $this->db->table('chatbot_training_phrases')->where('id', $id)->update(array_merge($payload, [
            'updated_at' => date('Y-m-d H:i:s'),
        ]));
    }

    public function deleteTrainingPhrase(int $id): void
    {
        $this->ensureSchema();
        $this->db->table('chatbot_training_phrases')->where('id', $id)->delete();
    }

    public function getKeywordRows(string $keyword = '', int $intentId = 0): array
    {
        $this->ensureSchema();
        $builder = $this->db->table('chatbot_keywords k')
            ->select('k.*, i.name AS intent_name')
            ->join('chatbot_intents i', 'i.id = k.intent_id')
            ->orderBy('i.name', 'ASC')
            ->orderBy('k.keyword', 'ASC');

        if ($intentId > 0) {
            $builder->where('k.intent_id', $intentId);
        }

        if ($keyword !== '') {
            $builder->groupStart()
                ->like('k.keyword', $keyword)
                ->orLike('i.name', $keyword)
                ->groupEnd();
        }

        return $builder->get()->getResultArray();
    }

    public function createKeyword(array $data): void
    {
        $this->ensureSchema();
        $payload = $this->keywordPayload($data);
        $now = date('Y-m-d H:i:s');

        if ($this->db->table('chatbot_keywords')
            ->where('intent_id', $payload['intent_id'])
            ->where('keyword', $payload['keyword'])
            ->countAllResults()) {
            throw new \InvalidArgumentException('Keyword untuk intent ini sudah ada.');
        }

        $this->db->table('chatbot_keywords')->insert(array_merge($payload, [
            'created_at' => $now,
            'updated_at' => $now,
        ]));
    }

    public function updateKeyword(int $id, array $data): void
    {
        $this->ensureSchema();
        $payload = $this->keywordPayload($data);

        if (!$this->db->table('chatbot_keywords')->where('id', $id)->countAllResults()) {
            throw new \InvalidArgumentException('Keyword tidak ditemukan.');
        }

        $duplicate = $this->db->table('chatbot_keywords')
            ->where('intent_id', $payload['intent_id'])
            ->where('keyword', $payload['keyword'])
            ->where('id !=', $id)
            ->countAllResults();

        if ($duplicate) {
            throw new \InvalidArgumentException('Keyword untuk intent ini sudah ada.');
        }

        $this->db->table('chatbot_keywords')->where('id', $id)->update(array_merge($payload, [
            'updated_at' => date('Y-m-d H:i:s'),
        ]));
    }

    public function deleteKeyword(int $id): void
    {
        $this->ensureSchema();
        $this->db->table('chatbot_keywords')->where('id', $id)->delete();
    }

    public function getIntentList(string $keyword = '', string $status = '', int $page = 1, int $perPage = 10): array
    {
        $this->ensureSchema();

        $base = $this->db->table('chatbot_intents i');

        if ($status !== '') {
            $base->where('i.status', $status);
        }

        if ($keyword !== '') {
            $base->groupStart()
                ->like('i.name', $keyword)
                ->orLike('i.response', $keyword)
                ->orLike('p.phrase', $keyword)
                ->orLike('k.keyword', $keyword)
                ->groupEnd()
                ->join('chatbot_training_phrases p', 'p.intent_id = i.id', 'left')
                ->join('chatbot_keywords k', 'k.intent_id = i.id', 'left');
        }

        $countBuilder = clone $base;
        $total = (int) $countBuilder->select('COUNT(DISTINCT i.id) AS total')->get()->getRow('total');

        $builder = $this->db->table('chatbot_intents i')
            ->select("i.*, GROUP_CONCAT(DISTINCT p.phrase ORDER BY p.id SEPARATOR '\n') AS training_phrases_text, GROUP_CONCAT(DISTINCT k.keyword ORDER BY k.keyword SEPARATOR ', ') AS keywords_text")
            ->join('chatbot_training_phrases p', 'p.intent_id = i.id', 'left')
            ->join('chatbot_keywords k', 'k.intent_id = i.id', 'left')
            ->groupBy('i.id')
            ->orderBy('i.priority', 'DESC')
            ->orderBy('i.id', 'DESC')
            ->limit($perPage, max(0, ($page - 1) * $perPage));

        if ($status !== '') {
            $builder->where('i.status', $status);
        }

        if ($keyword !== '') {
            $builder->groupStart()
                ->like('i.name', $keyword)
                ->orLike('i.response', $keyword)
                ->orLike('p.phrase', $keyword)
                ->orLike('k.keyword', $keyword)
                ->groupEnd();
        }

        return [
            'items' => $builder->get()->getResultArray(),
            'total' => $total,
        ];
    }

    public function saveIntentDataset(array $data, ?int $id = null): int
    {
        $this->ensureSchema();

        if (!$id) {
            $existing = $this->where('name', $data['name'])->first();
            if ($existing) {
                $id = (int) $existing['id'];
                $data['training_phrases'] = array_values(array_unique(array_merge(
                    $this->getTrainingPhrases($id),
                    $data['training_phrases']
                )));
                $data['keywords'] = array_values(array_unique(array_merge(
                    $this->getKeywords($id),
                    $data['keywords']
                )));
            }
        }

        if ($id) {
            $this->update($id, [
                'name' => $data['name'],
                'response' => $data['response'],
                'status' => $data['status'],
                'priority' => $data['priority'],
                'source' => $data['source'],
            ]);
            $intentId = $id;
        } else {
            $this->insert([
                'name' => $data['name'],
                'response' => $data['response'],
                'status' => $data['status'],
                'priority' => $data['priority'],
                'source' => $data['source'],
            ]);
            $intentId = (int) $this->getInsertID();
        }

        $this->replaceTrainingPhrases($intentId, $data['training_phrases'], $data['source']);
        $this->replaceKeywords($intentId, $data['keywords']);

        return $intentId;
    }

    public function deleteIntentDataset(int $id): void
    {
        $this->ensureSchema();
        $this->db->table('chatbot_training_phrases')->where('intent_id', $id)->delete();
        $this->db->table('chatbot_keywords')->where('intent_id', $id)->delete();
        $this->delete($id);
    }

    private function getTrainingPhrases(int $intentId): array
    {
        return array_column(
            $this->db->table('chatbot_training_phrases')
                ->select('phrase')
                ->where('intent_id', $intentId)
                ->orderBy('id', 'ASC')
                ->get()
                ->getResultArray(),
            'phrase'
        );
    }

    private function getKeywords(int $intentId): array
    {
        return array_column(
            $this->db->table('chatbot_keywords')
                ->select('keyword')
                ->where('intent_id', $intentId)
                ->orderBy('keyword', 'ASC')
                ->get()
                ->getResultArray(),
            'keyword'
        );
    }

    private function replaceTrainingPhrases(int $intentId, array $phrases, string $source): void
    {
        $this->db->table('chatbot_training_phrases')->where('intent_id', $intentId)->delete();
        $now = date('Y-m-d H:i:s');
        $rows = [];

        foreach ($phrases as $phrase) {
            $rows[] = [
                'intent_id' => $intentId,
                'phrase' => $phrase,
                'source' => $source,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        if ($rows) {
            $this->db->table('chatbot_training_phrases')->insertBatch($rows);
        }
    }

    private function replaceKeywords(int $intentId, array $keywords): void
    {
        $this->db->table('chatbot_keywords')->where('intent_id', $intentId)->delete();
        $now = date('Y-m-d H:i:s');
        $rows = [];

        foreach ($keywords as $keyword) {
            $rows[] = [
                'intent_id' => $intentId,
                'keyword' => $keyword,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        if ($rows) {
            $this->db->table('chatbot_keywords')->insertBatch($rows);
        }
    }

    private function seedNlpDefaults(): void
    {
        $now = date('Y-m-d H:i:s');
        $this->insertUniqueRows('chatbot_stopwords', 'word', ['apa', 'aja', 'saja', 'yang', 'untuk', 'di', 'ke', 'itu', 'ini'], $now);
        $this->insertUniqueRows('chatbot_suffixes', 'suffix', ['nya', 'lah', 'kah', 'pun'], $now);

        $synonyms = [
            'dibuka' => 'pendaftaran',
            'mulai' => 'pendaftaran',
            'pendaftar' => 'pendaftaran',
            'persyaratan' => 'syarat',
            'syaratnya' => 'syarat',
            'daftar' => 'pendaftaran',
            'mendaftar' => 'pendaftaran',
            'mendaftarkan' => 'pendaftaran',
            'lokasi' => 'alamat',
            'dimana' => 'alamat',
            'mana' => 'alamat',
            'sekolahnya' => 'sekolah',
        ];

        foreach ($synonyms as $word => $normalizedWord) {
            if (!$this->db->table('chatbot_synonyms')->where('word', $word)->countAllResults()) {
                $this->db->table('chatbot_synonyms')->insert([
                    'word' => $word,
                    'normalized_word' => $normalizedWord,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }

    private function seedDefaultIntents(): void
    {
        if ((int) $this->countAllResults() > 0) {
            return;
        }

        $defaults = [
            [
                'name' => 'jadwal_ppdb',
                'training_phrases' => ['kapan pendaftaran ppdb'],
                'keywords' => ['kapan', 'pendaftaran', 'ppdb', 'jadwal', 'dibuka', 'mulai'],
                'response' => 'Pendaftaran PPDB dibuka pada bulan Juni setiap tahunnya.',
            ],
            [
                'name' => 'syarat_ppdb',
                'training_phrases' => ['syarat ppdb'],
                'keywords' => ['syarat', 'persyaratan', 'ppdb', 'daftar', 'dokumen', 'ijazah', 'kartu keluarga', 'pas foto'],
                'response' => 'Syarat PPDB adalah fotokopi ijazah, kartu keluarga, dan pas foto.',
            ],
            [
                'name' => 'alamat_sekolah',
                'training_phrases' => ['alamat sekolah'],
                'keywords' => ['alamat', 'lokasi', 'dimana', 'sekolah', 'tempat'],
                'response' => 'Alamat sekolah berada di Jl. Contoh No. 10 Kediri.',
            ],
        ];

        foreach ($defaults as $default) {
            $this->saveIntentDataset(array_merge($default, [
                'status' => 'active',
                'priority' => 10,
                'source' => 'default_seed',
            ]));
        }
    }

    private function seedPpdbTrainingPhrases(): void
    {
        $datasets = [
            [
                'name' => 'jadwal_ppdb',
                'keywords' => ['jadwal', 'pendaftaran', 'ppdb', 'dibuka', 'gelombang', 'mulai'],
                'response' => 'Pendaftaran PPDB dibuka pada bulan Juni setiap tahunnya. Untuk jadwal terbaru, silakan hubungi panitia sekolah.',
                'priority' => 20,
                'training_phrases' => [
                    'assalamualaikum bu kapan ppdb mulai dibuka',
                    'permisi pak saya mau tanya jadwal pendaftaran siswa baru',
                    'ppdb tahun ini dibuka tanggal berapa ya',
                    'kapan mulai daftar sekolah di smps plus fajar sentosa',
                    'apakah pendaftaran peserta didik baru sudah dibuka',
                    'gelombang pertama ppdb mulai kapan',
                    'gelombang kedua pendaftaran masih ada tidak',
                    'sampai kapan batas akhir daftar ppdb',
                    'mohon info jadwal ppdb terbaru',
                    'bu saya ingin tahu kapan bisa daftar anak saya',
                ],
            ],
            [
                'name' => 'syarat_ppdb',
                'keywords' => ['syarat', 'persyaratan', 'dokumen', 'berkas', 'kk', 'akta', 'ijazah'],
                'response' => 'Syarat PPDB umumnya fotokopi kartu keluarga, akta kelahiran, ijazah atau surat keterangan lulus, pas foto, dan mengisi formulir pendaftaran.',
                'priority' => 20,
                'training_phrases' => [
                    'assalamualaikum bu apa saja syarat masuk ppdb',
                    'mau tanya berkas pendaftaran apa saja',
                    'dokumen yang harus dibawa untuk daftar apa ya',
                    'apakah harus bawa kartu keluarga untuk ppdb',
                    'syarat daftar siswa baru smp apa saja',
                    'kalau ijazah belum keluar bisa daftar tidak',
                    'perlu akta kelahiran untuk pendaftaran ya bu',
                    'pas foto ukuran berapa untuk daftar sekolah',
                    'mohon info persyaratan ppdb lengkap',
                    'berkas daftar ulang sama dengan berkas ppdb tidak',
                ],
            ],
            [
                'name' => 'biaya_ppdb',
                'keywords' => ['biaya', 'bayar', 'uang', 'pendaftaran', 'spp', 'angsuran'],
                'response' => 'Informasi biaya pendaftaran dan rincian administrasi dapat ditanyakan langsung ke panitia PPDB agar mendapat nominal terbaru.',
                'priority' => 18,
                'training_phrases' => [
                    'assalamualaikum bu biaya daftar ppdb berapa',
                    'mau tanya uang pendaftaran siswa baru',
                    'berapa biaya masuk smps plus fajar sentosa',
                    'apakah biaya ppdb bisa dicicil',
                    'spp per bulan berapa ya pak',
                    'total administrasi awal masuk sekolah berapa',
                    'ada biaya seragam juga tidak',
                    'untuk daftar online bayar dulu atau nanti',
                    'mohon rincian biaya pendaftaran',
                    'apakah ada potongan biaya untuk pendaftar awal',
                ],
            ],
            [
                'name' => 'alur_pendaftaran',
                'keywords' => ['cara', 'alur', 'daftar', 'online', 'formulir', 'pendaftaran'],
                'response' => 'Alur pendaftaran dimulai dari mengisi formulir, melengkapi berkas, verifikasi panitia, lalu mengikuti informasi lanjutan dari sekolah.',
                'priority' => 18,
                'training_phrases' => [
                    'assalamualaikum cara daftar ppdb bagaimana ya',
                    'saya mau daftarkan anak langkahnya apa saja',
                    'pendaftaran bisa online atau harus datang ke sekolah',
                    'formulir ppdb bisa diisi dimana',
                    'setelah isi formulir lanjut apa ya bu',
                    'bagaimana prosedur pendaftaran siswa baru',
                    'kalau mau daftar langsung ke sekolah jam berapa',
                    'apakah bisa daftar lewat whatsapp',
                    'mohon panduan alur pendaftaran',
                    'saya masih bingung cara daftar anak saya',
                ],
            ],
            [
                'name' => 'alamat_sekolah',
                'keywords' => ['alamat', 'lokasi', 'sekolah', 'maps', 'arah', 'tempat'],
                'response' => 'Alamat sekolah berada di Jl. Contoh No. 10 Kediri. Untuk titik maps terbaru, silakan hubungi admin sekolah.',
                'priority' => 16,
                'training_phrases' => [
                    'assalamualaikum alamat sekolah dimana ya',
                    'lokasi smps plus fajar sentosa dimana',
                    'minta share maps sekolah boleh bu',
                    'kalau dari terminal arah ke sekolah lewat mana',
                    'sekolahnya dekat daerah mana ya pak',
                    'saya mau survey lokasi sekolah',
                    'alamat lengkap untuk datang daftar apa ya',
                    'apakah sekolah mudah dicari di google maps',
                    'mohon titik lokasi sekolah',
                    'tempat pendaftaran ppdb di sekolah bagian mana',
                ],
            ],
            [
                'name' => 'kontak_panitia',
                'keywords' => ['kontak', 'nomor', 'whatsapp', 'admin', 'panitia', 'hubungi'],
                'response' => 'Silakan hubungi admin atau panitia PPDB melalui WhatsApp resmi sekolah untuk informasi lebih lanjut.',
                'priority' => 16,
                'training_phrases' => [
                    'assalamualaikum nomor panitia ppdb berapa',
                    'bisa minta kontak admin sekolah',
                    'whatsapp ppdb yang aktif nomor berapa',
                    'saya mau bicara dengan panitia pendaftaran',
                    'kalau ada pertanyaan lanjut hubungi siapa',
                    'nomor cs ppdb ada tidak',
                    'admin online jam berapa ya bu',
                    'minta nomor wa untuk konsultasi pendaftaran',
                    'apakah bisa tanya ppdb lewat whatsapp',
                    'kontak sekolah untuk pendaftaran siswa baru apa',
                ],
            ],
            [
                'name' => 'tes_seleksi',
                'keywords' => ['tes', 'seleksi', 'ujian', 'wawancara', 'observasi', 'masuk'],
                'response' => 'Informasi tes, observasi, atau wawancara akan disampaikan panitia setelah calon siswa menyelesaikan pendaftaran.',
                'priority' => 15,
                'training_phrases' => [
                    'assalamualaikum apakah masuk sekolah ada tes',
                    'ppdb ada seleksi atau tidak',
                    'anak saya harus ikut ujian masuk ya',
                    'materi tes masuk apa saja bu',
                    'ada wawancara orang tua tidak',
                    'jadwal tes seleksi kapan diberikan',
                    'kalau tidak ikut tes bisa daftar tidak',
                    'apakah ada observasi calon siswa',
                    'tes ppdb dilakukan online atau offline',
                    'mohon info tahapan seleksi siswa baru',
                ],
            ],
            [
                'name' => 'kuota_ppdb',
                'keywords' => ['kuota', 'kursi', 'penuh', 'tersedia', 'kelas', 'rombongan'],
                'response' => 'Kuota PPDB terbatas dan mengikuti kapasitas kelas. Silakan daftar lebih awal atau hubungi panitia untuk cek ketersediaan.',
                'priority' => 14,
                'training_phrases' => [
                    'assalamualaikum kuota ppdb masih ada tidak',
                    'kursi siswa baru masih tersedia ya bu',
                    'apakah pendaftaran sudah penuh',
                    'berapa kuota siswa baru tahun ini',
                    'kelas tujuh menerima berapa siswa',
                    'kalau daftar minggu depan masih bisa tidak',
                    'sisa kuota ppdb tinggal berapa',
                    'apakah ada daftar tunggu kalau penuh',
                    'mohon info kapasitas penerimaan siswa baru',
                    'gelombang terakhir masih buka kuota tidak',
                ],
            ],
            [
                'name' => 'seragam_fasilitas',
                'keywords' => ['seragam', 'fasilitas', 'ekskul', 'buku', 'kegiatan', 'kelas'],
                'response' => 'Informasi seragam, fasilitas, kegiatan sekolah, dan ekstrakurikuler dapat ditanyakan ke admin PPDB atau saat kunjungan sekolah.',
                'priority' => 12,
                'training_phrases' => [
                    'assalamualaikum seragam sekolah dapat apa saja',
                    'biaya seragam sudah termasuk pendaftaran tidak',
                    'fasilitas sekolah apa saja ya bu',
                    'ada ekstrakurikuler apa di sekolah',
                    'apakah ada kegiatan tahfidz atau keagamaan',
                    'buku pelajaran disediakan sekolah atau beli sendiri',
                    'kelasnya pakai ac atau kipas',
                    'apakah ada lab komputer',
                    'minta info fasilitas smps plus fajar sentosa',
                    'seragam bisa diambil kapan setelah daftar',
                ],
            ],
            [
                'name' => 'daftar_ulang_pindahan',
                'keywords' => ['daftar ulang', 'pindahan', 'transfer', 'lanjutan', 'diterima', 'verifikasi'],
                'response' => 'Daftar ulang dilakukan setelah calon siswa dinyatakan diterima. Untuk siswa pindahan, silakan hubungi panitia agar berkas dapat dicek lebih dulu.',
                'priority' => 12,
                'training_phrases' => [
                    'assalamualaikum daftar ulang kapan dilakukan',
                    'kalau sudah diterima harus daftar ulang dimana',
                    'berkas daftar ulang apa saja bu',
                    'apakah siswa pindahan bisa daftar',
                    'anak saya pindahan dari sekolah lain bisa masuk tidak',
                    'syarat siswa transfer apa saja',
                    'kalau sudah bayar pendaftaran lanjut daftar ulang ya',
                    'jadwal verifikasi berkas kapan',
                    'apakah daftar ulang wajib datang orang tua',
                    'mohon info proses siswa pindahan',
                ],
            ],
            [
                'name' => 'penutup_chat',
                'keywords' => ['oke', 'ok', 'terima kasih', 'makasih', 'thanks', 'sip', 'baik'],
                'response' => 'Sama-sama. Jika ada pertanyaan lain seputar PPDB, silakan hubungi kami kembali.',
                'priority' => 22,
                'training_phrases' => [
                    'oke terima kasih banyak',
                    'baik bu terima kasih',
                    'makasih banyak ya bu',
                    'terima kasih infonya',
                    'oke bu sudah jelas',
                    'baik pak sudah cukup',
                    'sip terima kasih',
                    'ok makasih admin',
                    'terimakasih atas informasinya',
                    'oke nanti saya kabari lagi',
                    'baik nanti saya lengkapi berkasnya',
                    'makasih ya sudah dibantu',
                    'terima kasih sudah dijawab',
                    'oke bu saya paham',
                    'baik terima kasih banyak pak',
                    'siap bu terima kasih',
                    'oke kalau begitu terima kasih',
                    'cukup jelas bu makasih',
                    'alhamdulillah terima kasih infonya',
                    'terima kasih nanti saya datang ke sekolah',
                ],
            ],
        ];

        foreach ($datasets as $dataset) {
            $this->saveIntentDataset(array_merge($dataset, [
                'status' => 'active',
                'source' => 'ppdb_training_seed',
            ]));
        }
    }

    private function insertUniqueRows(string $table, string $field, array $values, string $now): void
    {
        foreach ($values as $value) {
            if (!$this->db->table($table)->where($field, $value)->countAllResults()) {
                $this->db->table($table)->insert([
                    $field => $value,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
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

    private function nlpRuleConfig(string $type): array
    {
        return match ($type) {
            'stopwords' => [
                'table' => 'chatbot_stopwords',
                'unique' => 'word',
                'label' => 'Stopword',
            ],
            'suffixes' => [
                'table' => 'chatbot_suffixes',
                'unique' => 'suffix',
                'label' => 'Suffix',
            ],
            'synonyms' => [
                'table' => 'chatbot_synonyms',
                'unique' => 'word',
                'label' => 'Synonym',
            ],
            default => throw new \InvalidArgumentException('Jenis rule NLP tidak valid.'),
        };
    }

    private function nlpRulePayload(string $type, array $data): array
    {
        if ($type === 'synonyms') {
            $word = $this->normalizeRuleValue((string) ($data['word'] ?? ''));
            $normalizedWord = $this->normalizeRuleValue((string) ($data['normalized_word'] ?? ''));

            if ($word === '' || $normalizedWord === '') {
                throw new \InvalidArgumentException('Kata asal dan kata normal wajib diisi.');
            }

            return [
                'word' => $word,
                'normalized_word' => $normalizedWord,
            ];
        }

        $field = $type === 'stopwords' ? 'word' : 'suffix';
        $value = $this->normalizeRuleValue((string) ($data[$field] ?? ''));

        if ($value === '') {
            throw new \InvalidArgumentException(($type === 'stopwords' ? 'Stopword' : 'Suffix') . ' wajib diisi.');
        }

        return [$field => $value];
    }

    private function normalizeRuleValue(string $value): string
    {
        $value = strtolower(trim($value));
        $value = preg_replace('/[^\p{L}\p{N}\s_-]+/u', '', $value);

        return trim(preg_replace('/\s+/', ' ', $value));
    }

    private function intentPayload(array $data): array
    {
        $name = strtolower(trim((string) ($data['name'] ?? '')));
        $response = trim((string) ($data['response'] ?? ''));
        $status = trim((string) ($data['status'] ?? 'active')) ?: 'active';

        if ($name === '') {
            throw new \InvalidArgumentException('Intent wajib diisi.');
        }

        if (!preg_match('/^[a-z0-9_]+$/', $name)) {
            throw new \InvalidArgumentException('Intent hanya boleh berisi huruf kecil, angka, dan underscore.');
        }

        if ($response === '') {
            throw new \InvalidArgumentException('Response wajib diisi.');
        }

        if (!in_array($status, ['active', 'inactive', 'draft'], true)) {
            throw new \InvalidArgumentException('Status tidak valid.');
        }

        return [
            'name' => $name,
            'response' => $response,
            'status' => $status,
            'priority' => (int) ($data['priority'] ?? 0),
            'source' => trim((string) ($data['source'] ?? 'manual')) ?: 'manual',
        ];
    }

    private function trainingPhrasePayload(array $data): array
    {
        $intentId = (int) ($data['intent_id'] ?? 0);
        $phrase = trim((string) ($data['phrase'] ?? ''));
        $source = trim((string) ($data['source'] ?? 'manual')) ?: 'manual';

        if (!$this->find($intentId)) {
            throw new \InvalidArgumentException('Intent wajib dipilih.');
        }

        if ($phrase === '') {
            throw new \InvalidArgumentException('Training phrase wajib diisi.');
        }

        return [
            'intent_id' => $intentId,
            'phrase' => $phrase,
            'source' => $source,
        ];
    }

    private function keywordPayload(array $data): array
    {
        $intentId = (int) ($data['intent_id'] ?? 0);
        $keyword = strtolower(trim((string) ($data['keyword'] ?? '')));

        if (!$this->find($intentId)) {
            throw new \InvalidArgumentException('Intent wajib dipilih.');
        }

        if ($keyword === '') {
            throw new \InvalidArgumentException('Keyword wajib diisi.');
        }

        return [
            'intent_id' => $intentId,
            'keyword' => $keyword,
        ];
    }
}
