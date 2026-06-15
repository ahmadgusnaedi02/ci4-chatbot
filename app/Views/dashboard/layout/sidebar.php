<div class="container-fluid page-body-wrapper">
    <?php
    $currentPath = service('uri')->getPath();
    $menus = admin_sidebar_menus();
    $lastCategory = null;
    ?>
    <nav class="sidebar sidebar-offcanvas" id="sidebar">
        <ul class="nav">
            <?php foreach ($menus as $menu): ?>
                <?php if (($menu['category'] ?? null) !== $lastCategory): ?>
                    <?php $lastCategory = $menu['category'] ?? null; ?>
                    <?php if ($lastCategory): ?>
                        <li class="nav-item nav-category"><?= esc($lastCategory) ?></li>
                    <?php endif; ?>
                <?php endif; ?>

                <?php
                $isActive = false;

                foreach ($menu['active'] ?? [$menu['url']] as $activePath) {
                    if ($currentPath === $activePath || str_starts_with($currentPath, $activePath . '/')) {
                        $isActive = true;
                        break;
                    }
                }
                ?>
                <li class="nav-item <?= $isActive ? 'active' : '' ?>">
                    <a class="nav-link" href="<?= site_url($menu['url']) ?>">
                        <i class="<?= esc($menu['icon']) ?> menu-icon"></i>
                        <span class="menu-title"><?= esc($menu['label']) ?></span>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    </nav>
