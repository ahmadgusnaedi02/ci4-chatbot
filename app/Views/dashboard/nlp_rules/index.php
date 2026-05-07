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
                                                    <form action="<?= site_url('dashboard/nlp-rules/stopwords/' . $row['id'] . '/delete') ?>" method="post" onsubmit="return confirm('Hapus stopword ini?')">
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
                                                    <form action="<?= site_url('dashboard/nlp-rules/suffixes/' . $row['id'] . '/delete') ?>" method="post" onsubmit="return confirm('Hapus suffix ini?')">
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

            <div class="col-12" id="synonyms">
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
                                                    <form action="<?= site_url('dashboard/nlp-rules/synonyms/' . $row['id'] . '/delete') ?>" method="post" onsubmit="return confirm('Hapus synonym ini?')">
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

    <?= $this->include('dashboard/layout/footer') ?>
