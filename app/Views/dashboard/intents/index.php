<?= $this->include('dashboard/layout/header') ?>
<?= $this->include('dashboard/layout/navbar') ?>
<?= $this->include('dashboard/layout/sidebar') ?>

<?php
$canCreateIntent = admin_can('intents', 'create');
$canUpdateIntent = admin_can('intents', 'update');
$canDeleteIntent = admin_can('intents', 'delete');
?>

<div class="main-panel">
    <div class="content-wrapper admin-content">
        <div class="admin-page-head">
            <div>
                <p class="admin-eyebrow mb-2">Dataset Chatbot</p>
                <h2 class="admin-page-title mb-2">Intents</h2>
                <p class="admin-page-subtitle mb-0">Kelola kelas intent dan response yang dipilih setelah Naive Bayes
                    mengklasifikasi pesan.</p>
            </div>
            <?php if ($canCreateIntent): ?>
                <a class="btn admin-primary-btn mt-3 mt-md-0" href="<?= site_url('dashboard/intents/create') ?>">
                    <i class="mdi mdi-plus me-1"></i> Tambah Intent
                </a>
            <?php endif; ?>
        </div>

        <?php if (session('success')): ?>
            <div class="alert alert-success"><?= esc(session('success')) ?></div>
        <?php endif; ?>
        <?php if (session('error')): ?>
            <div class="alert alert-danger"><?= esc(session('error')) ?></div>
        <?php endif; ?>

        <div class="card admin-card">
            <div class="card-body">
                <form class="row g-3 align-items-end mb-4" action="<?= site_url('dashboard/intents') ?>" method="get">
                    <div class="col-lg-6">
                        <label class="form-label" for="q">Cari Intent</label>
                        <input class="form-control" id="q" name="q" type="search" value="<?= esc($keyword) ?>"
                            placeholder="Cari intent, response, atau source">
                    </div>
                    <div class="col-lg-3">
                        <label class="form-label" for="status">Status</label>
                        <select class="form-control" id="status" name="status">
                            <option value="">Semua status</option>
                            <option value="active" <?= $status === 'active' ? 'selected' : '' ?>>Active</option>
                            <option value="inactive" <?= $status === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                            <option value="draft" <?= $status === 'draft' ? 'selected' : '' ?>>Draft</option>
                        </select>
                    </div>
                    <div class="col-lg-3 d-flex gap-2">
                        <button class="btn admin-primary-btn flex-fill" type="submit">
                            <i class="mdi mdi-magnify me-1"></i> Filter
                        </button>
                        <a class="btn btn-outline-secondary" href="<?= site_url('dashboard/intents') ?>">Reset</a>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table admin-kb-table admin-data-table admin-intents-table align-middle">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Intent</th>
                                <th>Response</th>
                                <th>Status</th>
                                <th>Priority</th>
                                <th>Source</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $no = 1 ?>
                            <?php foreach ($items as $item): ?>
                                <?php
                                $statusName = $item['status'] ?? 'inactive';
                                $badgeClass = match ($statusName) {
                                    'active' => 'success',
                                    'draft' => 'warning',
                                    default => 'secondary',
                                };
                                ?>
                                <tr>
                                    <td class="admin-table-number"><?= $no++ ?></td>
                                    <td><span class="admin-code-chip"><?= esc($item['name']) ?></span></td>
                                    <td>
                                        <div class="admin-response-preview"><?= nl2br(esc($item['response'])) ?></div>
                                    </td>
                                    <td><span
                                            class="badge badge-opacity-<?= $badgeClass ?>"><?= esc(ucfirst($statusName)) ?></span>
                                    </td>
                                    <td><?= esc((string) ($item['priority'] ?? 0)) ?></td>
                                    <td><?= esc($item['source'] ?? '-') ?></td>
                                    <td>
                                        <?php if ($canUpdateIntent || $canDeleteIntent): ?>
                                        <div class="dropdown admin-row-actions">
                                            <button class="btn btn-outline-primary btn-sm dropdown-toggle" type="button"
                                                data-bs-toggle="dropdown" aria-expanded="false">
                                                Aksi
                                            </button>
                                            <div class="dropdown-menu dropdown-menu-end">
                                                <?php if ($canUpdateIntent): ?>
                                                    <a class="dropdown-item"
                                                        href="<?= site_url('dashboard/intents/' . $item['id'] . '/edit') ?>">
                                                        <i class="mdi mdi-pencil me-2"></i>Edit
                                                    </a>
                                                    <form
                                                        action="<?= site_url('dashboard/intents/' . $item['id'] . '/toggle') ?>"
                                                        method="post">
                                                        <?= csrf_field() ?>
                                                        <button class="dropdown-item" type="submit">
                                                            <i
                                                                class="mdi mdi-power me-2"></i><?= $statusName === 'active' ? 'Nonaktifkan' : 'Aktifkan' ?>
                                                        </button>
                                                    </form>
                                                <?php endif; ?>
                                                <?php if ($canDeleteIntent): ?>
                                                    <form
                                                        action="<?= site_url('dashboard/intents/' . $item['id'] . '/delete') ?>"
                                                        method="post"
                                                        data-confirm="Hapus intent ini beserta training phrase-nya?">
                                                        <?= csrf_field() ?>
                                                        <button class="dropdown-item text-danger" type="submit">
                                                            <i class="mdi mdi-delete me-2"></i>Hapus
                                                        </button>
                                                    </form>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (empty($items)): ?>
                                <tr>
                                    <td class="text-center text-muted py-5" colspan="7">Belum ada intent.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <?= $this->include('dashboard/layout/footer') ?>
