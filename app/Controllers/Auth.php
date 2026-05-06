<?php

namespace App\Controllers;

use CodeIgniter\HTTP\RedirectResponse;

class Auth extends BaseController
{
    private function ensureAdminTable(): void
    {
        $db = db_connect();

        $db->query("
            CREATE TABLE IF NOT EXISTS admin_users (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(120) NOT NULL,
                email VARCHAR(150) NOT NULL UNIQUE,
                password_hash VARCHAR(255) NOT NULL,
                role VARCHAR(50) NOT NULL DEFAULT 'admin',
                created_at DATETIME NULL,
                updated_at DATETIME NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
        ");

        $count = (int) $db->table('admin_users')->countAllResults();

        if ($count === 0) {
            $now = date('Y-m-d H:i:s');
            $db->table('admin_users')->insert([
                'name' => 'Admin Sekolah',
                'email' => 'admin@sekolah.test',
                'password_hash' => password_hash('admin123', PASSWORD_DEFAULT),
                'role' => 'admin',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function login(): string|RedirectResponse
    {
        if (session('admin_logged_in')) {
            return redirect()->to(site_url('dashboard'));
        }

        $this->ensureAdminTable();

        return view('auth/login');
    }

    public function attemptLogin(): RedirectResponse
    {
        $this->ensureAdminTable();

        $email = trim((string) $this->request->getPost('email'));
        $password = (string) $this->request->getPost('password');

        if ($email === '' || $password === '') {
            return redirect()->back()->withInput()->with('error', 'Email dan password wajib diisi.');
        }

        $admin = db_connect()->table('admin_users')
            ->where('email', $email)
            ->get()
            ->getRowArray();

        if (!$admin || !password_verify($password, $admin['password_hash'])) {
            return redirect()->back()->withInput()->with('error', 'Email atau password salah.');
        }

        session()->regenerate();
        session()->set([
            'admin_logged_in' => true,
            'admin_id' => (int) $admin['id'],
            'admin_name' => $admin['name'],
            'admin_email' => $admin['email'],
            'admin_role' => $admin['role'],
        ]);

        return redirect()->to(site_url('dashboard'));
    }

    public function logout(): RedirectResponse
    {
        session()->remove([
            'admin_logged_in',
            'admin_id',
            'admin_name',
            'admin_email',
            'admin_role',
        ]);

        return redirect()->to(site_url('admin/login'))->with('success', 'Anda sudah logout.');
    }
}
