<?= $this->include('dashboard/layout/header') ?>
<?= $this->include('dashboard/layout/navbar') ?>
<?= $this->include('dashboard/layout/sidebar') ?>

<div class="main-panel">
    <div class="content-wrapper admin-content">
        <div class="admin-page-head">
            <div>
                <p class="admin-eyebrow mb-2">Dataset Chatbot</p>
                <h2 class="admin-page-title mb-2">NLP Rules</h2>
                <p class="admin-page-subtitle mb-0">Kelola stopword, suffix, dan synonym yang dipakai preprocessing Naive Bayes.</p>
            </div>
            <a class="btn btn-outline-secondary mt-3 mt-md-0" href="<?= site_url('dashboard/knowledge-base') ?>">
                <i class="mdi mdi-database-search me-1"></i> Knowledge Base
            </a>
        </div>

        <?php if (session('success')): ?>
            <div class="alert alert-success"><?= esc(session('success')) ?></div>
        <?php endif; ?>
        <?php if (session('error')): ?>
            <div class="alert alert-danger"><?= esc(session('error')) ?></div>
        <?php endif; ?>

        <div class="nlp-stats mb-4">
            <div class="nlp-stat-card nlp-stat-stopword">
                <i class="mdi mdi-filter-remove-outline"></i>
                <span>Total Stopword</span>
                <strong><?= esc((string) count($stopwords ?? [])) ?></strong>
            </div>
            <div class="nlp-stat-card nlp-stat-suffix">
                <i class="mdi mdi-format-letter-case"></i>
                <span>Total Suffix</span>
                <strong><?= esc((string) count($suffixes ?? [])) ?></strong>
            </div>
            <div class="nlp-stat-card nlp-stat-synonym">
                <i class="mdi mdi-swap-horizontal"></i>
                <span>Total Synonym</span>
                <strong><?= esc((string) count($synonyms ?? [])) ?></strong>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-6 mb-4" id="stopwords">
                <div class="card admin-card h-100">
                    <div class="card-body">
                        <div class="admin-card-head">
                            <h4>Stopword</h4>
                            <p>Kata umum yang dibuang sebelum klasifikasi.</p>
                        </div>

                        <form class="row g-2 mb-4" action="<?= site_url('dashboard/nlp-rules/stopwords') ?>" method="post">
                            <?= csrf_field() ?>
                            <div class="col">
                                <input class="form-control" name="word" type="text" placeholder="contoh: untuk" required>
                            </div>
                            <div class="col-auto">
                                <button class="btn admin-primary-btn" type="submit">
                                    <i class="mdi mdi-plus"></i>
                                </button>
                            </div>
                        </form>

                        <div class="table-responsive">
                            <table class="table admin-kb-table admin-data-table align-middle">
                                <thead>
                                    <tr>
                                        <th>Word</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($stopwords as $row): ?>
                                        <tr>
                                            <td>
                                                <form id="stopword-update-<?= esc((string) $row['id']) ?>" action="<?= site_url('dashboard/nlp-rules/stopwords/' . $row['id']) ?>" method="post">
                                                    <?= csrf_field() ?>
                                                    <input class="form-control" name="word" type="text" value="<?= esc($row['word']) ?>" required>
                                                </form>
                                            </td>
                                            <td>
                                                <div class="d-flex gap-2">
                                                    <button class="btn btn-outline-primary btn-sm" form="stopword-update-<?= esc((string) $row['id']) ?>" type="submit">
                                                        <i class="mdi mdi-content-save"></i>
                                                    </button>
                                                    <form action="<?= site_url('dashboard/nlp-rules/stopwords/' . $row['id'] . '/delete') ?>" method="post" data-confirm="Hapus stopword ini?">
                                                        <?= csrf_field() ?>
                                                        <button class="btn btn-outline-danger btn-sm" type="submit">
                                                            <i class="mdi mdi-delete"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                    <?php if (empty($stopwords)): ?>
                                        <tr>
                                            <td class="text-muted text-center py-4" colspan="2">Belum ada stopword.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-6 mb-4" id="suffixes">
                <div class="card admin-card h-100">
                    <div class="card-body">
                        <div class="admin-card-head">
                            <h4>Suffix</h4>
                            <p>Akhiran kata yang dipotong saat normalisasi token.</p>
                        </div>

                        <form class="row g-2 mb-4" action="<?= site_url('dashboard/nlp-rules/suffixes') ?>" method="post">
                            <?= csrf_field() ?>
                            <div class="col">
                                <input class="form-control" name="suffix" type="text" placeholder="contoh: nya" required>
                            </div>
                            <div class="col-auto">
                                <button class="btn admin-primary-btn" type="submit">
                                    <i class="mdi mdi-plus"></i>
                                </button>
                            </div>
                        </form>

                        <div class="table-responsive">
                            <table class="table admin-kb-table admin-data-table align-middle">
                                <thead>
                                    <tr>
                                        <th>Suffix</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($suffixes as $row): ?>
                                        <tr>
                                            <td>
                                                <form id="suffix-update-<?= esc((string) $row['id']) ?>" action="<?= site_url('dashboard/nlp-rules/suffixes/' . $row['id']) ?>" method="post">
                                                    <?= csrf_field() ?>
                                                    <input class="form-control" name="suffix" type="text" value="<?= esc($row['suffix']) ?>" required>
                                                </form>
                                            </td>
                                            <td>
                                                <div class="d-flex gap-2">
                                                    <button class="btn btn-outline-primary btn-sm" form="suffix-update-<?= esc((string) $row['id']) ?>" type="submit">
                                                        <i class="mdi mdi-content-save"></i>
                                                    </button>
                                                    <form action="<?= site_url('dashboard/nlp-rules/suffixes/' . $row['id'] . '/delete') ?>" method="post" data-confirm="Hapus suffix ini?">
                                                        <?= csrf_field() ?>
                                                        <button class="btn btn-outline-danger btn-sm" type="submit">
                                                            <i class="mdi mdi-delete"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                    <?php if (empty($suffixes)): ?>
                                        <tr>
                                            <td class="text-muted text-center py-4" colspan="2">Belum ada suffix.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-6 mb-4" id="synonyms">
                <div class="card admin-card">
                    <div class="card-body">
                        <div class="admin-card-head">
                            <h4>Synonym</h4>
                            <p>Pemetaan kata user ke bentuk kata standar sebelum dihitung Naive Bayes.</p>
                        </div>

                        <form class="row g-2 mb-4" action="<?= site_url('dashboard/nlp-rules/synonyms') ?>" method="post">
                            <?= csrf_field() ?>
                            <div class="col-md-5">
                                <input class="form-control" name="word" type="text" placeholder="kata asal, contoh: daftar" required>
                            </div>
                            <div class="col-md-5">
                                <input class="form-control" name="normalized_word" type="text" placeholder="kata normal, contoh: pendaftaran" required>
                            </div>
                            <div class="col-md-2">
                                <button class="btn admin-primary-btn w-100" type="submit">
                                    <i class="mdi mdi-plus me-1"></i> Tambah
                                </button>
                            </div>
                        </form>

                        <div class="table-responsive">
                            <table class="table admin-kb-table admin-data-table align-middle">
                                <thead>
                                    <tr>
                                        <th>Kata Asal</th>
                                        <th>Kata Normal</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($synonyms as $row): ?>
                                        <tr>
                                            <td>
                                                <form id="synonym-update-<?= esc((string) $row['id']) ?>" action="<?= site_url('dashboard/nlp-rules/synonyms/' . $row['id']) ?>" method="post"></form>
                                                <input type="hidden" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>" form="synonym-update-<?= esc((string) $row['id']) ?>">
                                                <input class="form-control" name="word" type="text" value="<?= esc($row['word']) ?>" form="synonym-update-<?= esc((string) $row['id']) ?>" required>
                                            </td>
                                            <td>
                                                <input class="form-control" name="normalized_word" type="text" value="<?= esc($row['normalized_word']) ?>" form="synonym-update-<?= esc((string) $row['id']) ?>" required>
                                            </td>
                                            <td>
                                                <div class="d-flex gap-2">
                                                    <button class="btn btn-outline-primary btn-sm" form="synonym-update-<?= esc((string) $row['id']) ?>" type="submit">
                                                        <i class="mdi mdi-content-save"></i>
                                                    </button>
                                                    <form action="<?= site_url('dashboard/nlp-rules/synonyms/' . $row['id'] . '/delete') ?>" method="post" data-confirm="Hapus synonym ini?">
                                                        <?= csrf_field() ?>
                                                        <button class="btn btn-outline-danger btn-sm" type="submit">
                                                            <i class="mdi mdi-delete"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                    <?php if (empty($synonyms)): ?>
                                        <tr>
                                            <td class="text-muted text-center py-4" colspan="3">Belum ada synonym.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .nlp-stats {
            display: grid;
            gap: 14px;
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }

        .nlp-stat-card {
            background: #fff;
            border: 1px solid #dbe7ef;
            border-radius: 8px;
            overflow: hidden;
            padding: 16px 18px;
            position: relative;
        }

        .nlp-stat-card::before {
            bottom: 0;
            content: "";
            left: 0;
            position: absolute;
            top: 0;
            width: 5px;
        }

        .nlp-stat-card i {
            align-items: center;
            border-radius: 8px;
            display: inline-flex;
            font-size: 23px;
            height: 42px;
            justify-content: center;
            margin-bottom: 12px;
            width: 42px;
        }

        .nlp-stat-card span {
            color: var(--admin-muted);
            display: block;
            font-size: 13px;
            margin-bottom: 8px;
        }

        .nlp-stat-card strong {
            color: var(--admin-ink);
            display: block;
            font-size: 30px;
            line-height: 1;
        }

        .nlp-stat-stopword {
            border-color: rgba(16, 79, 134, 0.18);
        }

        .nlp-stat-stopword::before,
        .nlp-stat-stopword i {
            background: var(--admin-blue);
            color: #fff;
        }

        .nlp-stat-suffix {
            border-color: rgba(245, 183, 25, 0.34);
        }

        .nlp-stat-suffix::before,
        .nlp-stat-suffix i {
            background: var(--admin-yellow);
            color: var(--admin-ink);
        }

        .nlp-stat-synonym {
            border-color: rgba(95, 158, 160, 0.3);
        }

        .nlp-stat-synonym::before,
        .nlp-stat-synonym i {
            background: var(--admin-teal);
            color: #fff;
        }

        @media (max-width: 767px) {
            .nlp-stats {
                grid-template-columns: 1fr;
            }
        }
    </style>

    <?= $this->include('dashboard/layout/footer') ?>
