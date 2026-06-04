<?php
// AJAX Handlers

// Search
function steppa_ajax_search() {
    check_ajax_referer('steppa_nonce', 'nonce');
    $q = sanitize_text_field($_POST['q'] ?? '');
    if (strlen($q) < 2) { wp_send_json_success([]); return; }

    $query = new WP_Query([
        'post_type' => 'android_game', 'posts_per_page' => 6, 's' => $q, 'post_status' => 'publish',
    ]);
    $results = [];
    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $icon   = get_post_meta(get_the_ID(), '_game_icon', true);
            $genres = get_the_terms(get_the_ID(), 'game_genre');
            $genre  = (!is_wp_error($genres) && !empty($genres)) ? $genres[0]->name : '';
            $rating = get_post_meta(get_the_ID(), '_game_rating', true);
            $results[] = [
                'title'  => get_the_title(),
                'url'    => get_permalink(),
                'icon'   => $icon ?: 'https://ui-avatars.com/api/?name=' . urlencode(get_the_title()) . '&background=3dbb85&color=fff&size=64',
                'genre'  => $genre,
                'rating' => $rating ? number_format((float)$rating, 1) : '',
            ];
        }
    }
    wp_reset_postdata();
    wp_send_json_success($results);
}
add_action('wp_ajax_steppa_search', 'steppa_ajax_search');
add_action('wp_ajax_nopriv_steppa_search', 'steppa_ajax_search');

// Load More
function steppa_ajax_loadmore() {
    check_ajax_referer('steppa_nonce', 'nonce');
    $page    = (int)($_POST['page'] ?? 1);
    $genre   = sanitize_text_field($_POST['genre'] ?? '');
    $sort    = sanitize_text_field($_POST['sort'] ?? 'newest');
    $pp      = (int)($_POST['pp'] ?? 20);

    $args = ['post_type' => 'android_game', 'posts_per_page' => $pp, 'paged' => $page, 'post_status' => 'publish'];
    if ($sort === 'rating')   { $args['meta_key'] = '_game_rating';    $args['orderby'] = 'meta_value_num'; $args['order'] = 'DESC'; }
    elseif ($sort === 'install') { $args['meta_key'] = '_game_installs_raw'; $args['orderby'] = 'meta_value_num'; $args['order'] = 'DESC'; }
    elseif ($sort === 'trending') { $args['meta_key'] = '_game_trending'; $args['orderby'] = 'meta_value_num'; $args['order'] = 'DESC'; }
    else { $args['orderby'] = 'date'; $args['order'] = 'DESC'; }

    if ($genre) $args['tax_query'] = [['taxonomy' => 'game_genre', 'field' => 'slug', 'terms' => $genre]];

    $query = new WP_Query($args);
    $html = '';
    if ($query->have_posts()) {
        ob_start();
        while ($query->have_posts()) { $query->the_post(); echo steppa_card(get_the_ID()); }
        $html = ob_get_clean();
    }
    wp_reset_postdata();
    wp_send_json_success(['html' => $html, 'has_more' => ($query->max_num_pages > $page)]);
}
add_action('wp_ajax_steppa_loadmore', 'steppa_ajax_loadmore');
add_action('wp_ajax_nopriv_steppa_loadmore', 'steppa_ajax_loadmore');
