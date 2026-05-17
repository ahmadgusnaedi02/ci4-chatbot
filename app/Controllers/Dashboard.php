<?php

namespace App\Controllers;

use App\Models\ChatbotIntentModel;
use CodeIgniter\HTTP\RedirectResponse;

class Dashboard extends BaseController
{
    private function ensureAdminProfileSchema(): void
    {
        $db = db_connect();

        $db->query("
            CREATE TABLE IF NOT EXISTS admin_users (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(120) NOT NULL,
                email VARCHAR(150) NOT NULL UNIQUE,
                avatar_url VARCHAR(255) NULL,
                password_hash VARCHAR(255) NOT NULL,
                role VARCHAR(50) NOT NULL DEFAULT 'admin',
                created_at DATETIME NULL,
                updated_at DATETIME NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
        ");

        if (!$db->fieldExists('avatar_url', 'admin_users')) {
            $db->query('ALTER TABLE admin_users ADD avatar_url VARCHAR(255) NULL AFTER email');
        }
    }

    public function index(): string
    {
        return view('dashboard/index_dashboard', $this->getDashboardSummary());

    }

    private function getDashboardSummary(): array
    {
        $intentModel = new ChatbotIntentModel();
        $intentModel->ensureSchema();

        $db = db_connect();
        $dateRange = $this->lastSevenDays();

        $stats = [
            'questioners' => $this->countRows($db, 'wa_chats'),
            'intents' => $this->countRows($db, 'chatbot_intents'),
            'datasets' => $this->countRows($db, 'chatbot_training_phrases'),
            'chats' => $this->countRows($db, 'wa_messages'),
        ];

        return [
            'stats' => $stats,
            'chartLabels' => array_column($dateRange, 'label'),
            'chatChartData' => array_values($this->dailyMessageCounts($db, array_column($dateRange, 'date'))),
            'questionerChartData' => array_values($this->dailyQuestionerCounts($db, array_column($dateRange, 'date'))),
        ];
    }

    private function countRows($db, string $table): int
    {
        if (!$db->tableExists($table)) {
            return 0;
        }

        return (int) $db->table($table)->countAllResults();
    }

    private function lastSevenDays(): array
    {
        $days = [];

        for ($i = 6; $i >= 0; $i--) {
            $time = strtotime("-{$i} days");
            $days[] = [
                'date' => date('Y-m-d', $time),
                'label' => date('d M', $time),
            ];
        }

        return $days;
    }

    private function emptyDailySeries(array $dates): array
    {
        return array_fill_keys($dates, 0);
    }

    private function dailyMessageCounts($db, array $dates): array
    {
        $series = $this->emptyDailySeries($dates);

        if (!$db->tableExists('wa_messages')) {
            return $series;
        }

        $rows = $db->table('wa_messages')
            ->select('DATE(COALESCE(sent_at, created_at)) AS day, COUNT(*) AS total', false)
            ->where('DATE(COALESCE(sent_at, created_at)) >= ' . $db->escape($dates[0]), null, false)
            ->groupBy('day', false)
            ->get()
            ->getResultArray();

        foreach ($rows as $row) {
            if (array_key_exists($row['day'], $series)) {
                $series[$row['day']] = (int) $row['total'];
            }
        }

        return $series;
    }

    private function dailyQuestionerCounts($db, array $dates): array
    {
        $series = $this->emptyDailySeries($dates);

        if (!$db->tableExists('wa_chats')) {
            return $series;
        }

        $rows = $db->table('wa_chats')
            ->select('DATE(created_at) AS day, COUNT(DISTINCT wa_number) AS total', false)
            ->where('DATE(created_at) >= ' . $db->escape($dates[0]), null, false)
            ->groupBy('day', false)
            ->get()
            ->getResultArray();

        foreach ($rows as $row) {
            if (array_key_exists($row['day'], $series)) {
                $series[$row['day']] = (int) $row['total'];
            }
        }

        return $series;
    }

    public function scanWhatsapp(): string
    {
        return view('dashboard/scan_whatsapp');
    }

    public function supportChat(): string
    {
        return view('dashboard/support_chat');
    }

    public function historyChat(): string
    {
        return view('dashboard/history_chat');
    }

    public function deleteHistoryChat(int $id): RedirectResponse
    {
        $db = db_connect();
        $chat = $db->table('wa_chats')->where('id', $id)->get()->getRowArray();

        if (!$chat) {
            return redirect()->to(site_url('dashboard/history-chat'))->with('error', 'Riwayat chat tidak ditemukan.');
        }

        $messageIds = array_column(
            $db->table('wa_messages')
                ->select('id')
                ->where('chat_id', $id)
                ->get()
                ->getResultArray(),
            'id'
        );

        $db->transStart();

        if ($messageIds && $db->tableExists('chatbot_training_data')) {
            $db->table('chatbot_training_data')
                ->whereIn('source_message_id', array_map('intval', $messageIds))
                ->delete();
        }

        if ($db->tableExists('wa_support_tickets')) {
            $db->table('wa_support_tickets')->where('chat_id', $id)->delete();
        }

        $db->table('wa_messages')->where('chat_id', $id)->delete();
        $db->table('wa_chats')->where('id', $id)->delete();

        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->to(site_url('dashboard/history-chat'))->with('error', 'Gagal menghapus riwayat chat.');
        }

        return redirect()->to(site_url('dashboard/history-chat'))->with('success', 'Riwayat chat berhasil dihapus.');
    }

    public function profile(): string|RedirectResponse
    {
        $this->ensureAdminProfileSchema();

        $admin = $this->currentAdmin();

        if (!$admin) {
            return redirect()->to(site_url('admin/logout'));
        }

        return view('dashboard/profile', [
            'admin' => $admin,
        ]);
    }

    public function updateProfile(): RedirectResponse
    {
        $this->ensureAdminProfileSchema();

        $admin = $this->currentAdmin();

        if (!$admin) {
            return redirect()->to(site_url('admin/logout'));
        }

        $name = trim((string) $this->request->getPost('name'));
        $email = trim((string) $this->request->getPost('email'));
        $currentPassword = (string) $this->request->getPost('current_password');
        $newPassword = (string) $this->request->getPost('new_password');
        $confirmPassword = (string) $this->request->getPost('confirm_password');

        if ($name === '' || $email === '') {
            return redirect()->back()->withInput()->with('error', 'Nama dan email wajib diisi.');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return redirect()->back()->withInput()->with('error', 'Format email tidak valid.');
        }

        $db = db_connect();
        $duplicateEmail = $db->table('admin_users')
            ->where('email', $email)
            ->where('id !=', (int) $admin['id'])
            ->countAllResults();

        if ($duplicateEmail > 0) {
            return redirect()->back()->withInput()->with('error', 'Email sudah digunakan admin lain.');
        }

        $payload = [
            'name' => $name,
            'email' => $email,
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        if ($newPassword !== '' || $confirmPassword !== '' || $currentPassword !== '') {
            if (!password_verify($currentPassword, $admin['password_hash'])) {
                return redirect()->back()->withInput()->with('error', 'Password saat ini tidak sesuai.');
            }

            if (strlen($newPassword) < 6) {
                return redirect()->back()->withInput()->with('error', 'Password baru minimal 6 karakter.');
            }

            if ($newPassword !== $confirmPassword) {
                return redirect()->back()->withInput()->with('error', 'Konfirmasi password baru belum sama.');
            }

            $payload['password_hash'] = password_hash($newPassword, PASSWORD_DEFAULT);
        }

        $avatar = $this->request->getFile('avatar');

        if ($avatar && $avatar->isValid() && !$avatar->hasMoved()) {
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
            $extension = strtolower($avatar->getClientExtension());

            if (!in_array($extension, $allowedExtensions, true)) {
                return redirect()->back()->withInput()->with('error', 'Foto profil harus JPG, PNG, atau WEBP.');
            }

            if ($avatar->getSizeByUnit('mb') > 2) {
                return redirect()->back()->withInput()->with('error', 'Ukuran foto profil maksimal 2 MB.');
            }

            $uploadPath = FCPATH . 'uploads' . DIRECTORY_SEPARATOR . 'admin-profiles';

            if (!is_dir($uploadPath)) {
                mkdir($uploadPath, 0775, true);
            }

            $newName = $avatar->getRandomName();
            $avatar->move($uploadPath, $newName);
            $avatarPath = $uploadPath . DIRECTORY_SEPARATOR . $newName;

            service('image')
                ->withFile($avatarPath)
                ->resize(512, 512, true, 'auto')
                ->save($avatarPath, 85);

            $payload['avatar_url'] = 'uploads/admin-profiles/' . $newName;

            if (!empty($admin['avatar_url'])) {
                $oldAvatar = FCPATH . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $admin['avatar_url']);

                if (is_file($oldAvatar) && str_starts_with(realpath($oldAvatar), realpath($uploadPath))) {
                    @unlink($oldAvatar);
                }
            }
        }

        $db->table('admin_users')->where('id', (int) $admin['id'])->update($payload);

        session()->set([
            'admin_name' => $payload['name'],
            'admin_email' => $payload['email'],
            'admin_avatar' => $payload['avatar_url'] ?? ($admin['avatar_url'] ?? null),
        ]);

        return redirect()->to(site_url('dashboard/profile'))->with('success', 'Profil admin berhasil diperbarui.');
    }

    private function currentAdmin(): ?array
    {
        $adminId = (int) session('admin_id');

        if ($adminId < 1) {
            return null;
        }

        return db_connect()->table('admin_users')
            ->where('id', $adminId)
            ->get()
            ->getRowArray() ?: null;
    }

    public function knowledgeBase(): string
    {
        return $this->intents();
    }

    public function intents(): string
    {
        $model = new ChatbotIntentModel();
        $model->ensureSchema();

        $keyword = trim((string) $this->request->getGet('q'));
        $status = trim((string) $this->request->getGet('status'));

        return view('dashboard/intents/index', [
            'items' => $model->getIntentRows($keyword, $status),
            'keyword' => $keyword,
            'status' => $status,
        ]);
    }

    public function createKnowledgeBase(): string
    {
        return $this->createIntent();
    }

    public function createIntent(): string
    {
        return view('dashboard/intents/form', [
            'mode' => 'create',
            'item' => [
                'name' => '',
                'response' => '',
                'status' => 'active',
                'priority' => 0,
                'source' => 'manual',
            ],
        ]);
    }

    public function storeKnowledgeBase(): RedirectResponse
    {
        return $this->storeIntent();
    }

    public function storeIntent(): RedirectResponse
    {
        $model = new ChatbotIntentModel();

        try {
            $model->createIntent($this->request->getPost());
        } catch (\InvalidArgumentException $error) {
            return redirect()->back()->withInput()->with('error', $error->getMessage());
        }

        return redirect()->to(site_url('dashboard/intents'))->with('success', 'Intent berhasil ditambahkan.');
    }

    public function editKnowledgeBase(int $id): string|RedirectResponse
    {
        return $this->editIntent($id);
    }

    public function editIntent(int $id): string|RedirectResponse
    {
        $model = new ChatbotIntentModel();
        $model->ensureSchema();
        $item = $model->find($id);

        if (!$item) {
            return redirect()->to(site_url('dashboard/intents'))->with('error', 'Intent tidak ditemukan.');
        }

        return view('dashboard/intents/form', [
            'mode' => 'edit',
            'item' => $item,
        ]);
    }

    public function updateKnowledgeBase(int $id): RedirectResponse
    {
        return $this->updateIntent($id);
    }

    public function updateIntent(int $id): RedirectResponse
    {
        $model = new ChatbotIntentModel();

        try {
            $model->updateIntentRow($id, $this->request->getPost());
        } catch (\InvalidArgumentException $error) {
            return redirect()->back()->withInput()->with('error', $error->getMessage());
        }

        return redirect()->to(site_url('dashboard/intents'))->with('success', 'Intent berhasil diperbarui.');
    }

    public function toggleKnowledgeBase(int $id): RedirectResponse
    {
        $model = new ChatbotIntentModel();
        $model->ensureSchema();
        $item = $model->find($id);

        if (!$item) {
            return redirect()->to(site_url('dashboard/knowledge-base'))->with('error', 'Data knowledge base tidak ditemukan.');
        }

        $model->update($id, [
            'status' => $item['status'] === 'active' ? 'inactive' : 'active',
        ]);

        return redirect()->to(site_url('dashboard/intents'))->with('success', 'Status intent berhasil diubah.');
    }

    public function deleteKnowledgeBase(int $id): RedirectResponse
    {
        return $this->deleteIntent($id);
    }

    public function deleteIntent(int $id): RedirectResponse
    {
        $model = new ChatbotIntentModel();

        try {
            $model->deleteIntentRow($id);
        } catch (\InvalidArgumentException $error) {
            return redirect()->back()->with('error', $error->getMessage());
        }

        return redirect()->to(site_url('dashboard/intents'))->with('success', 'Intent berhasil dihapus.');
    }

    public function trainingPhrases(): string
    {
        $model = new ChatbotIntentModel();
        $keyword = trim((string) $this->request->getGet('q'));
        $intentId = (int) $this->request->getGet('intent_id');

        return view('dashboard/training_phrases/index', [
            'items' => $model->getTrainingPhraseRows($keyword, $intentId),
            'intents' => $model->getSimpleIntents(),
            'trainingSummary' => $model->getTrainingPhraseSummary(),
            'keyword' => $keyword,
            'intentId' => $intentId,
        ]);
    }

    public function storeTrainingPhrase(): RedirectResponse
    {
        $model = new ChatbotIntentModel();

        try {
            $model->createTrainingPhrase($this->request->getPost());
        } catch (\InvalidArgumentException $error) {
            return redirect()->back()->withInput()->with('error', $error->getMessage());
        }

        return redirect()->to(site_url('dashboard/training-phrases'))->with('success', 'Training phrase berhasil ditambahkan.');
    }

    public function updateTrainingPhrase(int $id): RedirectResponse
    {
        $model = new ChatbotIntentModel();

        try {
            $model->updateTrainingPhrase($id, $this->request->getPost());
        } catch (\InvalidArgumentException $error) {
            return redirect()->back()->withInput()->with('error', $error->getMessage());
        }

        return redirect()->to(site_url('dashboard/training-phrases'))->with('success', 'Training phrase berhasil diperbarui.');
    }

    public function deleteTrainingPhrase(int $id): RedirectResponse
    {
        (new ChatbotIntentModel())->deleteTrainingPhrase($id);

        return redirect()->to(site_url('dashboard/training-phrases'))->with('success', 'Training phrase berhasil dihapus.');
    }

    public function keywords(): string
    {
        $model = new ChatbotIntentModel();
        $keyword = trim((string) $this->request->getGet('q'));
        $intentId = (int) $this->request->getGet('intent_id');

        return view('dashboard/keywords/index', [
            'items' => $model->getKeywordRows($keyword, $intentId),
            'intents' => $model->getSimpleIntents(),
            'keyword' => $keyword,
            'intentId' => $intentId,
        ]);
    }

    public function storeKeyword(): RedirectResponse
    {
        $model = new ChatbotIntentModel();

        try {
            $model->createKeyword($this->request->getPost());
        } catch (\InvalidArgumentException $error) {
            return redirect()->back()->withInput()->with('error', $error->getMessage());
        }

        return redirect()->to(site_url('dashboard/keywords'))->with('success', 'Keyword berhasil ditambahkan.');
    }

    public function updateKeyword(int $id): RedirectResponse
    {
        $model = new ChatbotIntentModel();

        try {
            $model->updateKeyword($id, $this->request->getPost());
        } catch (\InvalidArgumentException $error) {
            return redirect()->back()->withInput()->with('error', $error->getMessage());
        }

        return redirect()->to(site_url('dashboard/keywords'))->with('success', 'Keyword berhasil diperbarui.');
    }

    public function deleteKeyword(int $id): RedirectResponse
    {
        (new ChatbotIntentModel())->deleteKeyword($id);

        return redirect()->to(site_url('dashboard/keywords'))->with('success', 'Keyword berhasil dihapus.');
    }

    public function nlpRules(): string
    {
        $model = new ChatbotIntentModel();

        return view('dashboard/nlp_rules/index', $model->getNlpRuleDataset());
    }

    public function storeNlpRule(string $type): RedirectResponse
    {
        $model = new ChatbotIntentModel();

        try {
            $model->createNlpRule($type, $this->request->getPost());
        } catch (\InvalidArgumentException $error) {
            return redirect()->back()->withInput()->with('error', $error->getMessage());
        }

        return redirect()->to(site_url('dashboard/nlp-rules') . '#' . $type)->with('success', 'Rule NLP berhasil ditambahkan.');
    }

    public function updateNlpRule(string $type, int $id): RedirectResponse
    {
        $model = new ChatbotIntentModel();

        try {
            $model->updateNlpRule($type, $id, $this->request->getPost());
        } catch (\InvalidArgumentException $error) {
            return redirect()->back()->withInput()->with('error', $error->getMessage());
        }

        return redirect()->to(site_url('dashboard/nlp-rules') . '#' . $type)->with('success', 'Rule NLP berhasil diperbarui.');
    }

    public function deleteNlpRule(string $type, int $id): RedirectResponse
    {
        $model = new ChatbotIntentModel();

        try {
            $model->deleteNlpRule($type, $id);
        } catch (\InvalidArgumentException $error) {
            return redirect()->back()->with('error', $error->getMessage());
        }

        return redirect()->to(site_url('dashboard/nlp-rules') . '#' . $type)->with('success', 'Rule NLP berhasil dihapus.');
    }

}
