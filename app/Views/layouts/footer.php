<!-- Footer -->
<?php
    $settings = $landingSettings ?? [];
    $mapAddress = trim((string) ($settings['contact_address'] ?? ''));
    $latitude = trim((string) ($settings['contact_latitude'] ?? ''));
    $longitude = trim((string) ($settings['contact_longitude'] ?? ''));
    $mapEmbedUrl = trim((string) ($settings['contact_map_embed_url'] ?? ''));
    $hasCoordinates = $latitude !== '' && $longitude !== '';
    $mapQuery = $hasCoordinates
        ? $latitude . ',' . $longitude
        : ($mapAddress !== '' ? $mapAddress : ($settings['site_name'] ?? 'SMPS Plus Fajar Sentosa'));
    $mapSrc = $mapEmbedUrl !== ''
        ? $mapEmbedUrl
        : 'https://www.google.com/maps?q=' . rawurlencode($mapQuery) . '&output=embed';
    $mapOpenUrl = 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode($mapQuery);
?>
<footer class="footer mt-auto">
    <div class="container">
        <div class="footer-location-card mb-4">
            <div class="footer-map">
                <iframe
                    src="<?= esc($mapSrc, 'attr') ?>"
                    loading="lazy"
                    referrerpolicy="no-referrer-when-downgrade"
                    title="Lokasi <?= esc($settings['site_name'] ?? 'sekolah', 'attr') ?>"></iframe>
            </div>
            <div class="footer-map-panel">
                <span class="footer-map-kicker">Lokasi Sekolah</span>
                <h3><?= esc($settings['site_name'] ?? 'SMPS Plus Fajar Sentosa') ?></h3>
                <?php if ($mapAddress !== ''): ?>
                    <p><?= esc($mapAddress) ?></p>
                <?php endif; ?>
                <a class="footer-map-action" href="<?= esc($mapOpenUrl, 'attr') ?>" target="_blank" rel="noopener noreferrer">
                    <i class="fa-solid fa-location-arrow"></i>
                    Buka di Google Maps
                </a>
            </div>
        </div>
        <div class="text-center">
            <div class="mb-2">
                <a href="<?= esc($settings['facebook_url'] ?? '#', 'attr') ?>" class="text-white me-3" target="_blank" rel="noopener noreferrer"><i class="fab fa-facebook fa-lg"></i></a>
                <a href="<?= esc($settings['instagram_url'] ?? '#', 'attr') ?>" class="text-white me-3" target="_blank" rel="noopener noreferrer"><i class="fab fa-instagram fa-lg"></i></a>
                <a href="<?= esc($settings['tiktok_url'] ?? '#', 'attr') ?>" class="text-white me-3" target="_blank" rel="noopener noreferrer"><i class="fab fa-tiktok fa-lg"></i></a>
                <a href="<?= esc($settings['youtube_url'] ?? '#', 'attr') ?>" class="text-white" target="_blank" rel="noopener noreferrer"><i class="fab fa-youtube fa-lg"></i></a>
            </div>
            <div>
                &copy; <?= date('Y') ?> <?= esc($settings['site_name'] ?? 'SMPS Plus Fajar Sentosa') ?>. Semua hak cipta dilindungi.
            </div>
        </div>
    </div>
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/emoji-mart@latest/dist/browser.js"></script>
<script src="<?= base_url('js/script.js?v=20260507-admin-reply') ?>"></script>
<script>
    const navLinks = document.querySelectorAll('.school-navbar .nav-link');
    const sections = Array.from(navLinks)
        .map(link => document.querySelector(link.getAttribute('href')))
        .filter(Boolean);

    const setActiveNav = (activeHref) => {
        navLinks.forEach(link => {
            link.classList.toggle('active', link.getAttribute('href') === activeHref);
        });
    };

    navLinks.forEach(link => {
        link.addEventListener('click', function (e) {
            const href = this.getAttribute('href');
            if (href.startsWith('#') && document.querySelector(href)) {
                e.preventDefault();
                setActiveNav(href);
                document.querySelector(href).scrollIntoView({ behavior: 'smooth' });
            }
        });
    });

    window.addEventListener('scroll', () => {
        const activeSection = sections
            .slice()
            .reverse()
            .find(section => section.getBoundingClientRect().top <= 130);

        if (activeSection) {
            setActiveNav(`#${activeSection.id}`);
        }
    }, { passive: true });
</script>
</body>

</html>
