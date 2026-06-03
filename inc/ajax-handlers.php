<?php
/**
 * Steppa Discover — AJAX Handlers
 */

// Search Autocomplete
function steppa_ajax_search() {
    check_ajax_referer('steppa_nonce', 'nonce');
    
    $q = isset($_POST['q']) ? sanitize_text_field($_POST['q']) : '';
    if (strlen($q) < 2) {
        wp_send_json_success([]);
    }

    $args = [
        'post_type'      => 'android_game',
        'posts_per_page' => 5,
        's'              => $q,
        'post_status'    => 'publish',
    ];

    $query = new WP_Query($args);
    $results = [];

    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $icon = get_post_meta(get_the_ID(), '_game_icon_url', true);
            $genres = get_the_terms(get_the_ID(), 'game_genre');
            $genre = (!is_wp_error($genres) && !empty($genres)) ? $genres[0]->name : 'Game';
            $rating = get_post_meta(get_the_ID(), '_game_rating', true);

            $results[] = [
                'id'     => get_the_ID(),
                'title'  => get_the_title(),
                'url'    => get_permalink(),
                'icon'   => $icon ?: 'https://ui-avatars.com/api/?name=' . urlencode(get_the_title()) . '&background=7c3aed&color=fff',
                'genre'  => $genre,
                'rating' => $rating ? number_format((float)$rating, 1) : 'N/A'
            ];
        }
    }
    wp_reset_postdata();
    wp_send_json_success($results);
}
add_action('wp_ajax_steppa_search', 'steppa_ajax_search');
add_action('wp_ajax_nopriv_steppa_search', 'steppa_ajax_search');

// Load More / Pagination
function steppa_ajax_load_more() {
    check_ajax_referer('steppa_nonce', 'nonce');

    $page = isset($_POST['page']) ? (int)$_POST['page'] : 1;
    $genre = isset($_POST['genre']) ? sanitize_text_field($_POST['genre']) : '';
    $sort = isset($_POST['sort']) ? sanitize_text_field($_POST['sort']) : 'newest';
    $per_page = isset($_POST['per_page']) ? (int)$_POST['per_page'] : 24;
    $install_filter = isset($_POST['install_filter']) ? sanitize_text_field($_POST['install_filter']) : '';

    $args = [
        'post_type'      => 'android_game',
        'posts_per_page' => $per_page,
        'paged'          => $page,
        'post_status'    => 'publish',
    ];

    // Sorting
    if ($sort === 'rating') {
        $args['meta_key'] = '_game_rating';
        $args['orderby']  = 'meta_value_num';
        $args['order']    = 'DESC';
    } elseif ($sort === 'installs') {
        $args['meta_key'] = '_game_installs_raw';
        $args['orderby']  = 'meta_value_num';
        $args['order']    = 'DESC';
    } elseif ($sort === 'trending') {
        $args['meta_key'] = '_game_trending_score';
        $args['orderby']  = 'meta_value_num';
        $args['order']    = 'DESC';
    } else {
        $args['orderby']  = 'date';
        $args['order']    = 'DESC';
    }

    // Install Filter (e.g. 100000000)
    if ($install_filter) {
        $args['meta_query'] = [
            [
                'key'     => '_game_installs_raw',
                'value'   => (int)$install_filter,
                'compare' => '>=',
                'type'    => 'NUMERIC'
            ]
        ];
    }

    // Genre Filter
    if ($genre) {
        $args['tax_query'] = [
            [
                'taxonomy' => 'game_genre',
                'field'    => 'slug',
                'terms'    => $genre
            ]
        ];
    }

    $query = new WP_Query($args);
    $html = '';

    if ($query->have_posts()) {
        ob_start();
        while ($query->have_posts()) {
            $query->the_post();
            
            // Inline the game card since we might not have access to the function depending on scope, 
            // but ideally we'd call steppa_game_card() if it's in functions.php
            if (function_exists('steppa_game_card')) {
                echo steppa_game_card(get_the_ID());
            }
        }
        $html = ob_get_clean();
    }
    
    wp_reset_postdata();

    wp_send_json_success([
        'html'     => $html,
        'has_more' => $query->max_num_pages > $page
    ]);
}
add_action('wp_ajax_steppa_load_more', 'steppa_ajax_load_more');
add_action('wp_ajax_nopriv_steppa_load_more', 'steppa_ajax_load_more');
