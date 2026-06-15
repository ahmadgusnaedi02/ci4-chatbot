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

        service('permissions')->ensureSchema();
    }

    public function index(): string
    {
        return view('dashboard/index_dashboard', $this->getDashboardSummary());

    }

    public function rolePermissions(): string
    {
        $permissions = service('permissions');
        $selectedRole = $permissions->normalizeRole((string) ($this->request->getGet('role') ?: 'admin_spmb'));

        if ($selectedRole === 'super_admin') {
            $selectedRole = 'admin_spmb';
        }

        return view('dashboard/role_permissions/index', [
            'roles' => $permissions->getRoles(),
            'assignableRoles' => $permissions->getAssignableRoles(),
            'menus' => $permissions->getMenus(),
            'adminUsers' => $permissions->getAdminUsers(),
            'selectedRole' => $selectedRole,
            'rolePermissions' => $permissions->getPermissionsByRole($selectedRole),
        ]);
    }

    public function updateRolePermissions(): RedirectResponse
    {
        $permissions = service('permissions');
        $role = $permissions->normalizeRole((string) $this->request->getPost('role'));

        if ($role === 'super_admin') {
            return redirect()->to(site_url('dashboard/hak-akses'))->with('error', 'Permission Super Admin dikunci agar selalu memiliki akses penuh.');
        }

        $permissions->updateRolePermissions($role, (array) $this->request->getPost('permissions'));

        return redirect()->to(site_url('dashboard/hak-akses?role=' . $role))->with('success', 'Hak akses role berhasil diperbarui.');
    }

    public function storeAdminRole(): RedirectResponse
    {
        try {
            $role = service('permissions')->createRole(
                (string) $this->request->getPost('name'),
                (string) $this->request->getPost('description')
            );
        } catch (\InvalidArgumentException $exception) {
            return redirect()->back()->withInput()->with('error', $exception->getMessage());
        }

        return redirect()->to(site_url('dashboard/hak-akses?role=' . $role))->with('success', 'Role baru berhasil ditambahkan. Silakan centang hak aksesnya.');
    }

    public function storeAdminUser(): RedirectResponse
    {
        try {
            service('permissions')->createAdminUser(
                (string) $this->request->getPost('name'),
                (string) $this->request->getPost('email'),
                (string) $this->request->getPost('password'),
                (string) $this->request->getPost('role')
            );
        } catch (\InvalidArgumentException $exception) {
            return redirect()->back()->withInput()->with('error', $exception->getMessage());
        }

        return redirect()->to(site_url('dashboard/hak-akses#users'))->with('success', 'User admin baru berhasil ditambahkan.');
    }

    public function updateAdminUser(int $id): RedirectResponse
    {
        try {
            service('permissions')->updateAdminUser(
                $id,
                (string) $this->request->getPost('name'),
                (string) $this->request->getPost('email'),
                (string) $this->request->getPost('role'),
                (string) $this->request->getPost('password')
            );
        } catch (\InvalidArgumentException $exception) {
            return redirect()->back()->withInput()->with('error', $exception->getMessage());
        }

        return redirect()->to(site_url('dashboard/hak-akses#users'))->with('success', 'User admin berhasil diperbarui.');
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

    private function getDashboardLogoDataUri(): string
    {
        $logoPath = FCPATH . 'assets/images/logo-yapas.png';

        if (!is_file($logoPath)) {
            return '';
        }

        $contents = file_get_contents($logoPath);

        if ($contents === false) {
            return '';
        }

        return 'data:image/png;base64,' . base64_encode($contents);
    }

    private function buildNaiveBayesMetricChartHtml(array $perClass, string $format = 'pdf'): string
    {
        if (!$perClass) {
            return '';
        }

        if ($format === 'excel') {
            $html = '
                <table>
                    <tr><td class="section" colspan="4">Grafik Metrik Per Intent</td></tr>
                    <tr>
                        <th>Intent</th>
                        <th>Precision</th>
                        <th>Recall</th>
                        <th>F1-score</th>
                    </tr>';

            foreach ($perClass as $intent => $metrics) {
                $precision = max(0, min(1, (float) ($metrics['precision'] ?? 0)));
                $recall = max(0, min(1, (float) ($metrics['recall'] ?? 0)));
                $f1 = max(0, min(1, (float) ($metrics['f1'] ?? 0)));

                $html .= '
                    <tr>
                        <td>' . esc($intent) . '</td>
                        <td><div class="excel-bar-track"><div class="excel-bar precision" style="width: ' . esc((string) round($precision * 100, 2)) . '%;"></div></div> ' . esc(number_format($precision * 100, 2)) . '%</td>
                        <td><div class="excel-bar-track"><div class="excel-bar recall" style="width: ' . esc((string) round($recall * 100, 2)) . '%;"></div></div> ' . esc(number_format($recall * 100, 2)) . '%</td>
                        <td><div class="excel-bar-track"><div class="excel-bar f1" style="width: ' . esc((string) round($f1 * 100, 2)) . '%;"></div></div> ' . esc(number_format($f1 * 100, 2)) . '%</td>
                    </tr>';
            }

            return $html . '</table>';
        }

        $chartImage = $this->buildNaiveBayesMetricChartImage($perClass);

        return '
            <div class="metric-chart">
                <img class="metric-chart-image" src="' . esc($chartImage, 'attr') . '" alt="Grafik metrik per intent">
            </div>';
    }

    private function buildNaiveBayesAccuracyChartHtml(float $accuracy, string $format = 'pdf'): string
    {
        $accuracy = max(0, min(1, $accuracy));
        $accuracyPercent = number_format($accuracy * 100, 2);

        if ($format === 'excel') {
            return '
                <table>
                    <tr><td class="section" colspan="2">Diagram Akurasi Model</td></tr>
                    <tr>
                        <th>Metrik</th>
                        <th>Nilai</th>
                    </tr>
                    <tr>
                        <td>Accuracy</td>
                        <td><div class="excel-bar-track"><div class="excel-bar accuracy" style="width: ' . esc((string) round($accuracy * 100, 2)) . '%;"></div></div> ' . esc($accuracyPercent) . '%</td>
                    </tr>
                </table>';
        }

        $chartImage = $this->buildNaiveBayesAccuracyChartImage($accuracy);

        if ($chartImage === '') {
            return '';
        }

        return '
            <div class="metric-chart accuracy-chart">
                <img class="metric-chart-image" src="' . esc($chartImage, 'attr') . '" alt="Diagram akurasi model">
            </div>';
    }

    private function buildNaiveBayesAccuracyChartImage(float $accuracy): string
    {
        if (!function_exists('imagecreatetruecolor')) {
            return '';
        }

        $accuracy = max(0, min(1, $accuracy));
        $width = 1200;
        $height = 430;
        $plotLeft = 115;
        $plotTop = 62;
        $plotRight = 48;
        $plotBottom = 82;
        $plotWidth = $width - $plotLeft - $plotRight;
        $plotHeight = $height - $plotTop - $plotBottom;
        $image = imagecreatetruecolor($width, $height);
        imageantialias($image, true);

        $color = function (string $hex) use ($image): int {
            $hex = ltrim($hex, '#');

            return imagecolorallocate(
                $image,
                hexdec(substr($hex, 0, 2)),
                hexdec(substr($hex, 2, 2)),
                hexdec(substr($hex, 4, 2))
            );
        };

        $white = $color('#ffffff');
        $ink = $color('#0d2f4f');
        $muted = $color('#64748b');
        $grid = $color('#e3edf5');
        $axis = $color('#9fb3c8');
        $bar = $color('#2f75b5');

        imagefilledrectangle($image, 0, 0, $width, $height, $white);

        $percentText = number_format($accuracy * 100, 2) . '%';
        imagestring($image, 5, (int) (($width - strlen($percentText) * imagefontwidth(5)) / 2), 14, $percentText, $ink);
        imagestring($image, 3, (int) (($width - strlen('Model Accuracy') * imagefontwidth(3)) / 2), 36, 'Model Accuracy', $muted);
        imagestringup($image, 3, 28, (int) ($plotTop + ($plotHeight / 2) + 45), 'Accuracy (%)', $muted);

        for ($tick = 0; $tick <= 100; $tick += 20) {
            $y = (int) round($plotTop + $plotHeight - (($tick / 100) * $plotHeight));
            imageline($image, $plotLeft, $y, $width - $plotRight, $y, $tick === 0 ? $axis : $grid);
            imagestring($image, 2, $plotLeft - 42, $y - 7, (string) $tick, $muted);
        }

        imageline($image, $plotLeft, $plotTop, $plotLeft, $plotTop + $plotHeight, $axis);
        imageline($image, $plotLeft, $plotTop + $plotHeight, $width - $plotRight, $plotTop + $plotHeight, $axis);

        $barWidth = (int) ($plotWidth * 0.86);
        $x1 = (int) ($plotLeft + (($plotWidth - $barWidth) / 2));
        $x2 = $x1 + $barWidth;
        $barHeight = (int) round($accuracy * $plotHeight);
        $y1 = $plotTop + $plotHeight - $barHeight;
        imagefilledrectangle($image, $x1, $y1, $x2, $plotTop + $plotHeight, $bar);
        imagestring($image, 3, (int) (($width - strlen('Accuracy') * imagefontwidth(3)) / 2), $height - 48, 'Accuracy', $ink);

        ob_start();
        imagepng($image);
        $png = ob_get_clean();
        imagedestroy($image);

        return 'data:image/png;base64,' . base64_encode($png === false ? '' : $png);
    }

    private function buildNaiveBayesMetricChartImage(array $perClass): string
    {
        if (!function_exists('imagecreatetruecolor')) {
            return '';
        }

        $width = 1600;
        $height = 620;
        $plotLeft = 90;
        $plotTop = 70;
        $plotRight = 40;
        $plotBottom = 170;
        $plotWidth = $width - $plotLeft - $plotRight;
        $plotHeight = $height - $plotTop - $plotBottom;
        $image = imagecreatetruecolor($width, $height);
        imageantialias($image, true);

        $color = function (string $hex) use ($image): int {
            $hex = ltrim($hex, '#');

            return imagecolorallocate(
                $image,
                hexdec(substr($hex, 0, 2)),
                hexdec(substr($hex, 2, 2)),
                hexdec(substr($hex, 4, 2))
            );
        };

        $white = $color('#ffffff');
        $ink = $color('#0d2f4f');
        $muted = $color('#64748b');
        $grid = $color('#e3edf5');
        $axis = $color('#9fb3c8');
        $precisionColor = $color('#104f86');
        $recallColor = $color('#2f75b5');
        $f1Color = $color('#5f9ea0');

        imagefilledrectangle($image, 0, 0, $width, $height, $white);
        imagestring($image, 5, (int) (($width - strlen('Precision, Recall, dan F1-score per Intent') * imagefontwidth(5)) / 2), 24, 'Precision, Recall, dan F1-score per Intent', $ink);
        imagestringup($image, 3, 18, (int) ($plotTop + ($plotHeight / 2) + 45), 'Score (%)', $muted);

        for ($tick = 0; $tick <= 100; $tick += 10) {
            $y = (int) round($plotTop + $plotHeight - (($tick / 100) * $plotHeight));
            imageline($image, $plotLeft, $y, $width - $plotRight, $y, $tick === 0 ? $axis : $grid);
            imagestring($image, 2, $plotLeft - 42, $y - 7, (string) $tick, $muted);
        }

        $intentCount = max(1, count($perClass));
        $groupWidth = $plotWidth / $intentCount;
        $barWidth = (int) min(22, max(10, (($groupWidth - 28) / 3)));
        $seriesGap = 4;
        $index = 0;

        foreach ($perClass as $intent => $metrics) {
            $values = [
                ['value' => max(0, min(1, (float) ($metrics['precision'] ?? 0))), 'color' => $precisionColor],
                ['value' => max(0, min(1, (float) ($metrics['recall'] ?? 0))), 'color' => $recallColor],
                ['value' => max(0, min(1, (float) ($metrics['f1'] ?? 0))), 'color' => $f1Color],
            ];
            $groupStart = $plotLeft + ($index * $groupWidth);
            $groupCenter = $groupStart + ($groupWidth / 2);
            $barsWidth = (3 * $barWidth) + (2 * $seriesGap);
            $barStart = (int) round($groupCenter - ($barsWidth / 2));

            foreach ($values as $seriesIndex => $item) {
                $barHeight = (int) round($item['value'] * $plotHeight);
                $x1 = $barStart + ($seriesIndex * ($barWidth + $seriesGap));
                $x2 = $x1 + $barWidth;
                $y1 = $plotTop + $plotHeight - $barHeight;
                imagefilledrectangle($image, $x1, $y1, $x2, $plotTop + $plotHeight, $item['color']);
            }

            $label = (string) $intent;
            imagestringup($image, 2, (int) round($groupCenter - 4), $height - 55, $label, $ink);
            $index++;
        }

        $legendY = $height - 28;
        $legendX = $width - 390;
        imagefilledrectangle($image, $legendX, $legendY - 3, $legendX + 14, $legendY + 11, $precisionColor);
        imagestring($image, 3, $legendX + 22, $legendY - 4, 'Precision', $ink);
        imagefilledrectangle($image, $legendX + 130, $legendY - 3, $legendX + 144, $legendY + 11, $recallColor);
        imagestring($image, 3, $legendX + 152, $legendY - 4, 'Recall', $ink);
        imagefilledrectangle($image, $legendX + 235, $legendY - 3, $legendX + 249, $legendY + 11, $f1Color);
        imagestring($image, 3, $legendX + 257, $legendY - 4, 'F1-score', $ink);

        ob_start();
        imagepng($image);
        $png = ob_get_clean();
        imagedestroy($image);

        return 'data:image/png;base64,' . base64_encode($png === false ? '' : $png);
    }

    private function buildNaiveBayesEvaluationExcel(array $evaluation): string
    {
        $summary = $evaluation['summary'] ?? [];
        $perClass = $evaluation['per_class'] ?? [];
        $confusionMatrix = $evaluation['confusion_matrix'] ?? [];
        $logoDataUri = $this->getDashboardLogoDataUri();
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
                    .excel-bar-track { background: #eef2f7; display: inline-block; height: 12px; margin-right: 8px; vertical-align: middle; width: 120px; }
                    .excel-bar { display: block; height: 12px; }
                    .precision { background: #104f86; }
                    .recall { background: #2f75b5; }
                    .f1 { background: #5f9ea0; }
                    .accuracy { background: #2f75b5; }
                    .report-logo { height: 54px; width: 54px; }
                </style>
            </head>
            <body>
                <table>
                    <tr>
                        <td class="title" colspan="2">' . ($logoDataUri !== '' ? '<img class="report-logo" src="' . esc($logoDataUri, 'attr') . '" alt="Logo SMPS Plus Fajar Sentosa">' : '') . '</td>
                        <td class="title" colspan="2">Hasil Pengujian Naive Bayes<br>SMPS Plus Fajar Sentosa</td>
                    </tr>
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

                ' . $this->buildNaiveBayesAccuracyChartHtml((float) ($summary['accuracy'] ?? 0), 'excel') . '

                ' . $this->buildNaiveBayesMetricChartHtml($perClass, 'excel') . '

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
        $logoDataUri = $this->getDashboardLogoDataUri();
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
                    .header { background: #104f86; border-radius: 14px; color: #fff; padding: 18px 22px; }
                    .header-table { border-collapse: collapse; width: 100%; }
                    .header-table td { border: 0; padding: 0; vertical-align: middle; }
                    .header-logo-cell { width: 70px; }
                    .header-logo { background: #fff; border-radius: 999px; height: 56px; padding: 6px; width: 56px; }
                    .eyebrow { font-size: 10px; letter-spacing: 1px; margin: 0 0 5px; text-transform: uppercase; }
                    h1 { font-size: 24px; line-height: 1.1; margin: 0 0 8px; }
                    .subtitle { color: #dcecff; margin: 0; }
                    .meta { margin-top: 14px; }
                    .meta span { background: rgba(255,255,255,.14); border-radius: 999px; display: inline-block; margin: 0 6px 6px 0; padding: 5px 10px; }
                    h2 { border-bottom: 2px solid #dbe7e5; color: #104f86; font-size: 15px; margin: 24px 0 12px; padding-bottom: 6px; }
                    .metric-grid { width: 100%; border-collapse: separate; border-spacing: 8px; margin: 16px -8px 6px; }
                    .metric { background: #f4f8fb; border: 1px solid #cfe0ef; border-radius: 10px; padding: 12px; }
                    .metric span { color: #5b6b7a; display: block; font-size: 9px; margin-bottom: 5px; text-transform: uppercase; }
                    .metric strong { color: #0d2f4f; display: block; font-size: 18px; }
                    table.data { border-collapse: collapse; margin-top: 8px; table-layout: fixed; width: 100%; }
                    table.data th { background: #edf6fb; color: #0d2f4f; font-size: 10px; text-align: left; }
                    table.data th, table.data td { border: 1px solid #d7e2e0; padding: 7px 8px; vertical-align: top; }
                    table.data tr:nth-child(even) td { background: #fafdfd; }
                    table.matrix { font-size: 8px; page-break-inside: avoid; }
                    table.matrix th, table.matrix td { padding: 5px 4px; text-align: center; word-break: break-word; }
                    table.matrix th:first-child, table.matrix td:first-child { text-align: left; width: 130px; }
                    .intent { background: #e8f0f6; border-radius: 999px; color: #104f86; display: inline-block; font-size: 10px; padding: 3px 7px; }
                    table.matrix .intent { border-radius: 6px; font-size: 8px; padding: 2px 4px; }
                    .matrix-note { color: #64748b; font-size: 9px; margin: -4px 0 8px; }
                    .metric-chart { border: 1px solid #d7e2e0; border-radius: 12px; margin: 8px 0 16px; padding: 14px 14px 10px; page-break-inside: avoid; }
                    .metric-chart-image { display: block; height: auto; width: 100%; }
                    .accuracy-chart { padding: 10px; }
                    .chart-title { color: #0d2f4f; font-size: 11px; font-weight: bold; margin-bottom: 10px; text-align: center; }
                    .chart-table { border-collapse: collapse; table-layout: fixed; width: 100%; }
                    .chart-table td { border: 0; padding: 0 3px; vertical-align: bottom; }
                    .chart-group { height: 198px; text-align: center; }
                    .chart-bars { border-bottom: 1px solid #cbd5e1; height: 154px; margin: 0 auto 5px; white-space: nowrap; }
                    .chart-bar { display: inline-block; margin: 0 1px; vertical-align: bottom; width: 7px; }
                    .chart-bar.precision, .chart-legend .precision { background: #104f86; }
                    .chart-bar.recall, .chart-legend .recall { background: #2f75b5; }
                    .chart-bar.f1, .chart-legend .f1 { background: #5f9ea0; }
                    .chart-label { color: #334155; font-size: 7px; line-height: 1.15; word-break: break-word; }
                    .chart-legend { color: #334155; font-size: 9px; margin-top: 8px; text-align: right; }
                    .chart-legend span { display: inline-block; margin-left: 12px; }
                    .chart-legend i { display: inline-block; height: 8px; margin-right: 4px; vertical-align: middle; width: 8px; }
                    .footer { border-top: 1px solid #dbe7e5; color: #718096; font-size: 9px; margin-top: 24px; padding-top: 10px; text-align: right; }
                </style>
            </head>
            <body>
                <div class="header">
                    <table class="header-table">
                        <tr>
                            <td class="header-logo-cell">' . ($logoDataUri !== '' ? '<img class="header-logo" src="' . esc($logoDataUri, 'attr') . '" alt="Logo SMPS Plus Fajar Sentosa">' : '') . '</td>
                            <td>
                                <p class="eyebrow">Laporan Evaluasi Dataset Chatbot</p>
                                <h1>Hasil Pengujian Naive Bayes</h1>
                                <p class="subtitle">SMPS Plus Fajar Sentosa - Metode hold out dengan 80% data latih dan 20% data uji.</p>
                                <div class="meta">
                                    <span>Tanggal uji: ' . esc($evaluation['evaluated_at'] ?? '-') . '</span>
                                    <span>Intent: ' . esc((string) ($summary['intent_count'] ?? 0)) . '</span>
                                    <span>Data uji: ' . esc((string) ($summary['test_samples'] ?? 0)) . '</span>
                                </div>
                            </td>
                        </tr>
                    </table>
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

                <h2>Diagram Akurasi Model</h2>
                ' . $this->buildNaiveBayesAccuracyChartHtml((float) ($summary['accuracy'] ?? 0)) . '

                <h2>Metrik Per Intent</h2>
                ' . $this->buildNaiveBayesMetricChartHtml($perClass) . '
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
