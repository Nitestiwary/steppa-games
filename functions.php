<?php
/**
 * Steppa Theme — functions.php
 */

define('STEPPA_VER', '3.0.0');
define('STEPPA_DIR', get_template_directory());
define('STEPPA_URI', get_template_directory_uri());

// Theme Setup
function steppa_setup() {
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('html5', ['search-form','comment-form','comment-list','gallery','caption']);
    add_theme_support('custom-logo');
}
add_action('after_setup_theme', 'steppa_setup');

// Enqueue
function steppa_enqueue() {
    wp_enqueue_style('steppa-main', STEPPA_URI . '/assets/css/main.css', [], STEPPA_VER);
    wp_enqueue_style('steppa-style', get_stylesheet_uri(), [], STEPPA_VER);
    wp_enqueue_script('steppa-js', STEPPA_URI . '/assets/js/main.js', [], STEPPA_VER, true);
    wp_localize_script('steppa-js', 'steppa', [
        'ajax'   => admin_url('admin-ajax.php'),
        'nonce'  => wp_create_nonce('steppa_nonce'),
        'home'   => home_url('/'),
    ]);
}
add_action('wp_enqueue_scripts', 'steppa_enqueue');

// Includes
require_once STEPPA_DIR . '/inc/cpt.php';
require_once STEPPA_DIR . '/inc/seo.php';
require_once STEPPA_DIR . '/inc/ajax.php';
require_once STEPPA_DIR . '/inc/seeder.php';

// Helpers
function steppa_rating_stars($rating, $max = 5) {
    $out = '';
    $r = floatval($rating);
    for ($i = 1; $i <= $max; $i++) {
        if ($r >= $i) $out .= '★';
        elseif ($r >= $i - 0.5) $out .= '½';
        else $out .= '☆';
    }
    return $out;
}

function steppa_genre_icon($name) {
    $map = [
        'Action'        => '⚔️', 'Arcade'     => '🕹️', 'Puzzle'      => '🧩',
        'RPG'           => '🐉', 'Racing'     => '🏎️', 'Sports'      => '⚽',
        'Strategy'      => '♟️', 'Casual'     => '😊', 'Simulation'  => '🏙️',
        'Adventure'     => '🗺️', 'Educational'=> '📚', 'Battle Royale'=> '🎯',
        'Open World'    => '🌍', 'Multiplayer'=> '👥', 'Offline'     => '📴',
        'Horror'        => '👻', 'Music'      => '🎵', 'Card'        => '🃏',
        'Board'         => '🎲', 'Word'       => '📝',
    ];
    foreach ($map as $key => $icon) {
        if (stripos($name, $key) !== false) return $icon;
    }
    return '🎮';
}

// Render game card (reusable)
function steppa_card($post_id, $badge = '') {
    $icon       = get_post_meta($post_id, '_game_icon', true);
    $rating     = get_post_meta($post_id, '_game_rating', true);
    $installs   = get_post_meta($post_id, '_game_installs', true);
    $price      = get_post_meta($post_id, '_game_price', true) ?: 'Free';
    $offline    = get_post_meta($post_id, '_game_offline', true);
    $dev        = get_post_meta($post_id, '_game_dev', true) ?: 'Unknown';
    $genres     = get_the_terms($post_id, 'game_genre');
    $genre_name = (!is_wp_error($genres) && !empty($genres)) ? $genres[0]->name : '';

    $fallback = 'https://ui-avatars.com/api/?name=' . urlencode(get_the_title($post_id)) . '&background=3dbb85&color=fff&size=256&bold=true';
    $icon_url = $icon ?: $fallback;

    $badge_html = '';
    if ($badge === 'trending') $badge_html = '<span class="game-card-badge hot">🔥 Hot</span>';
    elseif ($badge === 'new')  $badge_html = '<span class="game-card-badge new">🆕 New</span>';
    elseif ($badge === 'top')  $badge_html = '<span class="game-card-badge">⭐ Top</span>';

    ob_start(); ?>
    <a href="<?php echo esc_url(get_permalink($post_id)); ?>" class="game-card" aria-label="<?php echo esc_attr(get_the_title($post_id)); ?>">
        <div class="game-card-thumb" style="position:relative;">
            <img src="<?php echo esc_url($icon_url); ?>"
                 alt="<?php echo esc_attr(get_the_title($post_id)); ?>"
                 loading="lazy"
                 onerror="this.src='<?php echo esc_url($fallback); ?>'">
            <?php echo $badge_html; ?>
        </div>
        <div class="game-card-body">
            <div class="game-card-name"><?php echo esc_html(get_the_title($post_id)); ?></div>
            <div class="game-card-dev"><?php echo esc_html($dev); ?></div>
            <div class="game-card-rating">
                <span class="stars-sm"><?php echo steppa_rating_stars($rating); ?></span>
                <span><?php echo esc_html(number_format((float)$rating, 1)); ?></span>
            </div>
        </div>
    </a>
    <?php return ob_get_clean();
}

// Flush rewrite rules on activation
function steppa_activate() {
    steppa_register_cpt();
    flush_rewrite_rules();
}
add_action('after_switch_theme', 'steppa_activate');

// Remove admin bar for non-admins
if (!current_user_can('manage_options')) {
    add_filter('show_admin_bar', '__return_false');
}
