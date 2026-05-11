<?= $this->include('layouts/header'); ?>
<?= $this->include('layouts/navbar'); ?>

<main class="school-home">
    <section class="school-hero" id="home">
        <div class="school-hero__media" aria-hidden="true">
            <img src="https://images.unsplash.com/photo-1580582932707-520aed937b7b?auto=format&fit=crop&w=1600&q=85"
                alt="">
        </div>
        <div class="school-hero__blue" aria-hidden="true"></div>
        <div class="container school-hero__content">
            <div>
                <h1>Yuk, Belajar Bersama Kita di SMP Plus Fajar Sentosa!</h1>
                <p>
                    Lingkungan belajar yang hangat, disiplin, dan aktif membimbing siswa untuk tumbuh percaya diri,
                    berprestasi, serta berakhlak baik.
                </p>
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
                    <div class="section-kicker">Tentang Sekolah</div>
                    <h2 class="section-title">Sekolah yang menyiapkan siswa untuk masa depan.</h2>
                </div>
                <div class="col-lg-6">
                    <p class="section-copy">
                        SMPS Plus Fajar Sentosa menghadirkan pembelajaran akademik, pembinaan karakter, dan kegiatan
                        pengembangan minat dalam suasana sekolah yang aman serta terarah.
                    </p>
                    <div class="stat-row">
                        <div class="stat-item">
                            <span class="stat-number">25+</span>
                            <span class="stat-label">Guru & pembimbing</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-number">15+</span>
                            <span class="stat-label">Kegiatan siswa</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-number">A</span>
                            <span class="stat-label">Akreditasi sekolah</span>
                        </div>
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
                    <h2 class="section-title">Belajar lebih hidup, terukur, dan menyenangkan.</h2>
                </div>
            </div>
            <div class="row g-4">
                <div class="col-md-4">
                    <article class="program-card">
                        <span class="program-icon"><i class="fa-solid fa-chalkboard-user"></i></span>
                        <h3>Guru Profesional</h3>
                        <p>Pendampingan belajar dengan pendekatan personal dan evaluasi perkembangan siswa.</p>
                    </article>
                </div>
                <div class="col-md-4">
                    <article class="program-card">
                        <span class="program-icon"><i class="fa-solid fa-computer"></i></span>
                        <h3>Literasi Digital</h3>
                        <p>Pengenalan teknologi, laboratorium komputer, dan kebiasaan belajar berbasis proyek.</p>
                    </article>
                </div>
                <div class="col-md-4">
                    <article class="program-card">
                        <span class="program-icon"><i class="fa-solid fa-trophy"></i></span>
                        <h3>Prestasi & Karakter</h3>
                        <p>Ekstrakurikuler, pembiasaan ibadah, disiplin, dan ruang tampil untuk potensi siswa.</p>
                    </article>
                </div>
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
            <div class="row g-4">
                <div class="col-md-4">
                    <article class="news-card">
                        <h3>Pembukaan SPMB Tahun Ajaran Baru</h3>
                        <p>Informasi pendaftaran, jadwal seleksi, dan persyaratan calon peserta didik baru.</p>
                    </article>
                </div>
                <div class="col-md-4">
                    <article class="news-card">
                        <h3>Kegiatan Projek Profil Pelajar</h3>
                        <p>Siswa belajar berkolaborasi melalui karya, presentasi, dan kegiatan sosial.</p>
                    </article>
                </div>
                <div class="col-md-4">
                    <article class="news-card">
                        <h3>Prestasi Akademik dan Nonakademik</h3>
                        <p>Apresiasi untuk siswa yang aktif berkembang di kelas, lomba, dan organisasi.</p>
                    </article>
                </div>
            </div>
        </div>
    </section>

    <section class="section-pad" id="SPMB">
        <div class="container">
            <div class="SPMB-panel">
                <div class="row align-items-center g-4">
                    <div class="col-lg-8">
                        <div class="section-kicker text-warning">SPMB</div>
                        <h2 class="fw-bold mb-3">Pendaftaran siswa baru sudah bisa disiapkan.</h2>
                        <p class="mb-0">
                            Tanyakan jadwal, syarat, biaya, dan alur pendaftaran melalui chatbot di pojok kanan bawah.
                        </p>
                    </div>
                    <div class="col-lg-4 text-lg-end position-relative">
                        <a href="#kontak" class="btn btn-school-primary">Hubungi Sekolah</a>
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
                    <h2 class="section-title">Kami siap membantu informasi sekolah.</h2>
                </div>
                <div class="col-lg-6">
                    <ul class="contact-list">
                        <li><i class="fa-solid fa-location-dot"></i><span>Jl. Contoh No. 10, Kediri</span></li>
                        <li><i class="fa-solid fa-phone"></i><span>0812-3456-7890</span></li>
                        <li><i class="fa-solid fa-envelope"></i><span>info@smpsplusfajarsentosa.sch.id</span></li>
                    </ul>
                </div>
            </div>
        </div>
    </section>
</main>

<?= $this->include('layouts/chatbot') ?>

<?= $this->include('layouts/footer'); ?>