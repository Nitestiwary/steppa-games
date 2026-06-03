<?php
/**
 * Steppa Discover — SEO, Meta Tags, and Schema
 */

function steppa_seo_meta_tags() {
    $title = '';
    $desc  = '';
    $url   = '';
    $image = '';
    $type  = 'website';

    if (is_front_page() || is_home()) {
        $title = 'Steppa - Discover The Best Android Games';
        $desc  = 'Discover the best Android games. Browse 1000+ top-rated mobile games across action, RPG, puzzle, racing and more categories.';
        $url   = home_url('/');
    } 
    elseif (is_singular('android_game')) {
        global $post;
        $title = get_the_title() . ' - Free Android Game | Steppa';
        $desc  = wp_trim_words(strip_shortcodes(strip_tags($post->post_content)), 25, '') . ' | Play on Android';
        $url   = get_permalink();
        $image = get_post_meta($post->ID, '_game_icon_url', true);
        $type  = 'article';
    } 
    elseif (is_tax('game_genre')) {
        $term = get_queried_object();
        $title = $term->name . ' Android Games - Discover & Download | Steppa';
        $desc  = 'Discover the best ' . strtolower($term->name) . ' Android games. Browse top-rated, trending and newest ' . strtolower($term->name) . ' games.';
        $url   = get_term_link($term);
    } 
    elseif (is_post_type_archive('android_game')) {
        $title = 'All Android Games - Browse Catalog | Steppa';
        $desc  = 'Browse our complete catalog of Android games. Filter by genre, rating, and installs to find your next favorite mobile game.';
        $url   = get_post_type_archive_link('android_game');
    } 
    else {
        $title = wp_title('|', false, 'right') . get_bloginfo('name');
        $desc  = get_bloginfo('description');
        $url   = get_permalink();
    }

    echo '<meta name="description" content="' . esc_attr($desc) . '">' . "\n";
    echo '<link rel="canonical" href="' . esc_url($url) . '">' . "\n";
    
    // Open Graph
    echo '<meta property="og:title" content="' . esc_attr($title) . '">' . "\n";
    echo '<meta property="og:description" content="' . esc_attr($desc) . '">' . "\n";
    echo '<meta property="og:url" content="' . esc_url($url) . '">' . "\n";
    echo '<meta property="og:site_name" content="' . esc_attr(get_bloginfo('name')) . '">' . "\n";
    echo '<meta property="og:type" content="' . esc_attr($type) . '">' . "\n";
    if ($image) {
        echo '<meta property="og:image" content="' . esc_url($image) . '">' . "\n";
    }

    // Twitter
    echo '<meta name="twitter:card" content="summary_large_image">' . "\n";
    echo '<meta name="twitter:title" content="' . esc_attr($title) . '">' . "\n";
    echo '<meta name="twitter:description" content="' . esc_attr($desc) . '">' . "\n";
    if ($image) {
        echo '<meta name="twitter:image" content="' . esc_url($image) . '">' . "\n";
    }

    // Schema
    if (is_front_page()) {
        $schema = [
            '@context' => 'https://schema.org',
            '@type'    => 'Organization',
            'name'     => 'Steppa',
            'url'      => home_url('/'),
            'logo'     => home_url('/wp-content/themes/steppa-discover/assets/img/logo.png'), // placeholder
        ];
        echo '<script type="application/ld+json">' . json_encode($schema) . '</script>' . "\n";
    } 
    elseif (is_singular('android_game')) {
        global $post;
        $rating = get_post_meta($post->ID, '_game_rating', true);
        $reviews = get_post_meta($post->ID, '_game_reviews', true);
        $clean_reviews = intval(str_replace(['K', 'M', 'B', '+', ','], ['000', '000000', '000000000', '', ''], $reviews));
        if ($clean_reviews == 0) $clean_reviews = 1;

        $schema = [
            '@context' => 'https://schema.org',
            '@type'    => 'SoftwareApplication',
            'name'     => get_the_title(),
            'operatingSystem' => 'Android',
            'applicationCategory' => 'GameApplication',
            'image'    => get_post_meta($post->ID, '_game_icon_url', true),
            'offers'   => [
                '@type' => 'Offer',
                'price' => get_post_meta($post->ID, '_game_price', true) === 'Free' ? '0' : '0', // simplify
                'priceCurrency' => 'USD'
            ],
            'aggregateRating' => [
                '@type'       => 'AggregateRating',
                'ratingValue' => $rating ? $rating : '4.5',
                'ratingCount' => $clean_reviews
            ]
        ];
        echo '<script type="application/ld+json">' . json_encode($schema) . '</script>' . "\n";
    }
}
add_action('wp_head', 'steppa_seo_meta_tags', 1);

// Custom Title Filter
function steppa_custom_title($title_parts) {
    if (is_front_page() || is_home()) {
        $title_parts['title'] = 'Steppa - Discover The Best Android Games';
    } elseif (is_singular('android_game')) {
        $title_parts['title'] = get_the_title() . ' - Free Android Game | Steppa';
    } elseif (is_tax('game_genre')) {
        $term = get_queried_object();
        $title_parts['title'] = $term->name . ' Android Games - Discover & Download | Steppa';
    } elseif (is_post_type_archive('android_game')) {
        $title_parts['title'] = 'All Android Games - Browse Catalog | Steppa';
    }
    return $title_parts;
}
add_filter('document_title_parts', 'steppa_custom_title');
