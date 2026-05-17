<?= $this->include('dashboard/layout/header') ?>
<?= $this->include('dashboard/layout/navbar') ?>
<?= $this->include('dashboard/layout/sidebar') ?>

<div class="main-panel">
    <div class="content-wrapper admin-content">
        <div class="admin-page-head">
            <div>
                <p class="admin-eyebrow mb-2">Dataset Chatbot</p>
                <h2 class="admin-page-title mb-2">Training Phrases</h2>
                <p class="admin-page-subtitle mb-0">Tambah contoh kalimat pertanyaan, lalu hubungkan ke intent yang sesuai.</p>
            </div>
        </div>

        <?php if (session('success')): ?>
            <div class="alert alert-success"><?= esc(session('success')) ?></div>
        <?php endif; ?>
        <?php if (session('error')): ?>
            <div class="alert alert-danger"><?= esc(session('error')) ?></div>
        <?php endif; ?>

        <?php
            $trainingSummary = $trainingSummary ?? [];
            $totalTraining = array_sum(array_map(static fn (array $item): int => (int) ($item['training_count'] ?? 0), $trainingSummary));
        ?>
        <div class="card admin-card training-summary-chart-card mb-4">
            <div class="card-body">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
                    <div>
                        <h4 class="card-title card-title-dash mb-1">Ringkasan Data Training</h4>
                        <p class="card-subtitle card-subtitle-dash mb-0"><?= esc((string) count($trainingSummary)) ?> intent terdaftar</p>
                    </div>
                    <div class="training-summary-total">
                        <span>Total</span>
                        <strong><?= esc((string) $totalTraining) ?></strong>
                    </div>
                </div>

                <?php if (empty($trainingSummary)): ?>
                    <div class="text-muted text-center py-4">Belum ada intent untuk diringkas.</div>
                <?php else: ?>
                    <div class="training-chart">
                        <?php foreach ($trainingSummary as $summary): ?>
                            <?php
                                $count = (int) ($summary['training_count'] ?? 0);
                                $percent = $totalTraining > 0 ? max(3, (int) round(($count / $totalTraining) * 100)) : 3;
                            ?>
                            <div class="training-chart-row">
                                <span class="training-chart-label"><?= esc($summary['name']) ?></span>
                                <div class="training-chart-track">
                                    <div class="training-chart-bar" style="width: <?= esc((string) $percent) ?>%"></div>
                                </div>
                                <strong><?= esc((string) $count) ?></strong>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="card admin-card mb-4">
            <div class="card-body">
                <form class="row g-3 align-items-end" action="<?= site_url('dashboard/training-phrases') ?>" method="post">
                    <?= csrf_field() ?>
                    <div class="col-lg-3">
                        <label class="form-label" for="intent_id">Intent</label>
                        <select class="form-control" id="intent_id" name="intent_id" required>
                            <option value="">Pilih intent</option>
                            <?php foreach ($intents as $intent): ?>
                                <option value="<?= esc((string) $intent['id']) ?>"><?= esc($intent['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-lg-6">
                        <label class="form-label" for="phrase">Training Phrase</label>
                        <input class="form-control" id="phrase" name="phrase" type="text" placeholder="contoh: apa saja syarat spmb" required>
                    </div>
                    <div class="col-lg-2">
                        <label class="form-label" for="source">Source</label>
                        <input class="form-control" id="source" name="source" type="text" value="manual">
                    </div>
                    <div class="col-lg-1">
                        <button class="btn admin-primary-btn w-100" type="submit">
                            <i class="mdi mdi-plus"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card admin-card">
            <div class="card-body">
                <form class="row g-3 align-items-end mb-4" action="<?= site_url('dashboard/training-phrases') ?>" method="get">
                    <div class="col-lg-5">
                        <label class="form-label" for="q">Cari</label>
                        <input class="form-control" id="q" name="q" type="search" value="<?= esc($keyword) ?>" placeholder="Cari phrase atau intent">
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
                        <a class="btn btn-outline-secondary" href="<?= site_url('dashboard/training-phrases') ?>">Reset</a>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table admin-kb-table admin-data-table admin-phrases-table align-middle">
                        <thead>
                            <tr>
                                <th>Intent</th>
                                <th>Training Phrase</th>
                                <th>Source</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items as $item): ?>
                                <tr>
                                    <td><span class="admin-code-chip"><?= esc($item['intent_name'] ?? '-') ?></span></td>
                                    <td>
                                        <div class="admin-response-preview"><?= esc($item['phrase']) ?></div>
                                    </td>
                                    <td><?= esc($item['source'] ?? 'manual') ?></td>
                                    <td>
                                        <div class="dropdown admin-row-actions">
                                            <button class="btn btn-outline-primary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                Aksi
                                            </button>
                                            <div class="dropdown-menu dropdown-menu-end">
                                                <button class="dropdown-item" type="button" data-bs-toggle="modal" data-bs-target="#phrase-edit-<?= esc((string) $item['id']) ?>">
                                                    <i class="mdi mdi-pencil me-2"></i>Edit
                                                </button>
                                                <form action="<?= site_url('dashboard/training-phrases/' . $item['id'] . '/delete') ?>" method="post" data-confirm="Hapus training phrase ini?">
                                                    <?= csrf_field() ?>
                                                    <button class="dropdown-item text-danger" type="submit">
                                                        <i class="mdi mdi-delete me-2"></i>Hapus
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (empty($items)): ?>
                                <tr>
                                    <td class="text-center text-muted py-5" colspan="4">Belum ada training phrase.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <?php foreach ($items as $item): ?>
                    <div class="modal fade" id="phrase-edit-<?= esc((string) $item['id']) ?>" tabindex="-1" aria-labelledby="phrase-edit-label-<?= esc((string) $item['id']) ?>" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <form class="modal-content admin-modal" action="<?= site_url('dashboard/training-phrases/' . $item['id']) ?>" method="post">
                                <?= csrf_field() ?>
                                <div class="modal-header">
                                    <h5 class="modal-title" id="phrase-edit-label-<?= esc((string) $item['id']) ?>">Edit Training Phrase</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="mb-3">
                                        <label class="form-label" for="phrase-intent-<?= esc((string) $item['id']) ?>">Intent</label>
                                        <select class="form-control" id="phrase-intent-<?= esc((string) $item['id']) ?>" name="intent_id" required>
                                            <?php foreach ($intents as $intent): ?>
                                                <option value="<?= esc((string) $intent['id']) ?>" <?= (int) $intent['id'] === (int) $item['intent_id'] ? 'selected' : '' ?>>
                                                    <?= esc($intent['name']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label" for="phrase-text-<?= esc((string) $item['id']) ?>">Training Phrase</label>
                                        <input class="form-control" id="phrase-text-<?= esc((string) $item['id']) ?>" name="phrase" type="text" value="<?= esc($item['phrase']) ?>" required>
                                    </div>
                                    <div class="mb-0">
                                        <label class="form-label" for="phrase-source-<?= esc((string) $item['id']) ?>">Source</label>
                                        <input class="form-control" id="phrase-source-<?= esc((string) $item['id']) ?>" name="source" type="text" value="<?= esc($item['source'] ?? 'manual') ?>">
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                                    <button class="btn admin-primary-btn" type="submit">
                                        <i class="mdi mdi-content-save me-1"></i> Simpan
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <style>
        .training-summary-chart-card .card-body {
            padding-bottom: 18px;
        }

        .training-summary-total {
            align-items: flex-end;
            display: flex;
            gap: 10px;
        }

        .training-summary-total span {
            color: #64748b;
            font-size: 13px;
            margin-bottom: 4px;
        }

        .training-summary-total strong {
            display: block;
            font-size: 34px;
            line-height: 1;
        }

        .training-chart {
            display: grid;
            gap: 10px;
            max-height: 240px;
            overflow-y: auto;
            padding-right: 4px;
        }

        .training-chart-row {
            align-items: center;
            display: grid;
            gap: 12px;
            grid-template-columns: minmax(120px, 180px) minmax(0, 1fr) 42px;
        }

        .training-chart-label {
            color: #334155;
            font-size: 13px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .training-chart-track {
            background: #edf6fb;
            border-radius: 999px;
            height: 10px;
            overflow: hidden;
        }

        .training-chart-bar {
            background: var(--admin-blue);
            border-radius: inherit;
            height: 100%;
        }

        .training-chart-row strong {
            color: #334155;
            font-size: 13px;
            text-align: right;
        }

        @media (max-width: 767px) {
            .training-chart-row {
                grid-template-columns: minmax(86px, 120px) minmax(0, 1fr) 36px;
            }
        }
    </style>

    <?= $this->include('dashboard/layout/footer') ?>
