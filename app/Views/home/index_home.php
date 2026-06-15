<?= $this->include('layouts/header'); ?>
<?= $this->include('layouts/navbar'); ?>

<?php
    $settings = $landingSettings ?? [];
    $programs = $landingPrograms ?? [];
    $staffItems = $landingStaff ?? [];
    $newsItems = $landingNews ?? [];
    $assetUrl = static function (?string $path): string {
        $path = trim((string) $path);

        if ($path === '') {
            return '';
        }

        return str_starts_with($path, 'http://') || str_starts_with($path, 'https://')
            ? $path
            : base_url($path);
    };
?>

<main class="school-home">
    <section class="school-hero" id="home">
        <div class="school-hero__media" aria-hidden="true">
            <img src="<?= esc($assetUrl($settings['hero_image_url'] ?? ''), 'attr') ?>" alt="">
        </div>
        <div class="school-hero__blue" aria-hidden="true"></div>
        <div class="container school-hero__content">
            <div>
                <h1><?= esc($settings['hero_title'] ?? '') ?></h1>
                <p><?= esc($settings['hero_subtitle'] ?? '') ?></p>
                <div class="hero-actions">
                    <a href="#SPMB" class="btn btn-school-primary">Daftar Sekarang</a>
                    <a href="#profil" class="btn btn-school-secondary">Lihat Profil Sekolah</a>
                </div>
            </div>
        </div>
    </section>
    <div class="school-wave" aria-hidden="true"></div>

    <section class="section-pad" id="profil">
        <div class="container">
            <div class="row align-items-center g-5">
                <div class="col-lg-6">
                    <div class="section-kicker"><?= esc($settings['about_kicker'] ?? 'Tentang Sekolah') ?></div>
                    <h2 class="section-title"><?= esc($settings['about_title'] ?? '') ?></h2>
                </div>
                <div class="col-lg-6">
                    <p class="section-copy"><?= esc($settings['about_text'] ?? '') ?></p>
                    <div class="stat-row">
                        <?php for ($index = 1; $index <= 3; $index++): ?>
                            <div class="stat-item">
                                <span class="stat-number"><?= esc($settings['stat_' . $index . '_number'] ?? '') ?></span>
                                <span class="stat-label"><?= esc($settings['stat_' . $index . '_label'] ?? '') ?></span>
                            </div>
                        <?php endfor; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="section-pad program-band" id="program">
        <div class="container">
            <div class="row mb-4">
                <div class="col-lg-7">
                    <div class="section-kicker">Program Unggulan</div>
                    <h2 class="section-title"><?= esc($settings['program_title'] ?? '') ?></h2>
                </div>
            </div>
            <div class="row g-4">
                <?php foreach ($programs as $program): ?>
                    <div class="col-md-4">
                        <article class="program-card">
                            <span class="program-icon"><i class="<?= esc($program['icon'] ?? 'fa-solid fa-star', 'attr') ?>"></i></span>
                            <h3><?= esc($program['title'] ?? '') ?></h3>
                            <p><?= esc($program['description'] ?? '') ?></p>
                        </article>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="section-pad" id="tenaga-pendidik">
        <div class="container">
            <div class="row align-items-end mb-4">
                <div class="col-lg-7">
                    <div class="section-kicker">Tenaga Pendidik</div>
                    <h2 class="section-title"><?= esc($settings['staff_title'] ?? '') ?></h2>
                </div>
                <div class="col-lg-5">
                    <p class="section-copy mb-0"><?= esc($settings['staff_subtitle'] ?? '') ?></p>
                </div>
            </div>
            <div class="school-scroll-wrap">
                <button class="school-scroll-btn" type="button" data-scroll-target="staffRail" data-scroll-direction="-1" aria-label="Geser tenaga pendidik ke kiri">
                    <i class="fa-solid fa-chevron-left"></i>
                </button>
                <button class="school-scroll-btn" type="button" data-scroll-target="staffRail" data-scroll-direction="1" aria-label="Geser tenaga pendidik ke kanan">
                    <i class="fa-solid fa-chevron-right"></i>
                </button>
            </div>
            <div class="school-card-rail" id="staffRail">
                <?php foreach ($staffItems as $staff): ?>
                    <div class="school-card-rail__item">
                        <article class="staff-card">
                            <img class="staff-card__photo" src="<?= esc($assetUrl($staff['photo_url'] ?? ''), 'attr') ?>" alt="<?= esc($staff['name'] ?? '', 'attr') ?>">
                            <div class="staff-card__body">
                                <span><?= esc($staff['position'] ?? '') ?></span>
                                <h3><?= esc($staff['name'] ?? '') ?></h3>
                                <p><?= esc($staff['bio'] ?? '') ?></p>
                            </div>
                        </article>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="section-pad" id="artikel">
        <div class="container">
            <div class="row align-items-end mb-4">
                <div class="col-lg-7">
                    <div class="section-kicker">Berita & Artikel</div>
                    <h2 class="section-title">Kabar terbaru dari lingkungan sekolah.</h2>
                </div>
            </div>
            <div class="school-scroll-wrap">
                <button class="school-scroll-btn" type="button" data-scroll-target="newsRail" data-scroll-direction="-1" aria-label="Geser artikel ke kiri">
                    <i class="fa-solid fa-chevron-left"></i>
                </button>
                <button class="school-scroll-btn" type="button" data-scroll-target="newsRail" data-scroll-direction="1" aria-label="Geser artikel ke kanan">
                    <i class="fa-solid fa-chevron-right"></i>
                </button>
            </div>
            <div class="school-card-rail" id="newsRail">
                <?php foreach ($newsItems as $news): ?>
                    <div class="school-card-rail__item">
                        <article class="news-card">
                            <img class="news-card__image" src="<?= esc($assetUrl($news['image_url'] ?? ''), 'attr') ?>" alt="<?= esc($news['title'] ?? '', 'attr') ?>">
                            <h3><?= esc($news['title'] ?? '') ?></h3>
                            <p><?= esc($news['excerpt'] ?? '') ?></p>
                        </article>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="section-pad" id="SPMB">
        <div class="container">
            <div class="ppdb-panel">
                <div class="row align-items-center g-4">
                    <div class="col-lg-8">
                        <div class="section-kicker text-warning">SPMB</div>
                        <h2 class="fw-bold mb-3"><?= esc($settings['spmb_title'] ?? '') ?></h2>
                        <p class="mb-0"><?= esc($settings['spmb_text'] ?? '') ?></p>
                    </div>
                    <div class="col-lg-4 text-lg-end position-relative ppdb-panel__action">
                        <button class="btn btn-school-primary" type="button" data-open-chatbot>
                            Tanya Chatbot
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="section-pad pt-0" id="kontak">
        <div class="container">
            <div class="row g-5">
                <div class="col-lg-6">
                    <div class="section-kicker">Kontak</div>
                    <h2 class="section-title"><?= esc($settings['contact_title'] ?? '') ?></h2>
                </div>
                <div class="col-lg-6">
                    <ul class="contact-list">
                        <li><i class="fa-solid fa-location-dot"></i><span><?= esc($settings['contact_address'] ?? '') ?></span></li>
                        <li><i class="fa-solid fa-phone"></i><span><?= esc($settings['contact_phone'] ?? '') ?></span></li>
                        <li><i class="fa-solid fa-envelope"></i><span><?= esc($settings['contact_email'] ?? '') ?></span></li>
                    </ul>
                </div>
            </div>
        </div>
    </section>
</main>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('[data-scroll-target]').forEach(function (button) {
            button.addEventListener('click', function () {
                const rail = document.getElementById(button.dataset.scrollTarget);
                const direction = Number(button.dataset.scrollDirection || 1);

                if (!rail) {
                    return;
                }

                rail.scrollBy({
                    left: direction * rail.clientWidth,
                    behavior: 'smooth'
                });
            });
        });
    });
</script>

<?= $this->include('layouts/chatbot') ?>

<?= $this->include('layouts/footer'); ?>
