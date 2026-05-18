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
            <div class="d-flex flex-wrap gap-2 mt-3 mt-md-0">
                <form action="<?= site_url('dashboard/training-phrases/evaluate-naive-bayes') ?>" method="post">
                    <?= csrf_field() ?>
                    <button class="btn btn-outline-primary" type="submit">
                        <i class="mdi mdi-chart-line me-1"></i> Uji Naive Bayes
                    </button>
                </form>
                <form action="<?= site_url('dashboard/training-phrases/retrain') ?>" method="post">
                    <?= csrf_field() ?>
                    <button class="btn admin-primary-btn" type="submit">
                        <i class="mdi mdi-refresh me-1"></i> Training Ulang
                    </button>
                </form>
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
            $vectorizerStatus = $vectorizerStatus ?? [];
            $naiveBayesEvaluation = $naiveBayesEvaluation ?? null;
            $vectorStats = $vectorizerStatus['stats'] ?? [];
            $nbSummary = $naiveBayesEvaluation['summary'] ?? [];
            $perClass = $naiveBayesEvaluation['per_class'] ?? [];
            $confusionMatrix = $naiveBayesEvaluation['confusion_matrix'] ?? [];
            $confusionLabels = array_keys($confusionMatrix);
            $totalTraining = array_sum(array_map(static fn (array $item): int => (int) ($item['training_count'] ?? 0), $trainingSummary));
            $formatPercent = static fn ($value): string => number_format(((float) $value) * 100, 2) . '%';
        ?>
        <?php if ($naiveBayesEvaluation): ?>
            <div class="nb-result-strip mb-4">
                <div class="nb-result-main">
                    <div class="nb-result-icon">
                        <i class="mdi mdi-chart-line"></i>
                    </div>
                    <div>
                        <span class="nb-result-label">Hasil uji Naive Bayes terakhir</span>
                        <div class="nb-result-copy">
                            <strong><?= esc($formatPercent($nbSummary['accuracy'] ?? 0)) ?></strong>
                            <span>akurasi dari <?= esc((string) ($nbSummary['test_samples'] ?? 0)) ?> data uji</span>
                        </div>
                    </div>
                </div>
                <div class="nb-result-actions">
                    <button class="btn btn-sm nb-outline-btn" type="button" data-bs-toggle="modal" data-bs-target="#naive-bayes-result-modal">
                        <i class="mdi mdi-eye-outline me-1"></i> Lihat Detail
                    </button>
                    <a class="btn btn-sm nb-blue-btn" href="<?= site_url('dashboard/training-phrases/naive-bayes-pdf') ?>">
                        <i class="mdi mdi-file-pdf-box me-1"></i> Simpan PDF
                    </a>
                    <a class="btn btn-sm nb-excel-btn" href="<?= site_url('dashboard/training-phrases/naive-bayes-excel') ?>">
                        <i class="mdi mdi-file-excel-box me-1"></i> Simpan Excel
                    </a>
                </div>
            </div>
        <?php endif; ?>

        <div class="card admin-card training-summary-chart-card mb-4">
            <div class="card-body">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
                    <div>
                        <h4 class="card-title card-title-dash mb-1">Ringkasan Data Training</h4>
                        <p class="card-subtitle card-subtitle-dash mb-0">
                            <?= esc((string) count($trainingSummary)) ?> intent terdaftar
                            <?php if (!empty($vectorizerStatus['trained_at'])): ?>
                                - CountVectorizer: <?= esc($vectorizerStatus['trained_at']) ?>
                            <?php endif; ?>
                        </p>
                    </div>
                    <div class="training-summary-metrics">
                        <div class="training-summary-total">
                            <span>Phrase</span>
                            <strong><?= esc((string) $totalTraining) ?></strong>
                        </div>
                        <div class="training-summary-total">
                            <span>Vocabulary</span>
                            <strong><?= esc((string) ($vectorStats['vocabulary_size'] ?? 0)) ?></strong>
                        </div>
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

    <?php if ($naiveBayesEvaluation): ?>
        <div class="modal fade" id="naive-bayes-result-modal" tabindex="-1" aria-labelledby="naive-bayes-result-label" aria-hidden="true">
            <div class="modal-dialog modal-xl modal-dialog-scrollable">
                <div class="modal-content admin-modal">
                    <div class="modal-header">
                        <div>
                            <h5 class="modal-title" id="naive-bayes-result-label">Hasil Pengujian Naive Bayes</h5>
                            <p class="text-muted mb-0 small">
                                Hold out 80% data latih / 20% data uji - <?= esc($naiveBayesEvaluation['evaluated_at'] ?? '-') ?>
                            </p>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                    </div>
                    <div class="modal-body">
                        <div class="nb-metric-grid mb-4">
                            <div class="nb-metric">
                                <span>Akurasi</span>
                                <strong><?= esc($formatPercent($nbSummary['accuracy'] ?? 0)) ?></strong>
                            </div>
                            <div class="nb-metric">
                                <span>Data Latih</span>
                                <strong><?= esc((string) ($nbSummary['train_samples'] ?? 0)) ?></strong>
                            </div>
                            <div class="nb-metric">
                                <span>Data Uji</span>
                                <strong><?= esc((string) ($nbSummary['test_samples'] ?? 0)) ?></strong>
                            </div>
                            <div class="nb-metric">
                                <span>Macro Precision</span>
                                <strong><?= esc($formatPercent($nbSummary['macro_precision'] ?? 0)) ?></strong>
                            </div>
                            <div class="nb-metric">
                                <span>Macro Recall</span>
                                <strong><?= esc($formatPercent($nbSummary['macro_recall'] ?? 0)) ?></strong>
                            </div>
                            <div class="nb-metric">
                                <span>Macro F1</span>
                                <strong><?= esc($formatPercent($nbSummary['macro_f1'] ?? 0)) ?></strong>
                            </div>
                            <div class="nb-metric">
                                <span>Weighted F1</span>
                                <strong><?= esc($formatPercent($nbSummary['weighted_f1'] ?? 0)) ?></strong>
                            </div>
                        </div>

                        <h5 class="mb-3">Metrik Per Intent</h5>
                        <div class="table-responsive mb-4">
                            <table class="table admin-kb-table align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Intent</th>
                                        <th>Support</th>
                                        <th>Precision</th>
                                        <th>Recall</th>
                                        <th>F1-score</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($perClass as $intentName => $metrics): ?>
                                        <tr>
                                            <td><span class="admin-code-chip"><?= esc($intentName) ?></span></td>
                                            <td><?= esc((string) ($metrics['support'] ?? 0)) ?></td>
                                            <td><?= esc($formatPercent($metrics['precision'] ?? 0)) ?></td>
                                            <td><?= esc($formatPercent($metrics['recall'] ?? 0)) ?></td>
                                            <td><?= esc($formatPercent($metrics['f1'] ?? 0)) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                    <?php if (empty($perClass)): ?>
                                        <tr>
                                            <td class="text-center text-muted py-4" colspan="5">Belum ada cukup data untuk diuji.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>

                        <?php if (!empty($confusionMatrix)): ?>
                            <h5 class="mb-3">Confusion Matrix</h5>
                            <div class="table-responsive">
                                <table class="table admin-kb-table align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th>Actual \ Predicted</th>
                                            <?php foreach ($confusionLabels as $label): ?>
                                                <th><?= esc($label) ?></th>
                                            <?php endforeach; ?>
                                            <th>Unknown</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($confusionMatrix as $actual => $predictions): ?>
                                            <tr>
                                                <td><span class="admin-code-chip"><?= esc($actual) ?></span></td>
                                                <?php foreach ($confusionLabels as $label): ?>
                                                    <td><?= esc((string) ($predictions[$label] ?? 0)) ?></td>
                                                <?php endforeach; ?>
                                                <td><?= esc((string) ($predictions['__unknown__'] ?? 0)) ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="modal-footer">
                        <a class="btn admin-primary-btn" href="<?= site_url('dashboard/training-phrases/naive-bayes-pdf') ?>">
                            <i class="mdi mdi-file-pdf-box me-1"></i> Simpan PDF
                        </a>
                        <a class="btn nb-excel-btn" href="<?= site_url('dashboard/training-phrases/naive-bayes-excel') ?>">
                            <i class="mdi mdi-file-excel-box me-1"></i> Simpan Excel
                        </a>
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Tutup</button>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <style>
        .training-summary-chart-card .card-body {
            padding-bottom: 18px;
        }

        .training-summary-metrics {
            display: flex;
            gap: 18px;
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

        .nb-result-strip {
            align-items: center;
            background: #fff;
            border: 1px solid var(--admin-line);
            border-left: 5px solid var(--admin-blue);
            border-radius: 8px;
            box-shadow: 0 14px 34px rgba(13, 47, 79, 0.07);
            display: flex;
            gap: 18px;
            justify-content: space-between;
            padding: 16px 18px;
        }

        .nb-result-main {
            align-items: center;
            display: flex;
            gap: 14px;
            min-width: 0;
        }

        .nb-result-icon {
            align-items: center;
            background: var(--admin-blue);
            border-radius: 8px;
            color: #fff;
            display: inline-flex;
            flex: 0 0 42px;
            font-size: 22px;
            height: 42px;
            justify-content: center;
            width: 42px;
        }

        .nb-result-label {
            color: var(--admin-muted);
            display: block;
            font-size: 12px;
            font-weight: 800;
            margin-bottom: 3px;
        }

        .nb-result-copy {
            align-items: baseline;
            color: var(--admin-ink);
            display: flex;
            flex-wrap: wrap;
            gap: 7px;
        }

        .nb-result-copy strong {
            color: var(--admin-blue);
            font-size: 24px;
            line-height: 1;
        }

        .nb-result-copy span {
            color: var(--admin-muted);
            font-weight: 700;
        }

        .nb-result-actions {
            display: flex;
            flex: 0 0 auto;
            gap: 8px;
            justify-content: flex-end;
        }

        .nb-outline-btn {
            border-color: var(--admin-blue);
            color: var(--admin-blue);
            font-weight: 800;
        }

        .nb-outline-btn:hover,
        .nb-blue-btn {
            background: var(--admin-blue);
            border-color: var(--admin-blue);
            color: #fff;
            font-weight: 800;
        }

        .nb-blue-btn:hover {
            background: var(--admin-blue-dark);
            border-color: var(--admin-blue-dark);
            color: #fff;
        }

        .nb-excel-btn {
            background: var(--admin-teal);
            border-color: var(--admin-teal);
            color: #fff;
            font-weight: 800;
        }

        .nb-excel-btn:hover {
            background: #4d8587;
            border-color: #4d8587;
            color: #fff;
        }

        .nb-metric-grid {
            display: grid;
            gap: 12px;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        }

        .nb-metric {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 14px;
        }

        .nb-metric span {
            color: #64748b;
            display: block;
            font-size: 12px;
            margin-bottom: 6px;
        }

        .nb-metric strong {
            color: #0f172a;
            font-size: 18px;
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
            .nb-result-strip,
            .nb-result-actions {
                align-items: stretch;
                flex-direction: column;
            }

            .nb-result-actions .btn {
                width: 100%;
            }

            .training-chart-row {
                grid-template-columns: minmax(86px, 120px) minmax(0, 1fr) 36px;
            }
        }
    </style>

    <?php if ($naiveBayesEvaluation && session('showNaiveBayesModal')): ?>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                var modalElement = document.getElementById('naive-bayes-result-modal');

                if (modalElement && window.bootstrap) {
                    new bootstrap.Modal(modalElement).show();
                }
            });
        </script>
    <?php endif; ?>

    <?= $this->include('dashboard/layout/footer') ?>

