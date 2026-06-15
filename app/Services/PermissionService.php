<?php

namespace App\Services;

class PermissionService
{
    private const SUPER_ADMIN = 'super_admin';
    private const ADMIN_SPMB = 'admin_spmb';

    private array $menus = [
        [
            'key' => 'dashboard',
            'label' => 'Dashboard',
            'category' => null,
            'icon' => 'mdi mdi-grid-large',
            'url' => 'dashboard',
            'active' => ['dashboard'],
            'sort_order' => 10,
        ],
        [
            'key' => 'support_chat',
            'label' => 'Answer Chat',
            'category' => 'Layanan Chat',
            'icon' => 'mdi mdi-message-reply-text-outline',
            'url' => 'dashboard/support-chat',
            'active' => ['dashboard/support-chat'],
            'sort_order' => 20,
        ],
        [
            'key' => 'history_chat',
            'label' => 'Riwayat Chat',
            'category' => 'Layanan Chat',
            'icon' => 'mdi mdi-history',
            'url' => 'dashboard/history-chat',
            'active' => ['dashboard/history-chat'],
            'sort_order' => 30,
        ],
        [
            'key' => 'scan_whatsapp',
            'label' => 'Scan WhatsApp',
            'category' => 'Layanan Chat',
            'icon' => 'mdi mdi-qrcode-scan',
            'url' => 'dashboard/scan-whatsapp',
            'active' => ['dashboard/scan-whatsapp'],
            'sort_order' => 40,
        ],
        [
            'key' => 'intents',
            'label' => 'Intents',
            'category' => 'Dataset Chatbot',
            'icon' => 'mdi mdi-format-list-bulleted-type',
            'url' => 'dashboard/intents',
            'active' => ['dashboard/intents', 'dashboard/knowledge-base'],
            'sort_order' => 50,
        ],
        [
            'key' => 'training_phrases',
            'label' => 'Training Phrases',
            'category' => 'Dataset Chatbot',
            'icon' => 'mdi mdi-message-text-outline',
            'url' => 'dashboard/training-phrases',
            'active' => ['dashboard/training-phrases'],
            'sort_order' => 60,
        ],
        [
            'key' => 'nlp_rules',
            'label' => 'NLP Rules',
            'category' => 'Dataset Chatbot',
            'icon' => 'mdi mdi-tune',
            'url' => 'dashboard/nlp-rules',
            'active' => ['dashboard/nlp-rules'],
            'sort_order' => 70,
        ],
        [
            'key' => 'landing_page',
            'label' => 'Landing Page',
            'category' => 'Pengaturan',
            'icon' => 'mdi mdi-monitor-dashboard',
            'url' => 'dashboard/landing-page',
            'active' => ['dashboard/landing-page'],
            'sort_order' => 80,
        ],
        [
            'key' => 'role_permissions',
            'label' => 'Hak Akses',
            'category' => 'Pengaturan',
            'icon' => 'mdi mdi-shield-account',
            'url' => 'dashboard/hak-akses',
            'active' => ['dashboard/hak-akses'],
            'sort_order' => 90,
        ],
        [
            'key' => 'profile',
            'label' => 'Setting Profil',
            'category' => 'Pengaturan',
            'icon' => 'mdi mdi-account-cog',
            'url' => 'dashboard/profile',
            'active' => ['dashboard/profile'],
            'sort_order' => 100,
            'always_visible' => true,
        ],
    ];

    private array $routeMenus = [
        'dashboard' => 'dashboard',
        'dashboard/support-chat' => 'support_chat',
        'dashboard/history-chat' => 'history_chat',
        'dashboard/scan-whatsapp' => 'scan_whatsapp',
        'dashboard/intents' => 'intents',
        'dashboard/knowledge-base' => 'intents',
        'dashboard/training-phrases' => 'training_phrases',
        'dashboard/nlp-rules' => 'nlp_rules',
        'dashboard/landing-page' => 'landing_page',
        'dashboard/hak-akses' => 'role_permissions',
        'dashboard/profile' => 'profile',
    ];

    public function ensureSchema(): void
    {
        $db = db_connect();

        $this->ensureAdminUsersSchema();

        $db->query("
            CREATE TABLE IF NOT EXISTS admin_roles (
                slug VARCHAR(50) PRIMARY KEY,
                name VARCHAR(120) NOT NULL,
                description VARCHAR(255) NULL,
                is_system TINYINT(1) NOT NULL DEFAULT 0,
                created_at DATETIME NULL,
                updated_at DATETIME NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
        ");

        $db->query("
            CREATE TABLE IF NOT EXISTS admin_menus (
                menu_key VARCHAR(80) PRIMARY KEY,
                label VARCHAR(120) NOT NULL,
                category VARCHAR(120) NULL,
                icon VARCHAR(120) NULL,
                url VARCHAR(160) NOT NULL,
                sort_order INT NOT NULL DEFAULT 0,
                is_active TINYINT(1) NOT NULL DEFAULT 1,
                created_at DATETIME NULL,
                updated_at DATETIME NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
        ");

        $db->query("
            CREATE TABLE IF NOT EXISTS admin_role_menu_permissions (
                id INT AUTO_INCREMENT PRIMARY KEY,
                role_slug VARCHAR(50) NOT NULL,
                menu_key VARCHAR(80) NOT NULL,
                can_view TINYINT(1) NOT NULL DEFAULT 0,
                can_create TINYINT(1) NOT NULL DEFAULT 0,
                can_update TINYINT(1) NOT NULL DEFAULT 0,
                can_delete TINYINT(1) NOT NULL DEFAULT 0,
                created_at DATETIME NULL,
                updated_at DATETIME NULL,
                UNIQUE KEY uniq_admin_role_menu (role_slug, menu_key)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
        ");

        if ($db->tableExists('admin_users') && $db->fieldExists('role', 'admin_users')) {
            $db->table('admin_users')
                ->where('role', 'admin')
                ->update(['role' => self::SUPER_ADMIN, 'updated_at' => date('Y-m-d H:i:s')]);
        }

        $this->seedRoles();
        $this->seedMenus();
        $this->seedDefaultPermissions();
    }

    public function ensureAdminUsersSchema(): void
    {
        $db = db_connect();

        $db->query("
            CREATE TABLE IF NOT EXISTS admin_users (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(120) NOT NULL,
                email VARCHAR(150) NOT NULL UNIQUE,
                avatar_url VARCHAR(255) NULL,
                password_hash VARCHAR(255) NOT NULL,
                role VARCHAR(50) NOT NULL DEFAULT 'super_admin',
                created_at DATETIME NULL,
                updated_at DATETIME NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
        ");

        if (!$db->fieldExists('avatar_url', 'admin_users')) {
            $db->query('ALTER TABLE admin_users ADD avatar_url VARCHAR(255) NULL AFTER email');
        }
    }

    public function getRoles(): array
    {
        $this->ensureSchema();

        return db_connect()->table('admin_roles')
            ->orderBy('slug', 'DESC')
            ->get()
            ->getResultArray();
    }

    public function getRole(string $role): ?array
    {
        $this->ensureSchema();

        $row = db_connect()->table('admin_roles')
            ->where('slug', $this->normalizeRole($role))
            ->get()
            ->getRowArray();

        return $row ?: null;
    }

    public function getAssignableRoles(): array
    {
        $roles = $this->getRoles();

        usort($roles, static fn (array $a, array $b): int => strcmp($a['name'], $b['name']));

        return $roles;
    }

    public function getAdminUsers(): array
    {
        $this->ensureSchema();

        return db_connect()->table('admin_users u')
            ->select('u.id, u.name, u.email, u.avatar_url, u.role, u.created_at, u.updated_at, r.name AS role_name')
            ->join('admin_roles r', 'r.slug = u.role', 'left')
            ->orderBy('u.name', 'ASC')
            ->get()
            ->getResultArray();
    }

    public function createRole(string $name, string $description = ''): string
    {
        $this->ensureSchema();

        $name = trim($name);
        $description = trim($description);

        if ($name === '') {
            throw new \InvalidArgumentException('Nama role wajib diisi.');
        }

        $slug = $this->uniqueRoleSlug($name);
        $now = date('Y-m-d H:i:s');

        db_connect()->table('admin_roles')->insert([
            'slug' => $slug,
            'name' => $name,
            'description' => $description !== '' ? $description : null,
            'is_system' => 0,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $this->createEmptyPermissions($slug);

        return $slug;
    }

    public function createAdminUser(string $name, string $email, string $password, string $role): void
    {
        $this->ensureSchema();

        $name = trim($name);
        $email = trim($email);
        $role = $this->normalizeRole($role);

        if ($name === '' || $email === '' || $password === '') {
            throw new \InvalidArgumentException('Nama, email, dan password wajib diisi.');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('Format email tidak valid.');
        }

        if (strlen($password) < 6) {
            throw new \InvalidArgumentException('Password minimal 6 karakter.');
        }

        if (!$this->getRole($role)) {
            throw new \InvalidArgumentException('Role tidak ditemukan.');
        }

        $db = db_connect();
        $duplicate = $db->table('admin_users')->where('email', $email)->countAllResults();

        if ($duplicate > 0) {
            throw new \InvalidArgumentException('Email sudah digunakan admin lain.');
        }

        $now = date('Y-m-d H:i:s');

        $db->table('admin_users')->insert([
            'name' => $name,
            'email' => $email,
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            'role' => $role,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    public function updateAdminUser(int $id, string $name, string $email, string $role, string $password = ''): void
    {
        $this->ensureSchema();

        $name = trim($name);
        $email = trim($email);
        $role = $this->normalizeRole($role);

        if ($name === '' || $email === '') {
            throw new \InvalidArgumentException('Nama dan email wajib diisi.');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('Format email tidak valid.');
        }

        if ($password !== '' && strlen($password) < 6) {
            throw new \InvalidArgumentException('Password minimal 6 karakter.');
        }

        if (!$this->getRole($role)) {
            throw new \InvalidArgumentException('Role tidak ditemukan.');
        }

        $db = db_connect();
        $user = $db->table('admin_users')->where('id', $id)->get()->getRowArray();

        if (!$user) {
            throw new \InvalidArgumentException('User admin tidak ditemukan.');
        }

        if ((int) session('admin_id') === $id && $role !== self::SUPER_ADMIN) {
            throw new \InvalidArgumentException('Role akun yang sedang login tidak bisa diturunkan dari Super Admin.');
        }

        $duplicate = $db->table('admin_users')
            ->where('email', $email)
            ->where('id !=', $id)
            ->countAllResults();

        if ($duplicate > 0) {
            throw new \InvalidArgumentException('Email sudah digunakan admin lain.');
        }

        $payload = [
            'name' => $name,
            'email' => $email,
            'role' => $role,
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        if ($password !== '') {
            $payload['password_hash'] = password_hash($password, PASSWORD_DEFAULT);
        }

        $db->table('admin_users')->where('id', $id)->update($payload);

        if ((int) session('admin_id') === $id) {
            session()->set([
                'admin_name' => $name,
                'admin_email' => $email,
                'admin_role' => $role,
            ]);
        }
    }

    public function getMenus(): array
    {
        $this->ensureSchema();

        return db_connect()->table('admin_menus')
            ->where('is_active', 1)
            ->orderBy('sort_order', 'ASC')
            ->get()
            ->getResultArray();
    }

    public function getPermissionsByRole(string $role): array
    {
        $this->ensureSchema();

        $rows = db_connect()->table('admin_role_menu_permissions')
            ->where('role_slug', $this->normalizeRole($role))
            ->get()
            ->getResultArray();

        return array_column($rows, null, 'menu_key');
    }

    public function updateRolePermissions(string $role, array $postedPermissions): void
    {
        $this->ensureSchema();
        $role = $this->normalizeRole($role);

        if ($role === self::SUPER_ADMIN) {
            return;
        }

        $db = db_connect();
        $now = date('Y-m-d H:i:s');

        foreach ($this->getMenus() as $menu) {
            $key = $menu['menu_key'];
            $values = [
                'can_view' => isset($postedPermissions[$key]['can_view']) ? 1 : 0,
                'can_create' => isset($postedPermissions[$key]['can_create']) ? 1 : 0,
                'can_update' => isset($postedPermissions[$key]['can_update']) ? 1 : 0,
                'can_delete' => isset($postedPermissions[$key]['can_delete']) ? 1 : 0,
                'updated_at' => $now,
            ];

            if ($key === 'profile') {
                $values = array_merge($values, [
                    'can_view' => 1,
                    'can_create' => 0,
                    'can_update' => 1,
                    'can_delete' => 0,
                ]);
            }

            if ($key === 'role_permissions') {
                $values = array_merge($values, [
                    'can_view' => 0,
                    'can_create' => 0,
                    'can_update' => 0,
                    'can_delete' => 0,
                ]);
            }

            $exists = $db->table('admin_role_menu_permissions')
                ->where('role_slug', $role)
                ->where('menu_key', $key)
                ->countAllResults() > 0;

            if ($exists) {
                $db->table('admin_role_menu_permissions')
                    ->where('role_slug', $role)
                    ->where('menu_key', $key)
                    ->update($values);
                continue;
            }

            $db->table('admin_role_menu_permissions')->insert(array_merge($values, [
                'role_slug' => $role,
                'menu_key' => $key,
                'created_at' => $now,
            ]));
        }
    }

    public function can(string $menuKey, string $action = 'view', ?string $role = null): bool
    {
        $role = $this->normalizeRole($role ?? (string) session('admin_role'));

        if ($role === self::SUPER_ADMIN || $menuKey === 'profile') {
            return true;
        }

        $permissions = $this->getPermissionsByRole($role);
        $row = $permissions[$menuKey] ?? null;

        if (!$row) {
            return false;
        }

        $column = 'can_' . $action;

        return isset($row[$column]) && (int) $row[$column] === 1;
    }

    public function canAccessPath(string $path, string $method): bool
    {
        $this->ensureSchema();

        $path = trim($path, '/');
        $method = strtoupper($method);

        if ($path === '' || !str_starts_with($path, 'dashboard')) {
            return true;
        }

        if ($path === 'dashboard/profile' || str_starts_with($path, 'dashboard/profile/')) {
            return true;
        }

        if ($path === 'dashboard/hak-akses' || str_starts_with($path, 'dashboard/hak-akses/')) {
            return $this->normalizeRole((string) session('admin_role')) === self::SUPER_ADMIN;
        }

        $menuKey = $this->menuKeyForPath($path);

        if ($menuKey === null) {
            return $this->normalizeRole((string) session('admin_role')) === self::SUPER_ADMIN;
        }

        return $this->can($menuKey, $this->actionForPath($path, $method));
    }

    public function getVisibleSidebarMenus(?string $role = null): array
    {
        $this->ensureSchema();
        $role = $this->normalizeRole($role ?? (string) session('admin_role'));
        $permissions = $role === self::SUPER_ADMIN ? [] : $this->getPermissionsByRole($role);
        $visible = [];

        foreach ($this->menus as $menu) {
            $key = $menu['key'];

            if (!empty($menu['always_visible']) || $role === self::SUPER_ADMIN || !empty($permissions[$key]['can_view'])) {
                $visible[] = $menu;
            }
        }

        return $visible;
    }

    public function normalizeRole(string $role): string
    {
        return $role === 'admin' || $role === '' ? self::SUPER_ADMIN : $role;
    }

    private function seedRoles(): void
    {
        $now = date('Y-m-d H:i:s');
        $roles = [
            self::SUPER_ADMIN => [
                'name' => 'Super Admin',
                'description' => 'Akses penuh ke seluruh dashboard dan pengaturan hak akses.',
                'is_system' => 1,
            ],
            self::ADMIN_SPMB => [
                'name' => 'Admin SPMB',
                'description' => 'Akses operasional SPMB sesuai menu dan CRUD yang dipilih super admin.',
                'is_system' => 1,
            ],
        ];

        foreach ($roles as $slug => $data) {
            $this->upsert('admin_roles', 'slug', $slug, array_merge($data, [
                'slug' => $slug,
                'updated_at' => $now,
            ], $this->createdAtIfMissing('admin_roles', 'slug', $slug, $now)));
        }
    }

    private function seedMenus(): void
    {
        $now = date('Y-m-d H:i:s');

        foreach ($this->menus as $menu) {
            $this->upsert('admin_menus', 'menu_key', $menu['key'], [
                'menu_key' => $menu['key'],
                'label' => $menu['label'],
                'category' => $menu['category'],
                'icon' => $menu['icon'],
                'url' => $menu['url'],
                'sort_order' => $menu['sort_order'],
                'is_active' => 1,
                'updated_at' => $now,
            ] + $this->createdAtIfMissing('admin_menus', 'menu_key', $menu['key'], $now));
        }
    }

    private function seedDefaultPermissions(): void
    {
        $now = date('Y-m-d H:i:s');
        $adminSpmbDefaults = [
            'dashboard' => ['view'],
            'support_chat' => ['view', 'update'],
            'history_chat' => ['view'],
            'intents' => ['view'],
            'training_phrases' => ['view'],
            'nlp_rules' => ['view'],
            'profile' => ['view', 'update'],
        ];

        foreach ([self::SUPER_ADMIN, self::ADMIN_SPMB] as $role) {
            foreach ($this->menus as $menu) {
                $key = $menu['key'];
                $existing = db_connect()->table('admin_role_menu_permissions')
                    ->where('role_slug', $role)
                    ->where('menu_key', $key)
                    ->get()
                    ->getRowArray();

                if ($existing) {
                    continue;
                }

                $actions = $role === self::SUPER_ADMIN
                    ? ['view', 'create', 'update', 'delete']
                    : ($adminSpmbDefaults[$key] ?? []);

                db_connect()->table('admin_role_menu_permissions')->insert([
                    'role_slug' => $role,
                    'menu_key' => $key,
                    'can_view' => in_array('view', $actions, true) ? 1 : 0,
                    'can_create' => in_array('create', $actions, true) ? 1 : 0,
                    'can_update' => in_array('update', $actions, true) ? 1 : 0,
                    'can_delete' => in_array('delete', $actions, true) ? 1 : 0,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }

    private function createEmptyPermissions(string $role): void
    {
        $now = date('Y-m-d H:i:s');

        foreach ($this->getMenus() as $menu) {
            db_connect()->table('admin_role_menu_permissions')->insert([
                'role_slug' => $role,
                'menu_key' => $menu['menu_key'],
                'can_view' => $menu['menu_key'] === 'profile' ? 1 : 0,
                'can_create' => 0,
                'can_update' => $menu['menu_key'] === 'profile' ? 1 : 0,
                'can_delete' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    private function uniqueRoleSlug(string $name): string
    {
        $base = strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '_', $name) ?? '', '_'));
        $base = $base !== '' ? $base : 'role';
        $slug = $base;
        $suffix = 2;

        while (db_connect()->table('admin_roles')->where('slug', $slug)->countAllResults() > 0) {
            $slug = $base . '_' . $suffix;
            $suffix++;
        }

        return $slug;
    }

    private function upsert(string $table, string $keyColumn, string $keyValue, array $data): void
    {
        $db = db_connect();
        $exists = $db->table($table)->where($keyColumn, $keyValue)->countAllResults() > 0;

        if ($exists) {
            unset($data[$keyColumn], $data['created_at']);
            $db->table($table)->where($keyColumn, $keyValue)->update($data);
            return;
        }

        $db->table($table)->insert($data);
    }

    private function createdAtIfMissing(string $table, string $keyColumn, string $keyValue, string $now): array
    {
        $exists = db_connect()->table($table)->where($keyColumn, $keyValue)->countAllResults() > 0;

        return $exists ? [] : ['created_at' => $now];
    }

    private function menuKeyForPath(string $path): ?string
    {
        $matches = $this->routeMenus;
        uksort($matches, static fn ($a, $b) => strlen($b) <=> strlen($a));

        foreach ($matches as $prefix => $menuKey) {
            if ($path === $prefix || str_starts_with($path, $prefix . '/')) {
                return $menuKey;
            }
        }

        return null;
    }

    private function actionForPath(string $path, string $method): string
    {
        if ($method === 'GET') {
            if (str_ends_with($path, '/create')) {
                return 'create';
            }

            if (str_ends_with($path, '/edit')) {
                return 'update';
            }

            return 'view';
        }

        if (str_ends_with($path, '/delete')) {
            return 'delete';
        }

        if ($method === 'POST' && preg_match('~/create$~', $path)) {
            return 'create';
        }

        if ($method === 'POST' && preg_match('~/[0-9]+$~', $path)) {
            return 'update';
        }

        if ($method === 'POST' && preg_match('~/(toggle|settings|server-start|server-stop|retrain|evaluate-naive-bayes)$~', $path)) {
            return 'update';
        }

        return $method === 'POST' ? 'create' : 'view';
    }
}
