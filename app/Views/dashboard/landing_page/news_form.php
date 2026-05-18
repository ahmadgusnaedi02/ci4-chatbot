<?php $item = $item ?? []; ?>

<div class="row">
    <div class="col-md-8 mb-3">
        <label class="form-label">Judul Berita</label>
        <input class="form-control" name="title" type="text" required
            value="<?= esc($item['title'] ?? '') ?>">
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label">Foto Berita</label>
        <input class="form-control" name="image" type="file" accept="image/png,image/jpeg,image/webp">
        <small class="text-muted d-block mt-1">Kosongkan jika ingin memakai foto default sekolah.</small>
    </div>
</div>

<div class="mb-3">
    <label class="form-label">Ringkasan</label>
    <textarea class="form-control" name="excerpt" rows="4" required><?= esc($item['excerpt'] ?? '') ?></textarea>
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label">Status</label>
        <?php $status = $item['status'] ?? 'published'; ?>
        <select class="form-control" name="status">
            <option value="published" <?= $status === 'published' ? 'selected' : '' ?>>Published</option>
            <option value="draft" <?= $status === 'draft' ? 'selected' : '' ?>>Draft</option>
        </select>
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label">Urutan</label>
        <input class="form-control" name="sort_order" type="number"
            value="<?= esc((string) ($item['sort_order'] ?? 0)) ?>">
    </div>
</div>
