<?= $this->include('dashboard/layout/header') ?>
<?= $this->include('dashboard/layout/navbar') ?>
<?= $this->include('dashboard/layout/sidebar') ?>

<?php
    $avatar = $admin['avatar_url'] ?? '';
    $avatarUrl = $avatar !== '' ? base_url($avatar) : base_url('assets/images/faces/face8.jpg');
?>

<div class="main-panel">
    <div class="content-wrapper admin-content">
        <div class="admin-page-head">
            <div>
                <p class="admin-eyebrow mb-2">Akun Admin</p>
                <h2 class="admin-page-title mb-2">Setting Profil</h2>
                <p class="admin-page-subtitle mb-0">Perbarui identitas, email login, password, dan foto profil dashboard.</p>
            </div>
        </div>

        <?php if (session('success')): ?>
            <div class="alert alert-success"><?= esc(session('success')) ?></div>
        <?php endif; ?>

        <?php if (session('error')): ?>
            <div class="alert alert-danger"><?= esc(session('error')) ?></div>
        <?php endif; ?>

        <form action="<?= site_url('dashboard/profile') ?>" method="post" enctype="multipart/form-data">
            <?= csrf_field() ?>

            <div class="row">
                <div class="col-lg-4 grid-margin stretch-card">
                    <div class="card admin-card admin-profile-card">
                        <div class="card-body">
                            <div class="admin-profile-photo-wrap">
                                <img id="avatarPreview" class="admin-profile-photo" src="<?= $avatarUrl ?>" alt="Foto profil admin">
                                <label class="btn admin-yellow-btn admin-profile-upload" for="avatar">
                                    <i class="mdi mdi-camera-outline me-1"></i> Ubah Foto
                                </label>
                                <input class="d-none" id="avatar" name="avatar" type="file" accept="image/png,image/jpeg,image/webp">
                            </div>

                            <h4 class="admin-profile-name"><?= esc($admin['name'] ?? 'Admin Sekolah') ?></h4>
                            <p class="admin-profile-email"><?= esc($admin['email'] ?? 'admin@sekolah.test') ?></p>

                            <div class="admin-profile-note">
                                <i class="mdi mdi-shield-account-outline"></i>
                                <span>Gunakan password yang kuat agar akses panel admin tetap aman.</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-8 grid-margin stretch-card">
                    <div class="card admin-card">
                        <div class="card-body">
                            <div class="admin-card-head">
                                <h4>Informasi Profil</h4>
                                <p>Data ini dipakai untuk tampilan navbar dan akun login admin.</p>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label" for="name">Nama Admin</label>
                                    <input class="form-control" id="name" name="name" type="text" required
                                        value="<?= esc(old('name', $admin['name'] ?? '')) ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label" for="email">Email Login</label>
                                    <input class="form-control" id="email" name="email" type="email" required
                                        value="<?= esc(old('email', $admin['email'] ?? '')) ?>">
                                </div>
                            </div>

                            <div class="admin-profile-divider"></div>

                            <div class="admin-card-head">
                                <h4>Ubah Password</h4>
                                <p>Kosongkan bagian ini jika tidak ingin mengganti password.</p>
                            </div>

                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label" for="current_password">Password Saat Ini</label>
                                    <input class="form-control" id="current_password" name="current_password" type="password"
                                        autocomplete="current-password">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label" for="new_password">Password Baru</label>
                                    <input class="form-control" id="new_password" name="new_password" type="password"
                                        autocomplete="new-password">
                                </div>
                                <div class="col-md-4 mb-4">
                                    <label class="form-label" for="confirm_password">Konfirmasi Password</label>
                                    <input class="form-control" id="confirm_password" name="confirm_password" type="password"
                                        autocomplete="new-password">
                                </div>
                            </div>

                            <div class="admin-profile-actions">
                                <a class="btn btn-outline-secondary" href="<?= site_url('dashboard') ?>">
                                    <i class="mdi mdi-arrow-left me-1"></i> Kembali
                                </a>
                                <button class="btn admin-primary-btn" type="submit">
                                    <i class="mdi mdi-content-save me-1"></i> Simpan Profil
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const avatarInput = document.getElementById('avatar');
            const avatarPreview = document.getElementById('avatarPreview');

            if (!avatarInput || !avatarPreview) {
                return;
            }

            avatarInput.addEventListener('change', function () {
                const file = avatarInput.files && avatarInput.files[0];

                if (!file) {
                    return;
                }

                avatarPreview.src = URL.createObjectURL(file);
            });
        });
    </script>

    <?= $this->include('dashboard/layout/footer') ?>
