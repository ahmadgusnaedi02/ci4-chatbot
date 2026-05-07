<?= $this->include('dashboard/layout/header') ?>
<?= $this->include('dashboard/layout/navbar') ?>
<?= $this->include('dashboard/layout/sidebar') ?>

<?php
    $isEdit = $mode === 'edit';
    $action = $isEdit
        ? site_url('dashboard/intents/' . $item['id'])
        : site_url('dashboard/intents');
?>

<div class="main-panel">
    <div class="content-wrapper admin-content">
        <div class="admin-page-head">
            <div>
                <p class="admin-eyebrow mb-2">Dataset Chatbot</p>
                <h2 class="admin-page-title mb-2"><?= $isEdit ? 'Edit Intent' : 'Tambah Intent' ?></h2>
                <p class="admin-page-subtitle mb-0">Intent adalah kelas Naive Bayes dan tempat response utama disimpan.</p>
            </div>
            <a class="btn btn-outline-secondary mt-3 mt-md-0" href="<?= site_url('dashboard/intents') ?>">
                <i class="mdi mdi-arrow-left me-1"></i> Kembali
            </a>
        </div>

        <?php if (session('error')): ?>
            <div class="alert alert-danger"><?= esc(session('error')) ?></div>
        <?php endif; ?>

        <div class="card admin-card">
            <div class="card-body">
                <form action="<?= $action ?>" method="post">
                    <?= csrf_field() ?>

                    <div class="row">
                        <div class="col-lg-8">
                            <div class="mb-3">
                                <label class="form-label" for="response">Response</label>
                                <textarea class="form-control" id="response" name="response" rows="8" required><?= esc(old('response', $item['response'] ?? '')) ?></textarea>
                            </div>
                        </div>

                        <div class="col-lg-4">
                            <div class="mb-3">
                                <label class="form-label" for="name">Intent</label>
                                <input class="form-control" id="name" name="name" type="text" required
                                    value="<?= esc(old('name', $item['name'] ?? '')) ?>"
                                    placeholder="contoh: syarat_ppdb">
                            </div>

                            <div class="mb-3">
                                <label class="form-label" for="status">Status</label>
                                <?php $currentStatus = old('status', $item['status'] ?? 'active'); ?>
                                <select class="form-control" id="status" name="status">
                                    <option value="active" <?= $currentStatus === 'active' ? 'selected' : '' ?>>Active</option>
                                    <option value="inactive" <?= $currentStatus === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                                    <option value="draft" <?= $currentStatus === 'draft' ? 'selected' : '' ?>>Draft</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label" for="priority">Priority</label>
                                <input class="form-control" id="priority" name="priority" type="number"
                                    value="<?= esc((string) old('priority', $item['priority'] ?? 0)) ?>">
                            </div>

                            <div class="mb-4">
                                <label class="form-label" for="source">Source</label>
                                <input class="form-control" id="source" name="source" type="text"
                                    value="<?= esc(old('source', $item['source'] ?? 'manual')) ?>">
                            </div>

                            <button class="btn admin-primary-btn w-100" type="submit">
                                <i class="mdi mdi-content-save me-1"></i> Simpan
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?= $this->include('dashboard/layout/footer') ?>
