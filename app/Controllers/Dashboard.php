<?php

namespace App\Controllers;

use App\Models\ChatbotIntentModel;
use App\Models\LandingPageModel;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\ResponseInterface;
use Dompdf\Dompdf;
use Dompdf\Options;

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

    public function whatsappServerStatus(): ResponseInterface
    {
        return $this->response->setJSON([
            'running' => $this->isWhatsappServerRunning(),
            'port' => 3001,
        ]);
    }

    public function startWhatsappServer(): ResponseInterface
    {
        if ($this->isWhatsappServerRunning()) {
            return $this->response->setJSON([
                'ok' => true,
                'running' => true,
                'message' => 'WhatsApp server sudah berjalan.',
            ]);
        }

        $script = ROOTPATH . 'start-wa-server.bat';

        if (!is_file($script)) {
            return $this->response->setStatusCode(500)->setJSON([
                'ok' => false,
                'running' => false,
                'message' => 'File start-wa-server.bat tidak ditemukan.',
            ]);
        }

        $command = 'start "WhatsApp Server" /min cmd /c "' . $script . '"';
        pclose(popen($command, 'r'));
        usleep(700000);

        return $this->response->setJSON([
            'ok' => true,
            'running' => $this->isWhatsappServerRunning(),
            'message' => 'Perintah start WhatsApp server sudah dikirim.',
        ]);
    }

    public function stopWhatsappServer(): ResponseInterface
    {
        $command = 'powershell -NoProfile -ExecutionPolicy Bypass -Command "$connections = Get-NetTCPConnection -LocalPort 3001 -State Listen -ErrorAction SilentlyContinue; if ($connections) { $ids = $connections | Select-Object -ExpandProperty OwningProcess -Unique; foreach ($processId in $ids) { $process = Get-Process -Id $processId -ErrorAction SilentlyContinue; if ($process -and $process.ProcessName -like \"node*\") { Stop-Process -Id $processId -Force } } }"';
        exec($command);
        usleep(500000);

        return $this->response->setJSON([
            'ok' => true,
            'running' => $this->isWhatsappServerRunning(),
            'message' => 'Perintah stop WhatsApp server sudah dikirim.',
        ]);
    }

    private function isWhatsappServerRunning(): bool
    {
        $connection = @fsockopen('127.0.0.1', 3001, $errno, $errstr, 0.2);

        if (is_resource($connection)) {
            fclose($connection);

            return true;
        }

        return false;
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

    public function landingPage(): string
    {
        $model = new LandingPageModel();

        return view('dashboard/landing_page/index', [
            'settings' => $model->getSettings(),
            'programs' => $model->getPrograms(),
            'staffItems' => $model->getStaff(),
            'newsItems' => $model->getNews(),
        ]);
    }

    public function updateLandingSettings(): RedirectResponse
    {
        $model = new LandingPageModel();
        $settings = $model->getSettings();
        $payload = $this->request->getPost();

        try {
            $logoUrl = $this->uploadLandingImage('logo', 'landing-logo', 1024, 1024);
            $heroUrl = $this->uploadLandingImage('hero_image', 'landing-hero', 1600, 900);

            if ($logoUrl !== null) {
                $payload['logo_url'] = $logoUrl;
                $this->deleteUploadedLandingAsset($settings['logo_url'] ?? '');
            }

            if ($heroUrl !== null) {
                $payload['hero_image_url'] = $heroUrl;
                $this->deleteUploadedLandingAsset($settings['hero_image_url'] ?? '');
            }

            $model->updateSettings($payload);
        } catch (\InvalidArgumentException $error) {
            return redirect()->back()->withInput()->with('error', $error->getMessage());
        }

        return redirect()->to(site_url('dashboard/landing-page'))->with('success', 'Pengaturan landing page berhasil disimpan.');
    }

    public function updateLandingProgram(int $id): RedirectResponse
    {
        $model = new LandingPageModel();

        try {
            $model->updateProgram($id, $this->request->getPost());
        } catch (\InvalidArgumentException $error) {
            return redirect()->back()->withInput()->with('error', $error->getMessage());
        }

        return redirect()->to(site_url('dashboard/landing-page') . '#program')->with('success', 'Program landing page berhasil diperbarui.');
    }

    public function storeLandingStaff(): RedirectResponse
    {
        $model = new LandingPageModel();

        try {
            $model->createStaff($this->request->getPost(), $this->uploadLandingImage('photo', 'landing-staff', 700, 700));
        } catch (\InvalidArgumentException $error) {
            return redirect()->back()->withInput()->with('error', $error->getMessage());
        }

        return redirect()->to(site_url('dashboard/landing-page') . '#tenaga-pendidik')->with('success', 'Tenaga pendidik berhasil ditambahkan.');
    }

    public function updateLandingStaff(int $id): RedirectResponse
    {
        $model = new LandingPageModel();
        $item = $model->findStaff($id);

        if (!$item) {
            return redirect()->to(site_url('dashboard/landing-page') . '#tenaga-pendidik')->with('error', 'Data tenaga pendidik tidak ditemukan.');
        }

        try {
            $photoUrl = $this->uploadLandingImage('photo', 'landing-staff', 700, 700);
            $model->updateStaff($id, $this->request->getPost(), $photoUrl);

            if ($photoUrl !== null) {
                $this->deleteUploadedLandingAsset($item['photo_url'] ?? '');
            }
        } catch (\InvalidArgumentException $error) {
            return redirect()->back()->withInput()->with('error', $error->getMessage());
        }

        return redirect()->to(site_url('dashboard/landing-page') . '#tenaga-pendidik')->with('success', 'Tenaga pendidik berhasil diperbarui.');
    }

    public function deleteLandingStaff(int $id): RedirectResponse
    {
        $model = new LandingPageModel();
        $item = $model->findStaff($id);

        if ($item) {
            $model->deleteStaff($id);
            $this->deleteUploadedLandingAsset($item['photo_url'] ?? '');
        }

        return redirect()->to(site_url('dashboard/landing-page') . '#tenaga-pendidik')->with('success', 'Tenaga pendidik berhasil dihapus.');
    }

    public function storeLandingNews(): RedirectResponse
    {
        $model = new LandingPageModel();

        try {
            $model->createNews($this->request->getPost(), $this->uploadLandingImage('image', 'landing-news', 900, 520));
        } catch (\InvalidArgumentException $error) {
            return redirect()->back()->withInput()->with('error', $error->getMessage());
        }

        return redirect()->to(site_url('dashboard/landing-page') . '#berita')->with('success', 'Berita landing page berhasil ditambahkan.');
    }

    public function updateLandingNews(int $id): RedirectResponse
    {
        $model = new LandingPageModel();
        $item = $model->findNews($id);

        if (!$item) {
            return redirect()->to(site_url('dashboard/landing-page') . '#berita')->with('error', 'Berita tidak ditemukan.');
        }

        try {
            $imageUrl = $this->uploadLandingImage('image', 'landing-news', 900, 520);
            $model->updateNews($id, $this->request->getPost(), $imageUrl);

            if ($imageUrl !== null) {
                $this->deleteUploadedLandingAsset($item['image_url'] ?? '');
            }
        } catch (\InvalidArgumentException $error) {
            return redirect()->back()->withInput()->with('error', $error->getMessage());
        }

        return redirect()->to(site_url('dashboard/landing-page') . '#berita')->with('success', 'Berita landing page berhasil diperbarui.');
    }

    public function deleteLandingNews(int $id): RedirectResponse
    {
        $model = new LandingPageModel();
        $item = $model->findNews($id);

        if ($item) {
            $model->deleteNews($id);
            $this->deleteUploadedLandingAsset($item['image_url'] ?? '');
        }

        return redirect()->to(site_url('dashboard/landing-page') . '#berita')->with('success', 'Berita landing page berhasil dihapus.');
    }

    private function uploadLandingImage(string $field, string $folder, int $width, int $height): ?string
    {
        $file = $this->request->getFile($field);

        if (!$file || !$file->isValid() || $file->hasMoved()) {
            return null;
        }

        $extension = strtolower($file->getClientExtension());

        if (!in_array($extension, ['jpg', 'jpeg', 'png', 'webp'], true)) {
            throw new \InvalidArgumentException('Gambar harus berformat JPG, PNG, atau WEBP.');
        }

        if ($file->getSizeByUnit('mb') > 4) {
            throw new \InvalidArgumentException('Ukuran gambar maksimal 4 MB.');
        }

        $uploadPath = FCPATH . 'uploads' . DIRECTORY_SEPARATOR . $folder;

        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0775, true);
        }

        $newName = $file->getRandomName();
        $file->move($uploadPath, $newName);
        $imagePath = $uploadPath . DIRECTORY_SEPARATOR . $newName;

        service('image')
            ->withFile($imagePath)
            ->resize($width, $height, true, 'auto')
            ->save($imagePath, 85);

        return 'uploads/' . $folder . '/' . $newName;
    }

    private function deleteUploadedLandingAsset(string $assetPath): void
    {
        if (!str_starts_with($assetPath, 'uploads/landing-')) {
            return;
        }

        $path = FCPATH . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $assetPath);
        $realPath = realpath($path);
        $uploadsRoot = realpath(FCPATH . 'uploads');

        if ($realPath && $uploadsRoot && str_starts_with($realPath, $uploadsRoot) && is_file($realPath)) {
            @unlink($realPath);
        }
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
            'vectorizerStatus' => $model->getCountVectorizerStatus(),
            'naiveBayesEvaluation' => $model->getLatestNaiveBayesEvaluation(),
            'keyword' => $keyword,
            'intentId' => $intentId,
        ]);
    }

    public function retrainTrainingPhrases(): RedirectResponse
    {
        $model = new ChatbotIntentModel();
        $trained = $model->trainCountVectorizerModel();
        $stats = $trained['stats'] ?? [];
        $message = sprintf(
            'Training ulang selesai: %d intent, %d phrase, %d kata vocabulary.',
            (int) ($stats['intent_count'] ?? 0),
            (int) ($stats['phrase_count'] ?? 0),
            (int) ($stats['vocabulary_size'] ?? 0)
        );

        return redirect()->to(site_url('dashboard/training-phrases'))->with('success', $message);
    }

    public function evaluateNaiveBayes(): RedirectResponse
    {
        $model = new ChatbotIntentModel();
        $result = $model->evaluateNaiveBayesHoldOut(0.8);
        $summary = $result['summary'] ?? [];
        $message = sprintf(
            'Pengujian Naive Bayes selesai: akurasi %.2f%% dari %d data uji.',
            ((float) ($summary['accuracy'] ?? 0)) * 100,
            (int) ($summary['test_samples'] ?? 0)
        );

        return redirect()->to(site_url('dashboard/training-phrases'))
            ->with('success', $message)
            ->with('showNaiveBayesModal', true);
    }

    public function downloadNaiveBayesPdf(): ResponseInterface|RedirectResponse
    {
        $model = new ChatbotIntentModel();
        $evaluation = $model->getLatestNaiveBayesEvaluation();

        if (!$evaluation) {
            return redirect()->to(site_url('dashboard/training-phrases'))
                ->with('error', 'Belum ada hasil pengujian Naive Bayes untuk disimpan.');
        }

        $pdf = $this->buildNaiveBayesEvaluationPdf($evaluation);
        $filename = 'hasil-uji-naive-bayes-' . date('Ymd-His') . '.pdf';

        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->setBody($pdf);
    }

    public function downloadNaiveBayesExcel(): ResponseInterface|RedirectResponse
    {
        $model = new ChatbotIntentModel();
        $evaluation = $model->getLatestNaiveBayesEvaluation();

        if (!$evaluation) {
            return redirect()->to(site_url('dashboard/training-phrases'))
                ->with('error', 'Belum ada hasil pengujian Naive Bayes untuk disimpan.');
        }

        $excel = $this->buildNaiveBayesEvaluationExcel($evaluation);
        $filename = 'hasil-uji-naive-bayes-' . date('Ymd-His') . '.xls';

        return $this->response
            ->setHeader('Content-Type', 'application/vnd.ms-excel; charset=UTF-8')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->setBody($excel);
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

    private function buildNaiveBayesEvaluationPdf(array $evaluation): string
    {
        $html = $this->buildNaiveBayesEvaluationHtml($evaluation);
        $options = new Options();
        $options->set('defaultFont', 'DejaVu Sans');
        $options->set('isRemoteEnabled', false);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        return $dompdf->output();
    }

    private function buildNaiveBayesEvaluationExcel(array $evaluation): string
    {
        $summary = $evaluation['summary'] ?? [];
        $perClass = $evaluation['per_class'] ?? [];
        $confusionMatrix = $evaluation['confusion_matrix'] ?? [];
        $formatPercent = static fn ($value): string => number_format(((float) $value) * 100, 2) . '%';
        $html = '
            <html>
            <head>
                <meta charset="UTF-8">
                <style>
                    table { border-collapse: collapse; font-family: Arial, sans-serif; font-size: 12px; margin-bottom: 18px; }
                    th { background: #104f86; color: #ffffff; font-weight: bold; }
                    th, td { border: 1px solid #b7c9d9; padding: 7px 9px; text-align: left; }
                    .title { background: #0d2f4f; color: #ffffff; font-size: 18px; font-weight: bold; }
                    .section { background: #edf6fb; color: #104f86; font-weight: bold; }
                </style>
            </head>
            <body>
                <table>
                    <tr><td class="title" colspan="2">Hasil Pengujian Naive Bayes</td></tr>
                    <tr><td>Metode</td><td>Hold Out 80% data latih / 20% data uji</td></tr>
                    <tr><td>Tanggal Uji</td><td>' . esc($evaluation['evaluated_at'] ?? '-') . '</td></tr>
                </table>

                <table>
                    <tr><td class="section" colspan="2">Ringkasan</td></tr>
                    <tr><td>Data Latih</td><td>' . esc((string) ($summary['train_samples'] ?? 0)) . '</td></tr>
                    <tr><td>Data Uji</td><td>' . esc((string) ($summary['test_samples'] ?? 0)) . '</td></tr>
                    <tr><td>Jumlah Intent</td><td>' . esc((string) ($summary['intent_count'] ?? 0)) . '</td></tr>
                    <tr><td>Accuracy</td><td>' . esc($formatPercent($summary['accuracy'] ?? 0)) . '</td></tr>
                    <tr><td>Macro Precision</td><td>' . esc($formatPercent($summary['macro_precision'] ?? 0)) . '</td></tr>
                    <tr><td>Macro Recall</td><td>' . esc($formatPercent($summary['macro_recall'] ?? 0)) . '</td></tr>
                    <tr><td>Macro F1</td><td>' . esc($formatPercent($summary['macro_f1'] ?? 0)) . '</td></tr>
                    <tr><td>Weighted F1</td><td>' . esc($formatPercent($summary['weighted_f1'] ?? 0)) . '</td></tr>
                    <tr><td>Prediksi Unknown</td><td>' . esc((string) ($summary['unmatched_predictions'] ?? 0)) . '</td></tr>
                </table>

                <table>
                    <tr><td class="section" colspan="5">Metrik Per Intent</td></tr>
                    <tr>
                        <th>Intent</th>
                        <th>Support</th>
                        <th>Precision</th>
                        <th>Recall</th>
                        <th>F1-score</th>
                    </tr>';

        foreach ($perClass as $intent => $metrics) {
            $html .= '
                    <tr>
                        <td>' . esc($intent) . '</td>
                        <td>' . esc((string) ($metrics['support'] ?? 0)) . '</td>
                        <td>' . esc($formatPercent($metrics['precision'] ?? 0)) . '</td>
                        <td>' . esc($formatPercent($metrics['recall'] ?? 0)) . '</td>
                        <td>' . esc($formatPercent($metrics['f1'] ?? 0)) . '</td>
                    </tr>';
        }

        $html .= '</table>';

        if ($confusionMatrix) {
            $labels = array_keys($confusionMatrix);
            $colspan = count($labels) + 2;
            $html .= '
                <table>
                    <tr><td class="section" colspan="' . esc((string) $colspan) . '">Confusion Matrix</td></tr>
                    <tr>
                        <th>Actual \ Predicted</th>';

            foreach ($labels as $label) {
                $html .= '<th>' . esc($label) . '</th>';
            }

            $html .= '<th>Unknown</th></tr>';

            foreach ($confusionMatrix as $actual => $predictions) {
                $html .= '<tr><td>' . esc($actual) . '</td>';

                foreach ($labels as $label) {
                    $html .= '<td>' . esc((string) ($predictions[$label] ?? 0)) . '</td>';
                }

                $html .= '<td>' . esc((string) ($predictions['__unknown__'] ?? 0)) . '</td></tr>';
            }

            $html .= '</table>';
        }

        return $html . '</body></html>';
    }

    private function buildNaiveBayesEvaluationHtml(array $evaluation): string
    {
        $summary = $evaluation['summary'] ?? [];
        $perClass = $evaluation['per_class'] ?? [];
        $confusionMatrix = $evaluation['confusion_matrix'] ?? [];
        $formatPercent = static fn ($value): string => number_format(((float) $value) * 100, 2) . '%';
        $labels = array_keys($confusionMatrix);
        $html = '
            <!doctype html>
            <html>
            <head>
                <meta charset="utf-8">
                <style>
                    @page { margin: 34px 36px; }
                    body { color: #172033; font-family: "DejaVu Sans", sans-serif; font-size: 11px; line-height: 1.45; }
                    .header { background: #0f766e; border-radius: 14px; color: #fff; padding: 22px 24px; }
                    .eyebrow { font-size: 10px; letter-spacing: 1px; margin: 0 0 5px; text-transform: uppercase; }
                    h1 { font-size: 24px; line-height: 1.1; margin: 0 0 8px; }
                    .subtitle { color: #d9fffa; margin: 0; }
                    .meta { margin-top: 14px; }
                    .meta span { background: rgba(255,255,255,.14); border-radius: 999px; display: inline-block; margin: 0 6px 6px 0; padding: 5px 10px; }
                    h2 { border-bottom: 2px solid #dbe7e5; color: #0f766e; font-size: 15px; margin: 24px 0 12px; padding-bottom: 6px; }
                    .metric-grid { width: 100%; border-collapse: separate; border-spacing: 8px; margin: 16px -8px 6px; }
                    .metric { background: #f4fbfa; border: 1px solid #cfe6e3; border-radius: 10px; padding: 12px; }
                    .metric span { color: #5b6b7a; display: block; font-size: 9px; margin-bottom: 5px; text-transform: uppercase; }
                    .metric strong { color: #0f3f3a; display: block; font-size: 18px; }
                    table.data { border-collapse: collapse; margin-top: 8px; table-layout: fixed; width: 100%; }
                    table.data th { background: #e7f3f1; color: #0f3f3a; font-size: 10px; text-align: left; }
                    table.data th, table.data td { border: 1px solid #d7e2e0; padding: 7px 8px; vertical-align: top; }
                    table.data tr:nth-child(even) td { background: #fafdfd; }
                    table.matrix { font-size: 8px; page-break-inside: avoid; }
                    table.matrix th, table.matrix td { padding: 5px 4px; text-align: center; word-break: break-word; }
                    table.matrix th:first-child, table.matrix td:first-child { text-align: left; width: 130px; }
                    .intent { background: #eef2ff; border-radius: 999px; color: #3730a3; display: inline-block; font-size: 10px; padding: 3px 7px; }
                    table.matrix .intent { border-radius: 6px; font-size: 8px; padding: 2px 4px; }
                    .matrix-note { color: #64748b; font-size: 9px; margin: -4px 0 8px; }
                    .footer { border-top: 1px solid #dbe7e5; color: #718096; font-size: 9px; margin-top: 24px; padding-top: 10px; text-align: right; }
                </style>
            </head>
            <body>
                <div class="header">
                    <p class="eyebrow">Laporan Evaluasi Dataset Chatbot</p>
                    <h1>Hasil Pengujian Naive Bayes</h1>
                    <p class="subtitle">Metode hold out dengan 80% data latih dan 20% data uji.</p>
                    <div class="meta">
                        <span>Tanggal uji: ' . esc($evaluation['evaluated_at'] ?? '-') . '</span>
                        <span>Intent: ' . esc((string) ($summary['intent_count'] ?? 0)) . '</span>
                        <span>Data uji: ' . esc((string) ($summary['test_samples'] ?? 0)) . '</span>
                    </div>
                </div>

                <table class="metric-grid">
                    <tr>
                        <td class="metric"><span>Accuracy</span><strong>' . esc($formatPercent($summary['accuracy'] ?? 0)) . '</strong></td>
                        <td class="metric"><span>Macro Precision</span><strong>' . esc($formatPercent($summary['macro_precision'] ?? 0)) . '</strong></td>
                        <td class="metric"><span>Macro Recall</span><strong>' . esc($formatPercent($summary['macro_recall'] ?? 0)) . '</strong></td>
                    </tr>
                    <tr>
                        <td class="metric"><span>Macro F1</span><strong>' . esc($formatPercent($summary['macro_f1'] ?? 0)) . '</strong></td>
                        <td class="metric"><span>Weighted F1</span><strong>' . esc($formatPercent($summary['weighted_f1'] ?? 0)) . '</strong></td>
                        <td class="metric"><span>Data Latih / Uji</span><strong>' . esc((string) ($summary['train_samples'] ?? 0)) . ' / ' . esc((string) ($summary['test_samples'] ?? 0)) . '</strong></td>
                    </tr>
                </table>

                <h2>Metrik Per Intent</h2>
                <table class="data">
                    <thead>
                        <tr>
                            <th>Intent</th>
                            <th>Support</th>
                            <th>Precision</th>
                            <th>Recall</th>
                            <th>F1-score</th>
                        </tr>
                    </thead>
                    <tbody>';

        foreach ($perClass as $intent => $metrics) {
            $html .= '
                        <tr>
                            <td><span class="intent">' . esc($intent) . '</span></td>
                            <td>' . esc((string) ($metrics['support'] ?? 0)) . '</td>
                            <td>' . esc($formatPercent($metrics['precision'] ?? 0)) . '</td>
                            <td>' . esc($formatPercent($metrics['recall'] ?? 0)) . '</td>
                            <td>' . esc($formatPercent($metrics['f1'] ?? 0)) . '</td>
                        </tr>';
        }

        $html .= '
                    </tbody>
                </table>';

        if ($confusionMatrix) {
            $labelChunks = array_chunk($labels, 5);
            $html .= '
                <h2>Confusion Matrix</h2>
                <p class="matrix-note">Kolom prediksi dipecah per bagian supaya tabel tidak terpotong saat disimpan ke PDF.</p>';

            foreach ($labelChunks as $chunkIndex => $labelChunk) {
                $html .= '
                <table class="data matrix">
                    <thead>
                        <tr>
                            <th>Actual \ Predicted</th>';

                foreach ($labelChunk as $label) {
                    $html .= '<th>' . esc($label) . '</th>';
                }

                if ($chunkIndex === count($labelChunks) - 1) {
                    $html .= '<th>Unknown</th>';
                }

                $html .= '</tr></thead><tbody>';

                foreach ($confusionMatrix as $actual => $predictions) {
                    $html .= '<tr><td><span class="intent">' . esc($actual) . '</span></td>';

                    foreach ($labelChunk as $label) {
                        $html .= '<td>' . esc((string) ($predictions[$label] ?? 0)) . '</td>';
                    }

                    if ($chunkIndex === count($labelChunks) - 1) {
                        $html .= '<td>' . esc((string) ($predictions['__unknown__'] ?? 0)) . '</td>';
                    }

                    $html .= '</tr>';
                }

                $html .= '</tbody></table>';
            }
        }

        return $html . '<div class="footer">Generated by Dashboard Chatbot PPDB</div></body></html>';
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
