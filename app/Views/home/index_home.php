<?= $this->include('layouts/header'); ?>
<?= $this->include('layouts/navbar'); ?>

<!-- Hero Section -->
<section class="hero text-center">
    <div class="container">
        <h1 class="display-4">Selamat Datang di Website SMPS Plus Fajar Sentosa</h1>
        <p class="lead">Sekolah Unggulan dengan Fasilitas Modern dan Prestasi Gemilang</p>
    </div>
</section>

<!-- Features Section -->
<section class="py-5">
    <div class="container">
        <div class="row text-center">
            <div class="col-md-4 mb-4">
                <div class="feature-icon mb-3"><i class="fas fa-chalkboard-teacher"></i></div>
                <h5>Guru Profesional</h5>
                <p>Tenaga pengajar berpengalaman dan kompeten di bidangnya.</p>
            </div>
            <div class="col-md-4 mb-4">
                <div class="feature-icon mb-3"><i class="fas fa-laptop-code"></i></div>
                <h5>Fasilitas Lengkap</h5>
                <p>Laboratorium, perpustakaan, dan sarana olahraga modern.</p>
            </div>
            <div class="col-md-4 mb-4">
                <div class="feature-icon mb-3"><i class="fas fa-trophy"></i></div>
                <h5>Prestasi Membanggakan</h5>
                <p>Berbagai penghargaan akademik dan non-akademik tingkat nasional.</p>
            </div>
        </div>
    </div>
</section>
<?= $this->include('layouts/chatbot') ?>
<!-- Dynamic Content -->
<div class="container my-5">
    <?= $this->renderSection('content') ?>
</div>

<?= $this->include('layouts/footer'); ?>