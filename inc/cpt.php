<?php
// CPT Registration
function steppa_register_cpt() {
    register_post_type('android_game', [
        'labels' => [
            'name'          => 'Games',
            'singular_name' => 'Game',
            'add_new_item'  => 'Add New Game',
            'edit_item'     => 'Edit Game',
            'all_items'     => 'All Games',
        ],
        'public'             => true,
        'publicly_queryable' => true,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'query_var'          => true,
        'rewrite'            => ['slug' => 'game'],
        'has_archive'        => true,
        'hierarchical'       => false,
        'menu_icon'          => 'dashicons-smartphone',
        'supports'           => ['title', 'editor', 'thumbnail', 'excerpt'],
        'show_in_rest'       => true,
    ]);

    register_taxonomy('game_genre', ['android_game'], [
        'labels' => [
            'name'          => 'Genres',
            'singular_name' => 'Genre',
            'all_items'     => 'All Genres',
            'edit_item'     => 'Edit Genre',
            'add_new_item'  => 'Add New Genre',
        ],
        'hierarchical'      => false,
        'show_ui'           => true,
        'show_admin_column' => true,
        'rewrite'           => ['slug' => 'games/genre'],
        'show_in_rest'      => true,
    ]);
}
add_action('init', 'steppa_register_cpt', 0);
