<?= $this->include('dashboard/layout/header') ?>
<?= $this->include('dashboard/layout/navbar') ?>
<?= $this->include('dashboard/layout/sidebar') ?>

<div class="main-panel">
    <div class="content-wrapper admin-content">
        <div class="admin-page-head">
            <div>
                <p class="admin-eyebrow mb-2">Dataset Chatbot</p>
                <h2 class="admin-page-title mb-2">Knowledge Base</h2>
                <p class="admin-page-subtitle mb-0">Kelola pertanyaan, intent, keyword, dan response yang dipakai chatbot.</p>
            </div>
            <a class="btn admin-primary-btn mt-3 mt-md-0" href="<?= site_url('dashboard/knowledge-base/create') ?>">
                <i class="mdi mdi-plus me-1"></i> Tambah Data
            </a>
        </div>

        <?php if (session('success')): ?>
            <div class="alert alert-success"><?= esc(session('success')) ?></div>
        <?php endif; ?>
        <?php if (session('error')): ?>
            <div class="alert alert-danger"><?= esc(session('error')) ?></div>
        <?php endif; ?>

        <div class="card admin-card">
            <div class="card-body">
                <form class="row g-3 align-items-end mb-4" action="<?= site_url('dashboard/knowledge-base') ?>" method="get">
                    <div class="col-lg-6">
                        <label class="form-label" for="q">Cari Data</label>
                        <input class="form-control" id="q" name="q" type="search" value="<?= esc($keyword) ?>"
                            placeholder="Cari pertanyaan, intent, keyword, atau response">
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
                        <a class="btn btn-outline-secondary" href="<?= site_url('dashboard/knowledge-base') ?>">Reset</a>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table admin-kb-table align-middle">
                        <thead>
                            <tr>
                                <th>Pertanyaan</th>
                                <th>Intent</th>
                                <th>Keyword</th>
                                <th>Response</th>
                                <th>Status</th>
                                <th>Priority</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($items)): ?>
                                <tr>
                                    <td class="text-center text-muted py-5" colspan="7">Belum ada data knowledge base.</td>
                                </tr>
                            <?php endif; ?>
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
                                    <td>
                                        <strong><?= esc($item['pertanyaan']) ?></strong>
                                        <small class="text-muted d-block">Source: <?= esc($item['source'] ?? '-') ?></small>
                                    </td>
                                    <td><span class="admin-code-chip"><?= esc($item['intent']) ?></span></td>
                                    <td class="admin-kb-clamp"><?= esc($item['keyword']) ?></td>
                                    <td class="admin-kb-clamp"><?= esc($item['response']) ?></td>
                                    <td><span class="badge badge-opacity-<?= $badgeClass ?>"><?= esc(ucfirst($statusName)) ?></span></td>
                                    <td><?= esc((string) ($item['priority'] ?? 0)) ?></td>
                                    <td>
                                        <div class="d-flex flex-wrap gap-2">
                                            <a class="btn btn-outline-primary btn-sm" href="<?= site_url('dashboard/knowledge-base/' . $item['id'] . '/edit') ?>">
                                                <i class="mdi mdi-pencil"></i>
                                            </a>
                                            <form action="<?= site_url('dashboard/knowledge-base/' . $item['id'] . '/toggle') ?>" method="post">
                                                <?= csrf_field() ?>
                                                <button class="btn btn-outline-secondary btn-sm" type="submit">
                                                    <?= $statusName === 'active' ? 'Nonaktif' : 'Aktifkan' ?>
                                                </button>
                                            </form>
                                            <form action="<?= site_url('dashboard/knowledge-base/' . $item['id'] . '/delete') ?>" method="post"
                                                onsubmit="return confirm('Hapus data knowledge base ini?')">
                                                <?= csrf_field() ?>
                                                <button class="btn btn-outline-danger btn-sm" type="submit">
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

                <?php if ($pager): ?>
                    <div class="mt-4">
                        <?= $pager->links('knowledge_base', 'default_full') ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?= $this->include('dashboard/layout/footer') ?>
