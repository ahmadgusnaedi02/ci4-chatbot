<?= $this->include('dashboard/layout/header') ?>
<?= $this->include('dashboard/layout/navbar') ?>
<?= $this->include('dashboard/layout/sidebar') ?>

<?php
    $assetUrl = static function (?string $path): string {
        $path = trim((string) $path);

        if ($path === '') {
            return '';
        }

        return str_starts_with($path, 'http://') || str_starts_with($path, 'https://')
            ? $path
            : base_url($path);
    };
?>

<div class="main-panel">
    <div class="content-wrapper admin-content">
        <div class="admin-page-head">
            <div>
                <p class="admin-eyebrow mb-2">Pengaturan Website</p>
                <h2 class="admin-page-title mb-2">Landing Page</h2>
                <p class="admin-page-subtitle mb-0">Kelola logo, foto utama, konten profil, program, kontak, dan berita sekolah.</p>
            </div>
            <a class="btn btn-outline-secondary mt-3 mt-md-0" href="<?= site_url('/') ?>" target="_blank">
                <i class="mdi mdi-open-in-new me-1"></i> Lihat Halaman
            </a>
        </div>

        <?php if (session('success')): ?>
            <div class="alert alert-success"><?= esc(session('success')) ?></div>
        <?php endif; ?>

        <?php if (session('error')): ?>
            <div class="alert alert-danger"><?= esc(session('error')) ?></div>
        <?php endif; ?>

        <form action="<?= site_url('dashboard/landing-page/settings') ?>" method="post" enctype="multipart/form-data">
            <?= csrf_field() ?>

            <div class="row">
                <div class="col-lg-4 grid-margin stretch-card">
                    <div class="card admin-card landing-preview-card">
                        <div class="card-body">
                            <div class="admin-card-head">
                                <h4>Logo & Foto Hero</h4>
                                <p>Upload gambar baru untuk mengganti tampilan landing page.</p>
                            </div>

                            <div class="landing-image-preview logo">
                                <img id="logoPreview" src="<?= esc($assetUrl($settings['logo_url'] ?? ''), 'attr') ?>" alt="Logo landing page">
                            </div>
                            <label class="btn admin-yellow-btn w-100 mb-4" for="logo">
                                <i class="mdi mdi-image-edit-outline me-1"></i> Ganti Logo
                            </label>
                            <input class="d-none landing-file-input" id="logo" name="logo" type="file"
                                accept="image/png,image/jpeg,image/webp" data-preview="logoPreview">

                            <div class="landing-image-preview hero">
                                <img id="heroPreview" src="<?= esc($assetUrl($settings['hero_image_url'] ?? ''), 'attr') ?>" alt="Foto hero landing page">
                            </div>
                            <label class="btn btn-outline-primary w-100" for="hero_image">
                                <i class="mdi mdi-camera-outline me-1"></i> Ganti Foto Landing
                            </label>
                            <input class="d-none landing-file-input" id="hero_image" name="hero_image" type="file"
                                accept="image/png,image/jpeg,image/webp" data-preview="heroPreview">
                        </div>
                    </div>
                </div>

                <div class="col-lg-8 grid-margin stretch-card">
                    <div class="card admin-card">
                        <div class="card-body">
                            <div class="admin-card-head">
                                <h4>Konten Utama</h4>
                                <p>Teks ini tampil pada navbar, hero, profil sekolah, SPMB, dan kontak.</p>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label" for="site_name">Nama Website</label>
                                    <input class="form-control" id="site_name" name="site_name" type="text"
                                        value="<?= esc(old('site_name', $settings['site_name'] ?? '')) ?>">
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-label" for="brand_line_1">Brand Baris 1</label>
                                    <input class="form-control" id="brand_line_1" name="brand_line_1" type="text"
                                        value="<?= esc(old('brand_line_1', $settings['brand_line_1'] ?? '')) ?>">
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-label" for="brand_line_2">Brand Baris 2</label>
                                    <input class="form-control" id="brand_line_2" name="brand_line_2" type="text"
                                        value="<?= esc(old('brand_line_2', $settings['brand_line_2'] ?? '')) ?>">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label" for="hero_title">Judul Hero</label>
                                <input class="form-control" id="hero_title" name="hero_title" type="text"
                                    value="<?= esc(old('hero_title', $settings['hero_title'] ?? '')) ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="hero_subtitle">Deskripsi Hero</label>
                                <textarea class="form-control" id="hero_subtitle" name="hero_subtitle" rows="3"><?= esc(old('hero_subtitle', $settings['hero_subtitle'] ?? '')) ?></textarea>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label" for="about_title">Judul Tentang Sekolah</label>
                                    <input class="form-control" id="about_title" name="about_title" type="text"
                                        value="<?= esc(old('about_title', $settings['about_title'] ?? '')) ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label" for="program_title">Judul Program</label>
                                    <input class="form-control" id="program_title" name="program_title" type="text"
                                        value="<?= esc(old('program_title', $settings['program_title'] ?? '')) ?>">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label" for="staff_title">Judul Tenaga Pendidik</label>
                                    <input class="form-control" id="staff_title" name="staff_title" type="text"
                                        value="<?= esc(old('staff_title', $settings['staff_title'] ?? '')) ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label" for="staff_subtitle">Deskripsi Tenaga Pendidik</label>
                                    <input class="form-control" id="staff_subtitle" name="staff_subtitle" type="text"
                                        value="<?= esc(old('staff_subtitle', $settings['staff_subtitle'] ?? '')) ?>">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="about_text">Deskripsi Tentang Sekolah</label>
                                <textarea class="form-control" id="about_text" name="about_text" rows="3"><?= esc(old('about_text', $settings['about_text'] ?? '')) ?></textarea>
                            </div>

                            <div class="row">
                                <?php for ($index = 1; $index <= 3; $index++): ?>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label" for="stat_<?= $index ?>_number">Stat <?= $index ?></label>
                                        <input class="form-control mb-2" id="stat_<?= $index ?>_number" name="stat_<?= $index ?>_number" type="text"
                                            value="<?= esc(old('stat_' . $index . '_number', $settings['stat_' . $index . '_number'] ?? '')) ?>">
                                        <input class="form-control" name="stat_<?= $index ?>_label" type="text"
                                            value="<?= esc(old('stat_' . $index . '_label', $settings['stat_' . $index . '_label'] ?? '')) ?>">
                                    </div>
                                <?php endfor; ?>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label" for="spmb_title">Judul SPMB</label>
                                    <input class="form-control" id="spmb_title" name="spmb_title" type="text"
                                        value="<?= esc(old('spmb_title', $settings['spmb_title'] ?? '')) ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label" for="contact_title">Judul Kontak</label>
                                    <input class="form-control" id="contact_title" name="contact_title" type="text"
                                        value="<?= esc(old('contact_title', $settings['contact_title'] ?? '')) ?>">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="spmb_text">Deskripsi SPMB</label>
                                <textarea class="form-control" id="spmb_text" name="spmb_text" rows="2"><?= esc(old('spmb_text', $settings['spmb_text'] ?? '')) ?></textarea>
                            </div>

                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label" for="contact_address">Alamat</label>
                                    <input class="form-control" id="contact_address" name="contact_address" type="text"
                                        value="<?= esc(old('contact_address', $settings['contact_address'] ?? '')) ?>">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label" for="contact_phone">Telepon</label>
                                    <input class="form-control" id="contact_phone" name="contact_phone" type="text"
                                        value="<?= esc(old('contact_phone', $settings['contact_phone'] ?? '')) ?>">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label" for="contact_email">Email</label>
                                    <input class="form-control" id="contact_email" name="contact_email" type="email"
                                        value="<?= esc(old('contact_email', $settings['contact_email'] ?? '')) ?>">
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-3 mb-3">
                                    <label class="form-label" for="facebook_url">Facebook</label>
                                    <input class="form-control" id="facebook_url" name="facebook_url" type="text"
                                        value="<?= esc(old('facebook_url', $settings['facebook_url'] ?? '#')) ?>">
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-label" for="instagram_url">Instagram</label>
                                    <input class="form-control" id="instagram_url" name="instagram_url" type="text"
                                        value="<?= esc(old('instagram_url', $settings['instagram_url'] ?? '#')) ?>">
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-label" for="twitter_url">Twitter/X</label>
                                    <input class="form-control" id="twitter_url" name="twitter_url" type="text"
                                        value="<?= esc(old('twitter_url', $settings['twitter_url'] ?? '#')) ?>">
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-label" for="youtube_url">YouTube</label>
                                    <input class="form-control" id="youtube_url" name="youtube_url" type="text"
                                        value="<?= esc(old('youtube_url', $settings['youtube_url'] ?? '#')) ?>">
                                </div>
                            </div>

                            <div class="admin-profile-actions">
                                <button class="btn admin-primary-btn" type="submit">
                                    <i class="mdi mdi-content-save me-1"></i> Simpan Landing Page
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>

        <div class="row" id="program">
            <?php foreach ($programs as $program): ?>
                <div class="col-lg-4 grid-margin stretch-card">
                    <form class="card admin-card" action="<?= site_url('dashboard/landing-page/programs/' . $program['id']) ?>" method="post">
                        <?= csrf_field() ?>
                        <div class="card-body">
                            <div class="admin-card-head">
                                <h4><i class="<?= esc($program['icon'], 'attr') ?> me-2"></i> Program</h4>
                                <p>Ubah kartu program unggulan.</p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Icon Font Awesome</label>
                                <input class="form-control" name="icon" type="text" value="<?= esc($program['icon']) ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Judul</label>
                                <input class="form-control" name="title" type="text" value="<?= esc($program['title']) ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Deskripsi</label>
                                <textarea class="form-control" name="description" rows="3" required><?= esc($program['description']) ?></textarea>
                            </div>
                            <div class="row">
                                <div class="col-6 mb-3">
                                    <label class="form-label">Status</label>
                                    <select class="form-control" name="status">
                                        <option value="active" <?= $program['status'] === 'active' ? 'selected' : '' ?>>Active</option>
                                        <option value="inactive" <?= $program['status'] === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                                    </select>
                                </div>
                                <div class="col-6 mb-3">
                                    <label class="form-label">Urutan</label>
                                    <input class="form-control" name="sort_order" type="number" value="<?= esc((string) $program['sort_order']) ?>">
                                </div>
                            </div>
                            <button class="btn admin-primary-btn w-100" type="submit">
                                <i class="mdi mdi-content-save me-1"></i> Simpan Program
                            </button>
                        </div>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="card admin-card mb-4" id="tenaga-pendidik">
            <div class="card-body">
                <div class="admin-page-head mb-4">
                    <div>
                        <p class="admin-eyebrow mb-2">Profil Sekolah</p>
                        <h2 class="admin-page-title mb-2">Tenaga Pendidik</h2>
                        <p class="admin-page-subtitle mb-0">Kelola kepala sekolah, guru, dan staf yang tampil di landing page.</p>
                    </div>
                    <button class="btn admin-primary-btn mt-3 mt-md-0" type="button" data-bs-toggle="modal" data-bs-target="#createStaffModal">
                        <i class="mdi mdi-plus me-1"></i> Tambah Tenaga Pendidik
                    </button>
                </div>

                <div class="table-responsive">
                    <table class="table admin-kb-table align-middle">
                        <thead>
                            <tr>
                                <th>Nama</th>
                                <th>Jabatan</th>
                                <th>Status</th>
                                <th>Urutan</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($staffItems as $item): ?>
                                <tr>
                                    <td>
                                        <div class="landing-person-cell">
                                            <img src="<?= esc($assetUrl($item['photo_url'] ?? ''), 'attr') ?>" alt="<?= esc($item['name'], 'attr') ?>">
                                            <div>
                                                <strong><?= esc($item['name']) ?></strong>
                                                <p><?= esc($item['bio'] ?? '') ?></p>
                                            </div>
                                        </div>
                                    </td>
                                    <td><span class="admin-code-chip"><?= esc($item['position']) ?></span></td>
                                    <td><span class="badge <?= $item['status'] === 'active' ? 'badge-success' : 'badge-secondary' ?>"><?= esc($item['status']) ?></span></td>
                                    <td><?= esc((string) $item['sort_order']) ?></td>
                                    <td>
                                        <div class="admin-row-actions">
                                            <button class="btn btn-sm btn-outline-primary me-2" type="button" data-bs-toggle="modal"
                                                data-bs-target="#editStaffModal<?= (int) $item['id'] ?>">
                                                <i class="mdi mdi-pencil"></i>
                                            </button>
                                            <form action="<?= site_url('dashboard/landing-page/staff/' . $item['id'] . '/delete') ?>" method="post" data-confirm="Hapus tenaga pendidik ini dari landing page?">
                                                <?= csrf_field() ?>
                                                <button class="btn btn-sm btn-outline-danger" type="submit">
                                                    <i class="mdi mdi-delete"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="card admin-card" id="berita">
            <div class="card-body">
                <div class="admin-page-head mb-4">
                    <div>
                        <p class="admin-eyebrow mb-2">Berita & Artikel</p>
                        <h2 class="admin-page-title mb-2">Update Berita</h2>
                        <p class="admin-page-subtitle mb-0">Berita berstatus published akan muncul di landing page.</p>
                    </div>
                    <button class="btn admin-primary-btn mt-3 mt-md-0" type="button" data-bs-toggle="modal" data-bs-target="#createNewsModal">
                        <i class="mdi mdi-plus me-1"></i> Tambah Berita
                    </button>
                </div>

                <div class="table-responsive">
                    <table class="table admin-kb-table align-middle">
                        <thead>
                            <tr>
                                <th>Berita</th>
                                <th>Status</th>
                                <th>Urutan</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($newsItems as $item): ?>
                                <tr>
                                    <td>
                                        <div class="landing-news-cell">
                                            <img src="<?= esc($assetUrl($item['image_url'] ?? ''), 'attr') ?>" alt="<?= esc($item['title'], 'attr') ?>">
                                            <div>
                                                <strong><?= esc($item['title']) ?></strong>
                                                <p><?= esc($item['excerpt']) ?></p>
                                            </div>
                                        </div>
                                    </td>
                                    <td><span class="badge <?= $item['status'] === 'published' ? 'badge-success' : 'badge-secondary' ?>"><?= esc($item['status']) ?></span></td>
                                    <td><?= esc((string) $item['sort_order']) ?></td>
                                    <td>
                                        <div class="admin-row-actions">
                                            <button class="btn btn-sm btn-outline-primary me-2" type="button" data-bs-toggle="modal"
                                                data-bs-target="#editNewsModal<?= (int) $item['id'] ?>">
                                                <i class="mdi mdi-pencil"></i>
                                            </button>
                                            <form action="<?= site_url('dashboard/landing-page/news/' . $item['id'] . '/delete') ?>" method="post" data-confirm="Hapus berita ini dari landing page?">
                                                <?= csrf_field() ?>
                                                <button class="btn btn-sm btn-outline-danger" type="submit">
                                                    <i class="mdi mdi-delete"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="createStaffModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <form class="modal-content admin-modal" action="<?= site_url('dashboard/landing-page/staff') ?>" method="post" enctype="multipart/form-data">
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Tenaga Pendidik</h5>
                    <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <?= view('dashboard/landing_page/staff_form', ['item' => null]) ?>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-outline-secondary" type="button" data-bs-dismiss="modal">Batal</button>
                    <button class="btn admin-primary-btn" type="submit">Simpan Tenaga Pendidik</button>
                </div>
            </form>
        </div>
    </div>

    <?php foreach ($staffItems as $item): ?>
        <div class="modal fade" id="editStaffModal<?= (int) $item['id'] ?>" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <form class="modal-content admin-modal" action="<?= site_url('dashboard/landing-page/staff/' . $item['id']) ?>" method="post" enctype="multipart/form-data">
                    <?= csrf_field() ?>
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Tenaga Pendidik</h5>
                        <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <?= view('dashboard/landing_page/staff_form', ['item' => $item]) ?>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-outline-secondary" type="button" data-bs-dismiss="modal">Batal</button>
                        <button class="btn admin-primary-btn" type="submit">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    <?php endforeach; ?>

    <div class="modal fade" id="createNewsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <form class="modal-content admin-modal" action="<?= site_url('dashboard/landing-page/news') ?>" method="post" enctype="multipart/form-data">
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Berita</h5>
                    <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <?= view('dashboard/landing_page/news_form', ['item' => null]) ?>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-outline-secondary" type="button" data-bs-dismiss="modal">Batal</button>
                    <button class="btn admin-primary-btn" type="submit">Simpan Berita</button>
                </div>
            </form>
        </div>
    </div>

    <?php foreach ($newsItems as $item): ?>
        <div class="modal fade" id="editNewsModal<?= (int) $item['id'] ?>" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <form class="modal-content admin-modal" action="<?= site_url('dashboard/landing-page/news/' . $item['id']) ?>" method="post" enctype="multipart/form-data">
                    <?= csrf_field() ?>
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Berita</h5>
                        <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <?= view('dashboard/landing_page/news_form', ['item' => $item]) ?>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-outline-secondary" type="button" data-bs-dismiss="modal">Batal</button>
                        <button class="btn admin-primary-btn" type="submit">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    <?php endforeach; ?>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.landing-file-input').forEach(function (input) {
                input.addEventListener('change', function () {
                    const preview = document.getElementById(input.dataset.preview);
                    const file = input.files && input.files[0];

                    if (preview && file) {
                        preview.src = URL.createObjectURL(file);
                    }
                });
            });
        });
    </script>

    <?= $this->include('dashboard/layout/footer') ?>
