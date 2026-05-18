<?php $item = $item ?? []; ?>

<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label">Nama</label>
        <input class="form-control" name="name" type="text" required
            value="<?= esc($item['name'] ?? '') ?>">
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label">Jabatan</label>
        <input class="form-control" name="position" type="text" required
            value="<?= esc($item['position'] ?? '') ?>"
            placeholder="contoh: Kepala Sekolah, Guru IPA, Tata Usaha">
    </div>
</div>

<div class="row">
    <div class="col-md-8 mb-3">
        <label class="form-label">Bio Singkat</label>
        <textarea class="form-control" name="bio" rows="4"><?= esc($item['bio'] ?? '') ?></textarea>
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label">Foto</label>
        <input class="form-control" name="photo" type="file" accept="image/png,image/jpeg,image/webp">
        <small class="text-muted d-block mt-1">Kosongkan jika ingin memakai foto default.</small>
    </div>
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label">Status</label>
        <?php $status = $item['status'] ?? 'active'; ?>
        <select class="form-control" name="status">
            <option value="active" <?= $status === 'active' ? 'selected' : '' ?>>Active</option>
            <option value="inactive" <?= $status === 'inactive' ? 'selected' : '' ?>>Inactive</option>
        </select>
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label">Urutan</label>
        <input class="form-control" name="sort_order" type="number"
            value="<?= esc((string) ($item['sort_order'] ?? 0)) ?>">
    </div>
</div>
