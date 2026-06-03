<?php
/**
 * Steppa Discover — Seeder
 * Auto-populates the database with initial game data
 */

function steppa_auto_seed() {
    // Only seed once
    if (get_option('steppa_seeded_v2') === 'yes') {
        return;
    }

    $games_file = STEPPA_THEME_DIR . '/inc/seeder-games.php';
    if (!file_exists($games_file)) return;
    
    $games = include $games_file;
    if (!is_array($games)) return;

    // Create pages if they don't exist
    $pages = [
        'About' => 'Welcome to Steppa, your premium destination for discovering the best Android games. Powered by Monetiscope.',
        'Contact' => 'Get in touch with us at support@steppa.in',
        'Privacy Policy' => 'We take your privacy seriously. This policy describes what personal information we collect and how we use it.',
        'Disclaimer' => 'Steppa is a discovery platform. We do not host any APK files. All downloads are directed to the official Google Play Store.',
        'Terms of Use' => 'By using Steppa, you agree to these terms.'
    ];

    foreach ($pages as $title => $content) {
        if (!get_page_by_title($title)) {
            wp_insert_post([
                'post_title'   => $title,
                'post_content' => $content,
                'post_status'  => 'publish',
                'post_type'    => 'page'
            ]);
        }
    }

    // Insert games
    foreach ($games as $game) {
        // Check if game already exists
        $existing = new WP_Query([
            'post_type' => 'android_game',
            'title'     => $game['title'],
            'posts_per_page' => 1
        ]);

        if ($existing->have_posts()) {
            continue; // Skip
        }

        $post_id = wp_insert_post([
            'post_title'   => sanitize_text_field($game['title']),
            'post_name'    => sanitize_title($game['title']),
            'post_content' => wp_kses_post($game['desc']),
            'post_status'  => 'publish',
            'post_type'    => 'android_game'
        ]);

        if ($post_id && !is_wp_error($post_id)) {
            // Set Genres
            if (!empty($game['genres'])) {
                foreach ($game['genres'] as $genre) {
                    wp_set_post_terms($post_id, sanitize_text_field($genre), 'game_genre', true);
                }
            }

            // Set Developer
            if (!empty($game['developer'])) {
                wp_set_post_terms($post_id, sanitize_text_field($game['developer']), 'game_developer_tax');
            }

            // Meta data
            update_post_meta($post_id, '_game_package_id', sanitize_text_field($game['package_id']));
            update_post_meta($post_id, '_game_icon_url', esc_url_raw($game['icon']));
            update_post_meta($post_id, '_game_rating', sanitize_text_field($game['rating']));
            update_post_meta($post_id, '_game_reviews', sanitize_text_field($game['reviews']));
            update_post_meta($post_id, '_game_installs', sanitize_text_field($game['installs']));
            update_post_meta($post_id, '_game_installs_raw', (int)$game['installs_raw']);
            update_post_meta($post_id, '_game_size', sanitize_text_field($game['size']));
            update_post_meta($post_id, '_game_version', sanitize_text_field($game['version']));
            update_post_meta($post_id, '_game_updated', sanitize_text_field($game['updated']));
            update_post_meta($post_id, '_game_price', sanitize_text_field($game['price']));
            update_post_meta($post_id, '_game_is_offline', empty($game['is_offline']) ? '0' : '1');
            update_post_meta($post_id, '_game_screenshots', wp_json_encode(array_map('esc_url_raw', $game['screenshots'])));
            update_post_meta($post_id, '_game_playstore_url', esc_url_raw($game['playstore_url']));
            update_post_meta($post_id, '_game_trending_score', (int)$game['trending_score']);
            update_post_meta($post_id, '_game_featured', empty($game['featured']) ? '0' : '1');
            update_post_meta($post_id, '_game_developer', sanitize_text_field($game['developer']));
        }
    }

    update_option('steppa_seeded_v2', 'yes');
}
add_action('init', 'steppa_auto_seed', 50);

// Admin manual trigger for reseed
function steppa_reseed_manually() {
    if (isset($_GET['steppa_reseed']) && current_user_can('manage_options')) {
        // Delete all games
        $all_games = get_posts(['post_type' => 'android_game', 'numberposts' => -1]);
        foreach ($all_games as $g) {
            wp_delete_post($g->ID, true);
        }
        delete_option('steppa_seeded_v2');
        wp_redirect(admin_url('/'));
        exit;
    }
}
add_action('admin_init', 'steppa_reseed_manually');
