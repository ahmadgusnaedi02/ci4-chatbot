<!-- Footer -->
<footer class="footer mt-auto">
    <div class="container text-center">
        <div class="mb-2">
            <a href="#" class="text-white me-3"><i class="fab fa-facebook fa-lg"></i></a>
            <a href="#" class="text-white me-3"><i class="fab fa-instagram fa-lg"></i></a>
            <a href="#" class="text-white me-3"><i class="fab fa-twitter fa-lg"></i></a>
            <a href="#" class="text-white"><i class="fab fa-youtube fa-lg"></i></a>
        </div>
        <div>
            &copy; <?= date('Y') ?> SMPS Plus Fajar Sentosa. Semua hak cipta dilindungi.
        </div>
    </div>
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/emoji-mart@latest/dist/browser.js"></script>
<script src="<?= base_url('js/script.js') ?>"></script>
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
