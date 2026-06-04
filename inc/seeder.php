<?php
/**
 * Steppa — Seeder (150 games)
 * Runs once on theme activation
 */

function steppa_seed_games() {
    if (get_option('steppa_seeded_v3') === 'yes') return;

    $games_file = STEPPA_DIR . '/inc/games-data.php';
    if (!file_exists($games_file)) return;
    $games = include $games_file;
    if (!is_array($games)) return;

    // Create static pages
    $pages = [
        ['title' => 'Contact Us',     'slug' => 'contact',        'template' => 'page-contact.php'],
        ['title' => 'Privacy Policy', 'slug' => 'privacy-policy', 'template' => 'page-privacy.php'],
        ['title' => 'Terms of Service','slug' => 'terms',         'template' => 'page-terms.php'],
        ['title' => 'About Us',       'slug' => 'about',          'template' => 'page-about.php'],
        ['title' => 'DMCA',           'slug' => 'dmca',           'template' => 'page-dmca.php'],
    ];
    foreach ($pages as $p) {
        if (!get_page_by_path($p['slug'])) {
            $pid = wp_insert_post(['post_title' => $p['title'], 'post_name' => $p['slug'], 'post_status' => 'publish', 'post_type' => 'page']);
            if ($p['template'] && $pid) update_post_meta($pid, '_wp_page_template', $p['template']);
        }
    }

    // Insert games
    foreach ($games as $g) {
        $existing = new WP_Query(['post_type' => 'android_game', 'name' => sanitize_title($g['title']), 'posts_per_page' => 1]);
        if ($existing->have_posts()) continue;

        $pid = wp_insert_post([
            'post_title'   => sanitize_text_field($g['title']),
            'post_name'    => sanitize_title($g['title']),
            'post_content' => wp_kses_post($g['desc']),
            'post_excerpt' => wp_kses_post(wp_trim_words($g['desc'], 30)),
            'post_status'  => 'publish',
            'post_type'    => 'android_game',
        ]);

        if (!$pid || is_wp_error($pid)) continue;

        // Genres
        if (!empty($g['genres'])) {
            wp_set_post_terms($pid, array_map('sanitize_text_field', $g['genres']), 'game_genre');
        }

        // Meta
        $meta = [
            '_game_icon'         => esc_url_raw($g['icon'] ?? ''),
            '_game_dev'          => sanitize_text_field($g['developer'] ?? ''),
            '_game_rating'       => floatval($g['rating'] ?? 4.0),
            '_game_reviews'      => sanitize_text_field($g['reviews'] ?? ''),
            '_game_installs'     => sanitize_text_field($g['installs'] ?? ''),
            '_game_installs_raw' => intval($g['installs_raw'] ?? 0),
            '_game_size'         => sanitize_text_field($g['size'] ?? ''),
            '_game_version'      => sanitize_text_field($g['version'] ?? ''),
            '_game_updated'      => sanitize_text_field($g['updated'] ?? ''),
            '_game_price'        => sanitize_text_field($g['price'] ?? 'Free'),
            '_game_offline'      => ($g['offline'] ?? false) ? '1' : '0',
            '_game_pkg'          => sanitize_text_field($g['pkg'] ?? ''),
            '_game_playstore'    => esc_url_raw($g['playstore'] ?? ''),
            '_game_trending'     => intval($g['trending'] ?? 50),
            '_game_featured'     => ($g['featured'] ?? false) ? '1' : '0',
            '_game_category'     => sanitize_text_field($g['category'] ?? ''),
            '_game_age'          => sanitize_text_field($g['age'] ?? '3+'),
            '_game_platform'     => 'Android',
            '_game_how_to_play'  => wp_kses_post($g['how_to_play'] ?? ''),
            '_game_editor_review'=> wp_kses_post($g['editor_review'] ?? ''),
        ];
        foreach ($meta as $key => $val) update_post_meta($pid, $key, $val);
    }

    update_option('steppa_seeded_v3', 'yes');
}
add_action('init', 'steppa_seed_games', 60);

// Admin reseed trigger
function steppa_maybe_reseed() {
    if (isset($_GET['steppa_reseed']) && current_user_can('manage_options')) {
        $all = get_posts(['post_type' => 'android_game', 'numberposts' => -1, 'fields' => 'ids']);
        foreach ($all as $id) wp_delete_post($id, true);
        delete_option('steppa_seeded_v3');
        wp_safe_redirect(admin_url('?steppa_reseeded=1'));
        exit;
    }
}
add_action('admin_init', 'steppa_maybe_reseed');
