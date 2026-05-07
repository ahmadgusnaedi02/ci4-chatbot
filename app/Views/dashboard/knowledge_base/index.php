<?= $this->include('dashboard/layout/header') ?>
<?= $this->include('dashboard/layout/navbar') ?>
<?= $this->include('dashboard/layout/sidebar') ?>

<div class="main-panel">
    <div class="content-wrapper admin-content">
        <div class="admin-page-head">
            <div>
                <p class="admin-eyebrow mb-2">Dataset Chatbot</p>
                <h2 class="admin-page-title mb-2">Intent Dataset</h2>
                <p class="admin-page-subtitle mb-0">Kelola intent, response, training phrase, dan keyword dari tabel dataset yang terpisah.</p>
            </div>
            <a class="btn admin-primary-btn mt-3 mt-md-0" href="<?= site_url('dashboard/knowledge-base/create') ?>">
                <i class="mdi mdi-plus me-1"></i> Tambah Intent
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
                <div class="row mb-4">
                    <div class="col-md-4 mb-3 mb-md-0">
                        <div class="admin-dataset-note">
                            <strong>Intent</strong>
                            <span>Disimpan di tabel <code>chatbot_intents</code>.</span>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3 mb-md-0">
                        <div class="admin-dataset-note">
                            <strong>Training Phrase</strong>
                            <span>Disimpan di tabel <code>chatbot_training_phrases</code>.</span>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="admin-dataset-note">
                            <strong>Keyword</strong>
                            <span>Disimpan di tabel <code>chatbot_keywords</code>.</span>
                        </div>
                    </div>
                </div>

                <form class="row g-3 align-items-end mb-4" action="<?= site_url('dashboard/knowledge-base') ?>" method="get">
                    <div class="col-lg-6">
                        <label class="form-label" for="q">Cari Data</label>
                        <input class="form-control" id="q" name="q" type="search" value="<?= esc($keyword) ?>"
                            placeholder="Cari intent, training phrase, keyword, atau response">
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
                                <th>Intent</th>
                                <th>Training Phrase</th>
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
                                    <td class="text-center text-muted py-5" colspan="7">Belum ada intent dataset.</td>
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
                                        <span class="admin-code-chip"><?= esc($item['name']) ?></span>
                                        <small class="text-muted d-block mt-2">Source: <?= esc($item['source'] ?? '-') ?></small>
                                    </td>
                                    <td class="admin-kb-clamp"><?= nl2br(esc($item['training_phrases_text'] ?? '')) ?></td>
                                    <td class="admin-kb-clamp"><?= esc($item['keywords_text'] ?? '') ?></td>
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
                                                onsubmit="return confirm('Hapus intent dataset ini?')">
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

                <?php if (!empty($pagerLinks)): ?>
                    <div class="mt-4">
                        <?= $pagerLinks ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?= $this->include('dashboard/layout/footer') ?>
