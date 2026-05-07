<?= $this->include('dashboard/layout/header') ?>
<?= $this->include('dashboard/layout/navbar') ?>
<?= $this->include('dashboard/layout/sidebar') ?>

<div class="main-panel">
    <div class="content-wrapper admin-content">
        <div class="admin-page-head">
            <div>
                <p class="admin-eyebrow mb-2">Dataset Chatbot</p>
                <h2 class="admin-page-title mb-2">Keywords</h2>
                <p class="admin-page-subtitle mb-0">Tambah kata penting, lalu hubungkan ke intent yang sesuai.</p>
            </div>
        </div>

        <?php if (session('success')): ?>
            <div class="alert alert-success"><?= esc(session('success')) ?></div>
        <?php endif; ?>
        <?php if (session('error')): ?>
            <div class="alert alert-danger"><?= esc(session('error')) ?></div>
        <?php endif; ?>

        <div class="card admin-card mb-4">
            <div class="card-body">
                <form class="row g-3 align-items-end" action="<?= site_url('dashboard/keywords') ?>" method="post">
                    <?= csrf_field() ?>
                    <div class="col-lg-4">
                        <label class="form-label" for="intent_id">Intent</label>
                        <select class="form-control" id="intent_id" name="intent_id" required>
                            <option value="">Pilih intent</option>
                            <?php foreach ($intents as $intent): ?>
                                <option value="<?= esc((string) $intent['id']) ?>"><?= esc($intent['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-lg-6">
                        <label class="form-label" for="keyword">Keyword</label>
                        <input class="form-control" id="keyword" name="keyword" type="text" placeholder="contoh: syarat" required>
                    </div>
                    <div class="col-lg-2">
                        <button class="btn admin-primary-btn w-100" type="submit">
                            <i class="mdi mdi-plus me-1"></i> Tambah
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card admin-card">
            <div class="card-body">
                <form class="row g-3 align-items-end mb-4" action="<?= site_url('dashboard/keywords') ?>" method="get">
                    <div class="col-lg-5">
                        <label class="form-label" for="q">Cari</label>
                        <input class="form-control" id="q" name="q" type="search" value="<?= esc($keyword) ?>" placeholder="Cari keyword atau intent">
                    </div>
                    <div class="col-lg-4">
                        <label class="form-label" for="filter_intent_id">Intent</label>
                        <select class="form-control" id="filter_intent_id" name="intent_id">
                            <option value="">Semua intent</option>
                            <?php foreach ($intents as $intent): ?>
                                <option value="<?= esc((string) $intent['id']) ?>" <?= (int) $intent['id'] === (int) $intentId ? 'selected' : '' ?>>
                                    <?= esc($intent['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-lg-3 d-flex gap-2">
                        <button class="btn admin-primary-btn flex-fill" type="submit">
                            <i class="mdi mdi-magnify me-1"></i> Filter
                        </button>
                        <a class="btn btn-outline-secondary" href="<?= site_url('dashboard/keywords') ?>">Reset</a>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table admin-kb-table admin-data-table admin-keywords-table align-middle">
                        <thead>
                            <tr>
                                <th>Intent</th>
                                <th>Keyword</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items as $item): ?>
                                <tr>
                                    <td>
                                        <form id="keyword-update-<?= esc((string) $item['id']) ?>" action="<?= site_url('dashboard/keywords/' . $item['id']) ?>" method="post"></form>
                                        <input type="hidden" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>" form="keyword-update-<?= esc((string) $item['id']) ?>">
                                        <select class="form-control" name="intent_id" form="keyword-update-<?= esc((string) $item['id']) ?>" required>
                                            <?php foreach ($intents as $intent): ?>
                                                <option value="<?= esc((string) $intent['id']) ?>" <?= (int) $intent['id'] === (int) $item['intent_id'] ? 'selected' : '' ?>>
                                                    <?= esc($intent['name']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </td>
                                    <td>
                                        <input class="form-control" name="keyword" type="text" value="<?= esc($item['keyword']) ?>" form="keyword-update-<?= esc((string) $item['id']) ?>" required>
                                    </td>
                                    <td>
                                        <div class="d-flex gap-2">
                                            <button class="btn btn-outline-primary btn-sm" form="keyword-update-<?= esc((string) $item['id']) ?>" type="submit">
                                                <i class="mdi mdi-content-save"></i>
                                            </button>
                                            <form action="<?= site_url('dashboard/keywords/' . $item['id'] . '/delete') ?>" method="post" onsubmit="return confirm('Hapus keyword ini?')">
                                                <?= csrf_field() ?>
                                                <button class="btn btn-outline-danger btn-sm" type="submit">
                                                    <i class="mdi mdi-delete"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (empty($items)): ?>
                                <tr>
                                    <td class="text-center text-muted py-5" colspan="3">Belum ada keyword.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <?= $this->include('dashboard/layout/footer') ?>
