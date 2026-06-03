<?php
/**
 * Steppa Discover — functions.php
 * Main theme setup and includes
 */

// Theme Constants
define('STEPPA_VERSION', '2.0.0');
define('STEPPA_THEME_DIR', get_template_directory());
define('STEPPA_THEME_URI', get_template_directory_uri());

// Theme Setup
function steppa_setup() {
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('html5', ['search-form', 'comment-form', 'comment-list', 'gallery', 'caption']);
}
add_action('after_setup_theme', 'steppa_setup');

// Enqueue Scripts & Styles
function steppa_scripts() {
    // Main CSS
    wp_enqueue_style('steppa-style', get_stylesheet_uri(), [], STEPPA_VERSION);
    wp_enqueue_style('steppa-main', STEPPA_THEME_URI . '/assets/css/main.css', [], STEPPA_VERSION);
    
    // Tailwind CDN (for utility classes where needed)
    wp_enqueue_script('steppa-tailwind', 'https://cdn.tailwindcss.com', [], null, false);
    
    // Main JS
    wp_enqueue_script('steppa-main-js', STEPPA_THEME_URI . '/assets/js/main.js', [], STEPPA_VERSION, true);
    
    // Pass Data to JS
    wp_localize_script('steppa-main-js', 'steppaData', [
        'ajaxUrl'   => admin_url('admin-ajax.php'),
        'nonce'     => wp_create_nonce('steppa_nonce'),
        'searchUrl' => home_url('/'),
    ]);
}
add_action('wp_enqueue_scripts', 'steppa_scripts');

// Required Includes
require_once STEPPA_THEME_DIR . '/inc/cpt.php';
require_once STEPPA_THEME_DIR . '/inc/seo.php';
require_once STEPPA_THEME_DIR . '/inc/ajax-handlers.php';
// require_once STEPPA_THEME_DIR . '/inc/admin-import.php'; // Will create later if needed
require_once STEPPA_THEME_DIR . '/inc/seeder.php';

// Helper: Genre Icon
function steppa_get_genre_icon($name) {
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
