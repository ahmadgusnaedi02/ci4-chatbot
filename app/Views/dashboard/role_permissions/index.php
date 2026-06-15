<?= $this->include('dashboard/layout/header') ?>
<?= $this->include('dashboard/layout/navbar') ?>
<?= $this->include('dashboard/layout/sidebar') ?>

<?php
$roleNames = array_column($roles, 'name', 'slug');
$crudLabels = [
    'can_view' => 'Lihat',
    'can_create' => 'Tambah',
    'can_update' => 'Edit',
    'can_delete' => 'Hapus',
];
?>

<div class="main-panel">
    <div class="content-wrapper admin-content">
        <div class="admin-page-head">
            <div>
                <p class="admin-eyebrow mb-2">Pengaturan</p>
                <h2 class="admin-page-title mb-2">Hak Akses</h2>
                <p class="admin-page-subtitle mb-0">Kelola user admin, role, dan akses menu dashboard.</p>
            </div>
        </div>

        <?php if (session('success')): ?>
            <div class="alert alert-success"><?= esc(session('success')) ?></div>
        <?php endif; ?>
        <?php if (session('error')): ?>
            <div class="alert alert-danger"><?= esc(session('error')) ?></div>
        <?php endif; ?>

        <div class="row">
            <div class="col-lg-5 mb-4">
                <div class="card admin-card h-100">
                    <div class="card-body">
                        <h4 class="card-title card-title-dash mb-1">Tambah Role</h4>
                        <p class="text-muted mb-4">Role baru akan muncul di mapping hak akses.</p>
                        <form action="<?= site_url('dashboard/hak-akses/roles') ?>" method="post">
                            <?= csrf_field() ?>
                            <div class="mb-3">
                                <label class="form-label" for="role-name">Nama Role</label>
                                <input class="form-control" id="role-name" name="name" type="text"
                                    value="<?= esc(old('name')) ?>" placeholder="contoh: Admin Keuangan" required>
                            </div>
                            <div class="mb-4">
                                <label class="form-label" for="role-description">Deskripsi</label>
                                <textarea class="form-control" id="role-description" name="description" rows="3"
                                    placeholder="Ringkasan tugas role"><?= esc(old('description')) ?></textarea>
                            </div>
                            <button class="btn admin-primary-btn" type="submit">
                                <i class="mdi mdi-shield-plus-outline me-1"></i> Tambah Role
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-7 mb-4" id="users">
                <div class="card admin-card h-100">
                    <div class="card-body">
                        <h4 class="card-title card-title-dash mb-1">Tambah User Admin</h4>
                        <p class="text-muted mb-4">User baru bisa langsung diberi role yang tersedia.</p>
                        <form action="<?= site_url('dashboard/hak-akses/users') ?>" method="post">
                            <?= csrf_field() ?>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label" for="user-name">Nama</label>
                                    <input class="form-control" id="user-name" name="name" type="text"
                                        value="<?= esc(old('name')) ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" for="user-email">Email Login</label>
                                    <input class="form-control" id="user-email" name="email" type="email"
                                        value="<?= esc(old('email')) ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" for="user-role">Role</label>
                                    <select class="form-control" id="user-role" name="role" required>
                                        <?php foreach ($assignableRoles as $role): ?>
                                            <option value="<?= esc($role['slug']) ?>"><?= esc($role['name']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" for="user-password">Password</label>
                                    <input class="form-control" id="user-password" name="password" type="password" minlength="6" required>
                                </div>
                            </div>
                            <button class="btn admin-primary-btn mt-4" type="submit">
                                <i class="mdi mdi-account-plus-outline me-1"></i> Tambah User
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="card admin-card mb-4">
            <div class="card-body">
                <form class="row g-3 align-items-end" action="<?= site_url('dashboard/hak-akses') ?>" method="get">
                    <div class="col-md-6 col-lg-4">
                        <label class="form-label" for="role">Mapping Role</label>
                        <select class="form-control" id="role" name="role">
                            <?php foreach ($roles as $role): ?>
                                <?php if (($role['slug'] ?? '') === 'super_admin'): ?>
                                    <option value="super_admin" disabled>Super Admin - akses penuh</option>
                                    <?php continue; ?>
                                <?php endif; ?>
                                <option value="<?= esc($role['slug']) ?>" <?= $selectedRole === $role['slug'] ? 'selected' : '' ?>>
                                    <?= esc($role['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button class="btn admin-primary-btn" type="submit">
                            <i class="mdi mdi-filter-outline me-1"></i> Tampilkan
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <form action="<?= site_url('dashboard/hak-akses') ?>" method="post">
            <?= csrf_field() ?>
            <input type="hidden" name="role" value="<?= esc($selectedRole) ?>">

            <div class="card admin-card mb-4">
                <div class="card-body">
                    <div class="d-flex flex-column flex-md-row justify-content-between gap-3 mb-4">
                        <div>
                            <h4 class="card-title card-title-dash mb-1"><?= esc($roleNames[$selectedRole] ?? 'Role') ?></h4>
                            <p class="text-muted mb-0">Centang menu dan aksi CRUD yang boleh digunakan role ini.</p>
                        </div>
                        <button class="btn admin-primary-btn align-self-md-start" type="submit">
                            <i class="mdi mdi-content-save-outline me-1"></i> Simpan Hak Akses
                        </button>
                    </div>

                    <div class="table-responsive">
                        <table class="table admin-kb-table align-middle">
                            <thead>
                                <tr>
                                    <th>Menu</th>
                                    <th>Kategori</th>
                                    <?php foreach ($crudLabels as $label): ?>
                                        <th class="text-center"><?= esc($label) ?></th>
                                    <?php endforeach; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($menus as $menu): ?>
                                    <?php
                                    $key = $menu['menu_key'];
                                    $row = $rolePermissions[$key] ?? [];
                                    $isProfile = $key === 'profile';
                                    $isRoleMenu = $key === 'role_permissions';
                                    ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <i class="<?= esc($menu['icon'] ?? 'mdi mdi-menu') ?> text-primary"></i>
                                                <span class="fw-semibold"><?= esc($menu['label']) ?></span>
                                            </div>
                                        </td>
                                        <td><?= esc($menu['category'] ?: '-') ?></td>
                                        <?php foreach ($crudLabels as $field => $label): ?>
                                            <?php
                                            $locked = $isProfile || $isRoleMenu;
                                            $checked = $locked
                                                ? ($isProfile && in_array($field, ['can_view', 'can_update'], true))
                                                : ((int) ($row[$field] ?? 0) === 1);
                                            ?>
                                            <td class="text-center">
                                                <div class="form-check d-inline-flex justify-content-center">
                                                    <input class="form-check-input" type="checkbox"
                                                        name="permissions[<?= esc($key) ?>][<?= esc($field) ?>]"
                                                        value="1"
                                                        <?= $checked ? 'checked' : '' ?>
                                                        <?= $locked ? 'disabled' : '' ?>>
                                                </div>
                                            </td>
                                        <?php endforeach; ?>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </form>

        <div class="card admin-card">
            <div class="card-body">
                <h4 class="card-title card-title-dash mb-1">Daftar User Admin</h4>
                <p class="text-muted mb-4">Kosongkan password jika tidak ingin mengganti password user.</p>

                <div class="table-responsive">
                    <table class="table admin-kb-table align-middle">
                        <thead>
                            <tr>
                                <th>Nama</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Password Baru</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($adminUsers as $user): ?>
                                <tr>
                                    <td>
                                        <input class="form-control" name="name" type="text"
                                            value="<?= esc($user['name']) ?>"
                                            form="user-update-<?= esc((string) $user['id']) ?>" required>
                                    </td>
                                    <td>
                                        <input class="form-control" name="email" type="email"
                                            value="<?= esc($user['email']) ?>"
                                            form="user-update-<?= esc((string) $user['id']) ?>" required>
                                    </td>
                                    <td>
                                        <select class="form-control" name="role" form="user-update-<?= esc((string) $user['id']) ?>" required>
                                            <?php foreach ($assignableRoles as $role): ?>
                                                <option value="<?= esc($role['slug']) ?>" <?= $user['role'] === $role['slug'] ? 'selected' : '' ?>>
                                                    <?= esc($role['name']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </td>
                                    <td>
                                        <input class="form-control" name="password" type="password" minlength="6"
                                            placeholder="Opsional"
                                            form="user-update-<?= esc((string) $user['id']) ?>">
                                    </td>
                                    <td>
                                        <form id="user-update-<?= esc((string) $user['id']) ?>"
                                            action="<?= site_url('dashboard/hak-akses/users/' . $user['id']) ?>"
                                            method="post">
                                            <?= csrf_field() ?>
                                        </form>
                                        <button class="btn btn-outline-primary btn-sm" type="submit"
                                            form="user-update-<?= esc((string) $user['id']) ?>">
                                            <i class="mdi mdi-content-save-outline me-1"></i> Simpan
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (empty($adminUsers)): ?>
                                <tr>
                                    <td class="text-center text-muted py-5" colspan="5">Belum ada user admin.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <?= $this->include('dashboard/layout/footer') ?>
