<?php

namespace App\Models;

use CodeIgniter\Model;

class LandingPageModel extends Model
{
    private array $defaultSettings = [
        'site_name' => 'SMPS Plus Fajar Sentosa',
        'brand_line_1' => 'SMPS Plus',
        'brand_line_2' => 'Fajar Sentosa',
        'logo_url' => 'assets/images/logo-yapas.png',
        'hero_image_url' => 'https://images.unsplash.com/photo-1580582932707-520aed937b7b?auto=format&fit=crop&w=1600&q=85',
        'hero_title' => 'Yuk, Belajar Bersama Kita di SMP Plus Fajar Sentosa!',
        'hero_subtitle' => 'Lingkungan belajar yang hangat, disiplin, dan aktif membimbing siswa untuk tumbuh percaya diri, berprestasi, serta berakhlak baik.',
        'about_kicker' => 'Tentang Sekolah',
        'about_title' => 'Sekolah yang menyiapkan siswa untuk masa depan.',
        'about_text' => 'SMPS Plus Fajar Sentosa menghadirkan pembelajaran akademik, pembinaan karakter, dan kegiatan pengembangan minat dalam suasana sekolah yang aman serta terarah.',
        'stat_1_number' => '25+',
        'stat_1_label' => 'Guru & pembimbing',
        'stat_2_number' => '15+',
        'stat_2_label' => 'Kegiatan siswa',
        'stat_3_number' => 'A',
        'stat_3_label' => 'Akreditasi sekolah',
        'program_title' => 'Belajar lebih hidup, terukur, dan menyenangkan.',
        'staff_title' => 'Tenaga pendidik yang mendampingi siswa bertumbuh.',
        'staff_subtitle' => 'Kepala sekolah, guru, dan staf sekolah bekerja bersama menghadirkan lingkungan belajar yang aman, terarah, dan hangat.',
        'spmb_title' => 'Pendaftaran siswa baru sudah bisa disiapkan.',
        'spmb_text' => 'Tanyakan jadwal, syarat, biaya, dan alur pendaftaran melalui chatbot di pojok kanan bawah.',
        'contact_title' => 'Kami siap membantu informasi sekolah.',
        'contact_address' => 'Jl. Contoh No. 10, Kediri',
        'contact_latitude' => '',
        'contact_longitude' => '',
        'contact_map_embed_url' => 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d991.3014015406402!2d106.98522474931492!3d-6.367432305658795!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e6993f2573a2aa7%3A0xb16f2509831af51b!2sSMPS%20Plus%20Fajar%20Sentosa.!5e0!3m2!1sid!2sid!4v1781262481190!5m2!1sid!2sid',
        'contact_phone' => '0812-3456-7890',
        'contact_email' => 'info@smpsplusfajarsentosa.sch.id',
        'facebook_url' => '#',
        'instagram_url' => '#',
        'tiktok_url' => '#',
        'twitter_url' => '#',
        'youtube_url' => '#',
    ];

    private array $defaultPrograms = [
        [
            'icon' => 'fa-solid fa-chalkboard-user',
            'title' => 'Guru Profesional',
            'description' => 'Pendampingan belajar dengan pendekatan personal dan evaluasi perkembangan siswa.',
        ],
        [
            'icon' => 'fa-solid fa-computer',
            'title' => 'Literasi Digital',
            'description' => 'Pengenalan teknologi, laboratorium komputer, dan kebiasaan belajar berbasis proyek.',
        ],
        [
            'icon' => 'fa-solid fa-trophy',
            'title' => 'Prestasi & Karakter',
            'description' => 'Ekstrakurikuler, pembiasaan ibadah, disiplin, dan ruang tampil untuk potensi siswa.',
        ],
    ];

    private array $defaultNews = [
        [
            'title' => 'Pembukaan SPMB Tahun Ajaran Baru',
            'excerpt' => 'Informasi pendaftaran, jadwal seleksi, dan persyaratan calon peserta didik baru.',
            'image_url' => 'https://images.unsplash.com/photo-1509062522246-3755977927d7?auto=format&fit=crop&w=900&q=85',
        ],
        [
            'title' => 'Kegiatan Projek Profil Pelajar',
            'excerpt' => 'Siswa belajar berkolaborasi melalui karya, presentasi, dan kegiatan sosial.',
            'image_url' => 'https://images.unsplash.com/photo-1522202176988-66273c2fd55f?auto=format&fit=crop&w=900&q=85',
        ],
        [
            'title' => 'Prestasi Akademik dan Nonakademik',
            'excerpt' => 'Apresiasi untuk siswa yang aktif berkembang di kelas, lomba, dan organisasi.',
            'image_url' => 'https://images.unsplash.com/photo-1544717305-2782549b5136?auto=format&fit=crop&w=900&q=85',
        ],
    ];

    private array $defaultNewsImages = [
        'https://images.unsplash.com/photo-1509062522246-3755977927d7?auto=format&fit=crop&w=900&q=85',
        'https://images.unsplash.com/photo-1522202176988-66273c2fd55f?auto=format&fit=crop&w=900&q=85',
        'https://images.unsplash.com/photo-1544717305-2782549b5136?auto=format&fit=crop&w=900&q=85',
        'https://images.unsplash.com/photo-1497633762265-9d179a990aa6?auto=format&fit=crop&w=900&q=85',
    ];

    private array $defaultStaff = [
        [
            'name' => 'Nama Kepala Sekolah',
            'position' => 'Kepala Sekolah',
            'bio' => 'Memimpin pengembangan sekolah dan memastikan pembelajaran berjalan terarah.',
            'photo_url' => 'https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?auto=format&fit=crop&w=700&q=85',
        ],
        [
            'name' => 'Nama Guru Kelas',
            'position' => 'Guru Matematika',
            'bio' => 'Mendampingi siswa memahami konsep belajar dengan cara yang runtut dan menyenangkan.',
            'photo_url' => 'https://images.unsplash.com/photo-1544717302-de2939b7ef71?auto=format&fit=crop&w=700&q=85',
        ],
        [
            'name' => 'Nama Guru Pembina',
            'position' => 'Guru Bahasa Indonesia',
            'bio' => 'Mendorong kemampuan literasi, komunikasi, dan percaya diri siswa.',
            'photo_url' => 'https://images.unsplash.com/photo-1580894732444-8ecded7900cd?auto=format&fit=crop&w=700&q=85',
        ],
        [
            'name' => 'Nama Staf Sekolah',
            'position' => 'Tata Usaha',
            'bio' => 'Membantu layanan administrasi sekolah dan kebutuhan informasi orang tua.',
            'photo_url' => 'https://images.unsplash.com/photo-1551836022-d5d88e9218df?auto=format&fit=crop&w=700&q=85',
        ],
    ];

    private array $defaultStaffPhotos = [
        'https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?auto=format&fit=crop&w=700&q=85',
        'https://images.unsplash.com/photo-1544717302-de2939b7ef71?auto=format&fit=crop&w=700&q=85',
        'https://images.unsplash.com/photo-1580894732444-8ecded7900cd?auto=format&fit=crop&w=700&q=85',
        'https://images.unsplash.com/photo-1551836022-d5d88e9218df?auto=format&fit=crop&w=700&q=85',
    ];

    public function ensureSchema(): void
    {
        $db = db_connect();

        $db->query("
            CREATE TABLE IF NOT EXISTS landing_page_settings (
                setting_key VARCHAR(80) PRIMARY KEY,
                setting_value TEXT NULL,
                updated_at DATETIME NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
        ");

        $db->query("
            CREATE TABLE IF NOT EXISTS landing_page_news (
                id INT AUTO_INCREMENT PRIMARY KEY,
                title VARCHAR(180) NOT NULL,
                excerpt TEXT NOT NULL,
                image_url VARCHAR(255) NULL,
                status ENUM('published','draft') NOT NULL DEFAULT 'published',
                sort_order INT NOT NULL DEFAULT 0,
                created_at DATETIME NULL,
                updated_at DATETIME NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
        ");

        $db->query("
            CREATE TABLE IF NOT EXISTS landing_page_programs (
                id INT AUTO_INCREMENT PRIMARY KEY,
                icon VARCHAR(100) NOT NULL,
                title VARCHAR(150) NOT NULL,
                description TEXT NOT NULL,
                status ENUM('active','inactive') NOT NULL DEFAULT 'active',
                sort_order INT NOT NULL DEFAULT 0,
                created_at DATETIME NULL,
                updated_at DATETIME NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
        ");

        $db->query("
            CREATE TABLE IF NOT EXISTS landing_page_staff (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(150) NOT NULL,
                position VARCHAR(150) NOT NULL,
                bio TEXT NULL,
                photo_url VARCHAR(255) NULL,
                status ENUM('active','inactive') NOT NULL DEFAULT 'active',
                sort_order INT NOT NULL DEFAULT 0,
                created_at DATETIME NULL,
                updated_at DATETIME NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
        ");

        $this->seedDefaults();
    }

    public function getSettings(): array
    {
        $this->ensureSchema();
        $settings = $this->defaultSettings;
        $rows = db_connect()->table('landing_page_settings')->get()->getResultArray();

        foreach ($rows as $row) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }

        return $settings;
    }

    public function updateSettings(array $payload): void
    {
        $this->ensureSchema();
        $db = db_connect();
        $allowed = array_keys($this->defaultSettings);
        $now = date('Y-m-d H:i:s');

        foreach ($allowed as $key) {
            if (!array_key_exists($key, $payload)) {
                continue;
            }

            $value = trim((string) $payload[$key]);
            $exists = $db->table('landing_page_settings')->where('setting_key', $key)->countAllResults() > 0;

            if ($exists) {
                $db->table('landing_page_settings')->where('setting_key', $key)->update([
                    'setting_value' => $value,
                    'updated_at' => $now,
                ]);
            } else {
                $db->table('landing_page_settings')->insert([
                    'setting_key' => $key,
                    'setting_value' => $value,
                    'updated_at' => $now,
                ]);
            }
        }
    }

    public function getPrograms(bool $activeOnly = false): array
    {
        $this->ensureSchema();
        $builder = db_connect()->table('landing_page_programs')->orderBy('sort_order', 'ASC')->orderBy('id', 'ASC');

        if ($activeOnly) {
            $builder->where('status', 'active');
        }

        return $builder->get()->getResultArray();
    }

    public function updateProgram(int $id, array $payload): void
    {
        $this->ensureSchema();
        $data = $this->cleanProgramPayload($payload);
        $data['updated_at'] = date('Y-m-d H:i:s');
        db_connect()->table('landing_page_programs')->where('id', $id)->update($data);
    }

    public function getStaff(bool $activeOnly = false): array
    {
        $this->ensureSchema();
        $builder = db_connect()->table('landing_page_staff')->orderBy('sort_order', 'ASC')->orderBy('id', 'ASC');

        if ($activeOnly) {
            $builder->where('status', 'active');
        }

        $rows = $builder->get()->getResultArray();

        foreach ($rows as $index => &$row) {
            if (empty($row['photo_url'])) {
                $row['photo_url'] = $this->defaultStaffPhotos[$index % count($this->defaultStaffPhotos)];
            }
        }

        return $rows;
    }

    public function createStaff(array $payload, ?string $photoUrl = null): void
    {
        $this->ensureSchema();
        $data = $this->cleanStaffPayload($payload);
        $data['photo_url'] = $photoUrl ?: $this->defaultStaffPhotos[((int) $data['sort_order']) % count($this->defaultStaffPhotos)];
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');
        db_connect()->table('landing_page_staff')->insert($data);
    }

    public function updateStaff(int $id, array $payload, ?string $photoUrl = null): void
    {
        $this->ensureSchema();
        $data = $this->cleanStaffPayload($payload);
        $data['updated_at'] = date('Y-m-d H:i:s');

        if ($photoUrl !== null) {
            $data['photo_url'] = $photoUrl;
        }

        db_connect()->table('landing_page_staff')->where('id', $id)->update($data);
    }

    public function findStaff(int $id): ?array
    {
        $this->ensureSchema();
        return db_connect()->table('landing_page_staff')->where('id', $id)->get()->getRowArray() ?: null;
    }

    public function deleteStaff(int $id): void
    {
        $this->ensureSchema();
        db_connect()->table('landing_page_staff')->where('id', $id)->delete();
    }

    public function getNews(bool $publishedOnly = false): array
    {
        $this->ensureSchema();
        $builder = db_connect()->table('landing_page_news')->orderBy('sort_order', 'ASC')->orderBy('id', 'DESC');

        if ($publishedOnly) {
            $builder->where('status', 'published');
        }

        $rows = $builder->get()->getResultArray();

        foreach ($rows as $index => &$row) {
            if (empty($row['image_url'])) {
                $row['image_url'] = $this->defaultNewsImages[$index % count($this->defaultNewsImages)];
            }
        }

        return $rows;
    }

    public function createNews(array $payload, ?string $imageUrl = null): void
    {
        $this->ensureSchema();
        $data = $this->cleanNewsPayload($payload);
        $data['image_url'] = $imageUrl ?: $this->defaultNewsImages[((int) $data['sort_order']) % count($this->defaultNewsImages)];
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');
        db_connect()->table('landing_page_news')->insert($data);
    }

    public function updateNews(int $id, array $payload, ?string $imageUrl = null): void
    {
        $this->ensureSchema();
        $data = $this->cleanNewsPayload($payload);
        $data['updated_at'] = date('Y-m-d H:i:s');

        if ($imageUrl !== null) {
            $data['image_url'] = $imageUrl;
        }

        db_connect()->table('landing_page_news')->where('id', $id)->update($data);
    }

    public function findNews(int $id): ?array
    {
        $this->ensureSchema();
        return db_connect()->table('landing_page_news')->where('id', $id)->get()->getRowArray() ?: null;
    }

    public function deleteNews(int $id): void
    {
        $this->ensureSchema();
        db_connect()->table('landing_page_news')->where('id', $id)->delete();
    }

    private function seedDefaults(): void
    {
        $db = db_connect();
        $now = date('Y-m-d H:i:s');

        foreach ($this->defaultSettings as $key => $value) {
            $exists = $db->table('landing_page_settings')->where('setting_key', $key)->countAllResults() > 0;

            if (!$exists) {
                $db->table('landing_page_settings')->insert([
                    'setting_key' => $key,
                    'setting_value' => $value,
                    'updated_at' => $now,
                ]);
            }
        }

        if ($db->table('landing_page_programs')->countAllResults() === 0) {
            foreach ($this->defaultPrograms as $index => $program) {
                $db->table('landing_page_programs')->insert([
                    'icon' => $program['icon'],
                    'title' => $program['title'],
                    'description' => $program['description'],
                    'status' => 'active',
                    'sort_order' => $index + 1,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

        if ($db->table('landing_page_news')->countAllResults() === 0) {
            foreach ($this->defaultNews as $index => $news) {
                $db->table('landing_page_news')->insert([
                    'title' => $news['title'],
                    'excerpt' => $news['excerpt'],
                    'image_url' => $news['image_url'],
                    'status' => 'published',
                    'sort_order' => $index + 1,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

        if ($db->table('landing_page_staff')->countAllResults() === 0) {
            foreach ($this->defaultStaff as $index => $staff) {
                $db->table('landing_page_staff')->insert([
                    'name' => $staff['name'],
                    'position' => $staff['position'],
                    'bio' => $staff['bio'],
                    'photo_url' => $staff['photo_url'],
                    'status' => 'active',
                    'sort_order' => $index + 1,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }

    private function cleanProgramPayload(array $payload): array
    {
        $title = trim((string) ($payload['title'] ?? ''));
        $description = trim((string) ($payload['description'] ?? ''));

        if ($title === '' || $description === '') {
            throw new \InvalidArgumentException('Judul dan deskripsi program wajib diisi.');
        }

        return [
            'icon' => trim((string) ($payload['icon'] ?? 'fa-solid fa-star')),
            'title' => $title,
            'description' => $description,
            'status' => in_array(($payload['status'] ?? 'active'), ['active', 'inactive'], true) ? $payload['status'] : 'active',
            'sort_order' => (int) ($payload['sort_order'] ?? 0),
        ];
    }

    private function cleanNewsPayload(array $payload): array
    {
        $title = trim((string) ($payload['title'] ?? ''));
        $excerpt = trim((string) ($payload['excerpt'] ?? ''));

        if ($title === '' || $excerpt === '') {
            throw new \InvalidArgumentException('Judul dan ringkasan berita wajib diisi.');
        }

        return [
            'title' => $title,
            'excerpt' => $excerpt,
            'status' => in_array(($payload['status'] ?? 'published'), ['published', 'draft'], true) ? $payload['status'] : 'published',
            'sort_order' => (int) ($payload['sort_order'] ?? 0),
        ];
    }

    private function cleanStaffPayload(array $payload): array
    {
        $name = trim((string) ($payload['name'] ?? ''));
        $position = trim((string) ($payload['position'] ?? ''));

        if ($name === '' || $position === '') {
            throw new \InvalidArgumentException('Nama dan jabatan tenaga pendidik wajib diisi.');
        }

        return [
            'name' => $name,
            'position' => $position,
            'bio' => trim((string) ($payload['bio'] ?? '')),
            'status' => in_array(($payload['status'] ?? 'active'), ['active', 'inactive'], true) ? $payload['status'] : 'active',
            'sort_order' => (int) ($payload['sort_order'] ?? 0),
        ];
    }
}
