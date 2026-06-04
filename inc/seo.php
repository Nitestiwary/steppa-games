<?php
// SEO Meta Tags & Schema
function steppa_seo() {
    if (is_singular('android_game')) {
        global $post;
        $pid     = $post->ID;
        $icon    = get_post_meta($pid, '_game_icon', true);
        $rating  = get_post_meta($pid, '_game_rating', true);
        $reviews = get_post_meta($pid, '_game_reviews', true);
        $installs= get_post_meta($pid, '_game_installs', true);
        $dev     = get_post_meta($pid, '_game_dev', true);
        $price   = get_post_meta($pid, '_game_price', true) ?: 'Free';
        $genres  = get_the_terms($pid, 'game_genre');
        $genre   = (!is_wp_error($genres) && !empty($genres)) ? $genres[0]->name : 'Game';
        $title   = get_the_title() . ' APK Download Free for Android | Steppa';
        $excerpt = get_the_excerpt() ?: wp_trim_words(strip_tags(get_the_content()), 25);
        $desc    = esc_attr(wp_trim_words($excerpt, 22, '...')) . ' - Download ' . get_the_title() . ' latest version free for Android.';
        $url     = get_permalink();

        echo '<meta name="description" content="' . $desc . '">' . "\n";
        echo '<link rel="canonical" href="' . esc_url($url) . '">' . "\n";
        echo '<meta property="og:title" content="' . esc_attr($title) . '">' . "\n";
        echo '<meta property="og:description" content="' . $desc . '">' . "\n";
        echo '<meta property="og:url" content="' . esc_url($url) . '">' . "\n";
        echo '<meta property="og:type" content="website">' . "\n";
        if ($icon) echo '<meta property="og:image" content="' . esc_url($icon) . '">' . "\n";
        echo '<meta name="twitter:card" content="summary">' . "\n";
        echo '<meta name="twitter:title" content="' . esc_attr($title) . '">' . "\n";
        echo '<meta name="twitter:description" content="' . $desc . '">' . "\n";

        // SoftwareApplication Schema
        $rcount = (int) preg_replace('/[^0-9]/', '', $reviews ?: '1000');
        if ($rcount < 1) $rcount = 1;
        $schema = [
            '@context'            => 'https://schema.org',
            '@type'               => 'SoftwareApplication',
            'name'                => get_the_title(),
            'operatingSystem'     => 'Android',
            'applicationCategory' => 'GameApplication',
            'description'         => strip_tags($excerpt),
            'offers'              => ['@type' => 'Offer', 'price' => ($price === 'Free' ? '0' : preg_replace('/[^0-9.]/', '', $price)), 'priceCurrency' => 'USD'],
            'aggregateRating'     => ['@type' => 'AggregateRating', 'ratingValue' => floatval($rating) ?: 4.5, 'ratingCount' => $rcount],
        ];
        if ($icon) $schema['image'] = $icon;
        echo '<script type="application/ld+json">' . wp_json_encode($schema, JSON_UNESCAPED_SLASHES) . '</script>' . "\n";

    } elseif (is_tax('game_genre')) {
        $term = get_queried_object();
        $title = $term->name . ' Android Games - Download Free | Steppa';
        $desc  = 'Download the best ' . strtolower($term->name) . ' Android games for free. Browse top-rated, trending and newest ' . strtolower($term->name) . ' games.';
        echo '<meta name="description" content="' . esc_attr($desc) . '">' . "\n";
        echo '<link rel="canonical" href="' . esc_url(get_term_link($term)) . '">' . "\n";
        echo '<meta property="og:title" content="' . esc_attr($title) . '">' . "\n";
        echo '<meta property="og:description" content="' . esc_attr($desc) . '">' . "\n";

    } elseif (is_post_type_archive('android_game')) {
        $title = 'Download Free Android Games | Steppa';
        $desc  = 'Browse and download thousands of free Android games. Find the latest action, RPG, puzzle, racing games and more.';
        echo '<meta name="description" content="' . esc_attr($desc) . '">' . "\n";
        echo '<link rel="canonical" href="' . esc_url(get_post_type_archive_link('android_game')) . '">' . "\n";

    } elseif (is_front_page()) {
        $title = 'Steppa - Download Top Free Mobile Games for Android';
        $desc  = 'Discover and download the best free Android games. Browse 1000+ top-rated mobile games across action, RPG, puzzle, racing and more categories.';
        echo '<meta name="description" content="' . esc_attr($desc) . '">' . "\n";

        $schema = [
            '@context' => 'https://schema.org',
            '@type'    => 'WebSite',
            'name'     => 'Steppa',
            'url'      => home_url('/'),
            'potentialAction' => ['@type' => 'SearchAction', 'target' => ['@type' => 'EntryPoint', 'urlTemplate' => home_url('/?s={search_term_string}')], 'query-input' => 'required name=search_term_string'],
        ];
        echo '<script type="application/ld+json">' . wp_json_encode($schema, JSON_UNESCAPED_SLASHES) . '</script>' . "\n";
    }
}
add_action('wp_head', 'steppa_seo', 1);

// Custom title
function steppa_title($parts) {
    if (is_singular('android_game')) {
        $parts['title'] = get_the_title() . ' APK Download Free | Steppa';
    } elseif (is_tax('game_genre')) {
        $term = get_queried_object();
        $parts['title'] = $term->name . ' Games Download Free | Steppa';
    } elseif (is_post_type_archive('android_game')) {
        $parts['title'] = 'Download Free Android Games | Steppa';
    } elseif (is_front_page()) {
        $parts['title'] = 'Steppa - Download Top Free Mobile Games for Android';
    }
    return $parts;
}
add_filter('document_title_parts', 'steppa_title');
