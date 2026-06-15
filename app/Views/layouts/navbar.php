<?php
    $settings = $landingSettings ?? [];
    $logoPath = $settings['logo_url'] ?? 'assets/images/logo-yapas.png';
    $logoUrl = str_starts_with($logoPath, 'http://') || str_starts_with($logoPath, 'https://')
        ? $logoPath
        : base_url($logoPath);
?>
<nav class="navbar navbar-expand-lg navbar-light school-navbar">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center gap-3" href="#">
            <img class="school-logo" src="<?= esc($logoUrl, 'attr') ?>" alt="<?= esc($settings['site_name'] ?? 'Logo Sekolah', 'attr') ?>">
            <span>
                <?= esc($settings['brand_line_1'] ?? 'SMPS Plus') ?><br>
                <?= esc($settings['brand_line_2'] ?? 'Fajar Sentosa') ?>
            </span>
        </a>
        <button class="navbar-toggler school-navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
            aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse school-navbar-menu" id="navbarNav">
            <ul class="navbar-nav align-items-lg-center ms-auto">
                <li class="nav-item"><a class="nav-link active" href="#home">Home</a></li>
                <li class="nav-item"><a class="nav-link" href="#profil">Tentang Sekolah</a></li>
                <li class="nav-item"><a class="nav-link" href="#program">Program</a></li>
                <li class="nav-item"><a class="nav-link" href="#tenaga-pendidik">Tenaga Pendidik</a></li>
                <li class="nav-item"><a class="nav-link" href="#artikel">Berita & Artikel</a></li>
                <li class="nav-item"><a class="nav-link" href="#kontak">Kontak</a></li>
                <li class="nav-item ms-lg-3 mt-3 mt-lg-0">
                    <a class="btn btn-ppdb" href="<?= site_url('admin/login') ?>">Login As Administrator</a>
                </li>
            </ul>
        </div>
    </div>
</nav>
