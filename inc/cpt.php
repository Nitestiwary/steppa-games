<?php
/**
 * Steppa Discover — Custom Post Types & Taxonomies
 */

function steppa_register_cpt() {
    // Android Game CPT
    $labels = [
        'name'               => 'Games',
        'singular_name'      => 'Game',
        'menu_name'          => 'Games',
        'name_admin_bar'     => 'Game',
        'add_new'            => 'Add New Game',
        'add_new_item'       => 'Add New Android Game',
        'new_item'           => 'New Game',
        'edit_item'          => 'Edit Game',
        'view_item'          => 'View Game',
        'all_items'          => 'All Games',
        'search_items'       => 'Search Games',
        'not_found'          => 'No games found.',
    ];

    $args = [
        'labels'             => $labels,
        'public'             => true,
        'publicly_queryable' => true,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'query_var'          => true,
        'rewrite'            => ['slug' => 'games'],
        'capability_type'    => 'post',
        'has_archive'        => true,
        'hierarchical'       => false,
        'menu_position'      => 5,
        'menu_icon'          => 'dashicons-smartphone',
        'supports'           => ['title', 'editor', 'thumbnail', 'excerpt'],
        'show_in_rest'       => true,
    ];

    register_post_type('android_game', $args);

    // Genre Taxonomy
    $genre_labels = [
        'name'              => 'Genres',
        'singular_name'     => 'Genre',
        'search_items'      => 'Search Genres',
        'all_items'         => 'All Genres',
        'edit_item'         => 'Edit Genre',
        'update_item'       => 'Update Genre',
        'add_new_item'      => 'Add New Genre',
        'new_item_name'     => 'New Genre Name',
        'menu_name'         => 'Genres',
    ];

    register_taxonomy('game_genre', ['android_game'], [
        'hierarchical'      => false, // Tags style
        'labels'            => $genre_labels,
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'rewrite'           => ['slug' => 'games/genre'],
        'show_in_rest'      => true,
    ]);

    // Developer Taxonomy (Internal)
    register_taxonomy('game_developer_tax', ['android_game'], [
        'hierarchical'      => false,
        'labels'            => ['name' => 'Developers', 'singular_name' => 'Developer'],
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'public'            => false, // Admin only
        'rewrite'           => false,
    ]);
}
add_action('init', 'steppa_register_cpt', 0);

// Flush rules on theme activation
function steppa_flush_rewrite_rules() {
    steppa_register_cpt();
    flush_rewrite_rules();
}
add_action('after_switch_theme', 'steppa_flush_rewrite_rules');
