<?php
/**
 * Steppa Discover - header.php
 * Dark gaming navigation with mega search and mobile drawer
 */

$genre_list = get_terms([
    'taxonomy'   => 'game_genre',
    'hide_empty' => true,
    'orderby'    => 'count',
    'order'      => 'DESC',
    'number'     => 20,
]);

function steppa_genre_icon($name) {
    $icons = [
        'Action'        => '⚔️',
        'Arcade'        => '🕹️',
        'Puzzle'        => '🧩',
        'RPG'           => '🐉',
        'Racing'        => '🏎️',
        'Sports'        => '⚽',
        'Strategy'      => '♟️',
        'Casual'        => '😊',
        'Simulation'    => '🏙️',
        'Adventure'     => '🗺️',
        'Educational'   => '📚',
        'Battle Royale' => '🎯',
        'Open World'    => '🌍',
        'Multiplayer'   => '👥',
        'Offline'       => '📴',
    ];
    foreach ($icons as $key => $icon) {
        if (stripos($name, $key) !== false) return $icon;
    }
    return '🎮';
}
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    <?php if (!is_singular('android_game') && !is_tax('game_genre')): ?>
    <meta name="description" content="Discover the best Android games. Browse 1000+ top-rated mobile games across action, RPG, puzzle, racing, and more categories. Find trending and newly released games.">
    <?php endif; ?>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <?php wp_head(); ?>

    <!-- Tailwind Config -->
    <script>
    window.tailwind_config = {
        theme: {
            extend: {
                colors: {
                    purple: { DEFAULT: '#7c3aed', light: '#8b5cf6' },
                    gaming: { dark: '#050508', card: '#13132a' }
                }
            }
        }
    };
    </script>
</head>
<body <?php body_class('steppa-body'); ?>>
<?php wp_body_open(); ?>

<!-- Toast Container -->
<div id="toast" class="toast" role="alert" aria-live="polite"></div>

<!-- Lightbox -->
<div id="lightbox" class="lightbox" role="dialog" aria-modal="true" aria-label="Screenshot viewer">
    <button id="lightbox-close" class="lightbox-close" aria-label="Close">&times;</button>
    <img id="lightbox-img" src="" alt="Game Screenshot">
</div>

<!-- HEADER -->
<header class="site-header" role="banner">
    <div class="container">
        <nav class="nav-inner" role="navigation" aria-label="Main navigation">

            <!-- Logo -->
            <a href="<?php echo esc_url(home_url('/')); ?>" class="site-logo" aria-label="Steppa - Home">
                <div class="site-logo-icon" aria-hidden="true">🎮</div>
                Steppa
            </a>

            <!-- Desktop Search -->
            <div class="nav-search" role="search">
                <span class="search-icon" aria-hidden="true">🔍</span>
                <input
                    type="search"
                    class="nav-search-input"
                    placeholder="Search Android games..."
                    aria-label="Search games"
                    autocomplete="off"
                    value="<?php echo esc_attr(get_search_query()); ?>"
                >
                <div class="search-dropdown" role="listbox" aria-label="Search suggestions"></div>
            </div>

            <!-- Nav Links -->
            <div class="nav-links" role="list">
                <a href="<?php echo esc_url(get_post_type_archive_link('android_game')); ?>" class="nav-link" role="listitem">All Games</a>
                <?php if (!is_wp_error($genre_list)): foreach (array_slice((array)$genre_list, 0, 4) as $genre): ?>
                <a href="<?php echo esc_url(get_term_link($genre)); ?>" class="nav-link" role="listitem">
                    <?php echo steppa_genre_icon($genre->name) . ' ' . esc_html($genre->name); ?>
                </a>
                <?php endforeach; endif; ?>
                <a href="<?php echo esc_url(home_url('/about/')); ?>" class="nav-link" role="listitem">About</a>
            </div>

            <!-- Hamburger (mobile) -->
            <button id="nav-hamburger" class="nav-hamburger" aria-label="Open menu" aria-expanded="false" aria-controls="mobile-drawer">
                <span></span><span></span><span></span>
            </button>

        </nav>
    </div>
</header>

<!-- MOBILE DRAWER -->
<div id="mobile-drawer" class="mobile-drawer" role="dialog" aria-modal="true" aria-label="Navigation menu">
    <div id="drawer-overlay" class="drawer-overlay"></div>
    <div class="drawer-panel">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;">
            <a href="<?php echo esc_url(home_url('/')); ?>" class="drawer-logo">🎮 Steppa</a>
            <button id="drawer-close" style="color:var(--text-2);font-size:1.5rem;line-height:1;padding:4px;" aria-label="Close menu">&times;</button>
        </div>

        <input type="search" class="drawer-search" placeholder="Search games..." aria-label="Search games">

        <div class="drawer-section-title">Browse Categories</div>
        <div class="drawer-genre-list" role="list">
            <a href="<?php echo esc_url(get_post_type_archive_link('android_game')); ?>" class="drawer-genre-item" role="listitem">
                <span class="drawer-genre-icon">🎮</span>
                <span>All Games</span>
            </a>
            <?php if (!is_wp_error($genre_list) && !empty($genre_list)): ?>
                <?php foreach ($genre_list as $genre): ?>
                <a href="<?php echo esc_url(get_term_link($genre)); ?>" class="drawer-genre-item" role="listitem">
                    <span class="drawer-genre-icon"><?php echo steppa_genre_icon($genre->name); ?></span>
                    <span><?php echo esc_html($genre->name); ?></span>
                    <span class="drawer-genre-count"><?php echo (int) $genre->count; ?></span>
                </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <div class="drawer-section-title">Quick Links</div>
        <div class="drawer-genre-list">
            <a href="<?php echo esc_url(home_url('/about/')); ?>" class="drawer-genre-item">
                <span class="drawer-genre-icon">ℹ️</span><span>About Steppa</span>
            </a>
            <a href="<?php echo esc_url(home_url('/contact/')); ?>" class="drawer-genre-item">
                <span class="drawer-genre-icon">✉️</span><span>Contact</span>
            </a>
            <a href="<?php echo esc_url(home_url('/privacy-policy/')); ?>" class="drawer-genre-item">
                <span class="drawer-genre-icon">🔒</span><span>Privacy Policy</span>
            </a>
        </div>
    </div>
</div>
