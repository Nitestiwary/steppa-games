<?php
/**
 * Steppa Discover — archive-android_game.php
 * Games listing page with filters and load more
 */
get_header();

// Setup variables
$current_genre = isset($_GET['genre']) ? sanitize_text_field($_GET['genre']) : '';
$current_sort  = isset($_GET['sort']) ? sanitize_text_field($_GET['sort']) : 'newest';
$current_install = isset($_GET['install_filter']) ? sanitize_text_field($_GET['install_filter']) : '';

// Build Query Args
$paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
$args = [
    'post_type'      => 'android_game',
    'posts_per_page' => 24,
    'paged'          => $paged,
    'post_status'    => 'publish',
];

// Sort logic
if ($current_sort === 'rating') {
    $args['meta_key'] = '_game_rating';
    $args['orderby']  = 'meta_value_num';
    $args['order']    = 'DESC';
} elseif ($current_sort === 'installs') {
    $args['meta_key'] = '_game_installs_raw';
    $args['orderby']  = 'meta_value_num';
    $args['order']    = 'DESC';
} elseif ($current_sort === 'trending') {
    $args['meta_key'] = '_game_trending_score';
    $args['orderby']  = 'meta_value_num';
    $args['order']    = 'DESC';
} else {
    $args['orderby']  = 'date';
    $args['order']    = 'DESC';
}

// Meta Queries
$meta_query = [];
if ($current_install) {
    $meta_query[] = [
        'key'     => '_game_installs_raw',
        'value'   => (int)$current_install,
        'compare' => '>=',
        'type'    => 'NUMERIC'
    ];
}
if (isset($_GET['price']) && $_GET['price'] === 'free') {
    $meta_query[] = [
        'key'     => '_game_price',
        'value'   => 'Free',
        'compare' => '='
    ];
}
if (isset($_GET['offline']) && $_GET['offline'] === '1') {
    $meta_query[] = [
        'key'     => '_game_is_offline',
        'value'   => '1',
        'compare' => '='
    ];
}
if (!empty($meta_query)) {
    $args['meta_query'] = $meta_query;
}

// Tax Query
if ($current_genre) {
    $args['tax_query'] = [
        [
            'taxonomy' => 'game_genre',
            'field'    => 'slug',
            'terms'    => $current_genre
        ]
    ];
}

$games_query = new WP_Query($args);

// Reusable card function (defined in front-page.php usually, but let's redefine or assume it's in functions.php)
if (!function_exists('steppa_game_card')) {
    function steppa_game_card($post_id, $badge = '') {
        $icon        = get_post_meta($post_id, '_game_icon_url', true);
        $rating      = get_post_meta($post_id, '_game_rating', true);
        $installs    = get_post_meta($post_id, '_game_installs', true);
        $price       = get_post_meta($post_id, '_game_price', true) ?: 'Free';
        $is_offline  = get_post_meta($post_id, '_game_is_offline', true);
        $genres      = get_the_terms($post_id, 'game_genre');
        $genre_name  = (!is_wp_error($genres) && !empty($genres)) ? $genres[0]->name : 'Game';
        $developer   = get_post_meta($post_id, '_game_developer', true);
        $screenshots = json_decode(get_post_meta($post_id, '_game_screenshots', true), true);
        $thumb       = (!empty($screenshots) && isset($screenshots[0])) ? $screenshots[0] : '';
    
        $badge_class = '';
        $badge_label = '';
        if ($badge === 'trending') { $badge_class = 'trending'; $badge_label = '🔥 Trending'; }
        elseif ($badge === 'new')  { $badge_class = 'new'; $badge_label = '🆕 New'; }
        elseif ($badge === 'top')  { $badge_class = 'top-rated'; $badge_label = '⭐ Top Rated'; }
    
        ob_start();
        ?>
        <a href="<?php echo esc_url(get_permalink($post_id)); ?>" class="game-card" aria-label="<?php echo esc_attr(get_the_title($post_id)); ?>">
            <div class="game-card-thumb">
                <?php if ($thumb): ?>
                <img src="<?php echo esc_url($thumb); ?>" alt="<?php echo esc_attr(get_the_title($post_id)); ?> screenshot" loading="lazy">
                <?php else: ?>
                <div style="position:absolute;inset:0;background:linear-gradient(135deg,var(--bg-card-h),var(--bg-3));display:flex;align-items:center;justify-content:center;font-size:3rem;">🎮</div>
                <?php endif; ?>
                <?php if ($badge_label): ?>
                <span class="game-card-badge <?php echo esc_attr($badge_class); ?>"><?php echo $badge_label; ?></span>
                <?php endif; ?>
            </div>
            <div class="game-card-body">
                <img class="game-card-icon"
                     src="<?php echo esc_url($icon ?: 'https://ui-avatars.com/api/?name=' . urlencode(get_the_title($post_id)) . '&background=7c3aed&color=fff&size=256&bold=true'); ?>"
                     alt="<?php echo esc_attr(get_the_title($post_id)); ?> icon"
                     loading="lazy"
                     onerror="this.src='https://ui-avatars.com/api/?name=Game&background=7c3aed&color=fff&size=256'"
                >
                <div class="game-card-title"><?php echo esc_html(get_the_title($post_id)); ?></div>
                <div class="game-card-dev"><?php echo esc_html($developer ?: 'Unknown Developer'); ?></div>
                <div class="game-card-meta">
                    <span class="game-card-rating">⭐ <?php echo esc_html(number_format((float)$rating, 1)); ?></span>
                    <span class="game-card-installs">📥 <?php echo esc_html($installs ?: 'N/A'); ?></span>
                </div>
                <div style="margin-top:8px;display:flex;gap:4px;flex-wrap:wrap;align-items:center;">
                    <span class="game-card-genre"><?php echo esc_html($genre_name); ?></span>
                    <?php if ($price === 'Free'): ?><span class="badge badge-free">Free</span><?php endif; ?>
                    <?php if ($is_offline === '1'): ?><span class="badge badge-offline">Offline</span><?php endif; ?>
                </div>
            </div>
        </a>
        <?php
        return ob_get_clean();
    }
}
?>

<div class="archive-hero">
    <div class="container text-center" style="display:flex;flex-direction:column;align-items:center;text-align:center;">
        <div class="archive-icon">🎮</div>
        <h1 class="archive-title">All Android Games</h1>
        <p class="archive-desc">Browse our complete collection of top-rated Android games. Filter by genre, rating, and installs to find your next favorite game.</p>
        <div class="archive-count"><?php echo wp_count_posts('android_game')->publish; ?> games available</div>
    </div>
</div>

<div class="container" style="padding:48px 0;">
    
    <!-- Filter Bar -->
    <form id="filter-form" class="filter-bar" method="GET" action="<?php echo esc_url(get_post_type_archive_link('android_game')); ?>">
        <div class="filter-row">
            
            <div class="filter-group">
                <label class="filter-label">Sort By</label>
                <select name="sort" class="filter-select">
                    <option value="newest" <?php selected($current_sort, 'newest'); ?>>Newest Added</option>
                    <option value="rating" <?php selected($current_sort, 'rating'); ?>>Highest Rated</option>
                    <option value="installs" <?php selected($current_sort, 'installs'); ?>>Most Installed</option>
                    <option value="trending" <?php selected($current_sort, 'trending'); ?>>Trending Now</option>
                </select>
            </div>

            <div class="filter-group">
                <label class="filter-label">Genre</label>
                <select name="genre" class="filter-select">
                    <option value="">All Genres</option>
                    <?php 
                    $genres = get_terms(['taxonomy' => 'game_genre', 'hide_empty' => true]);
                    foreach ($genres as $g): 
                    ?>
                    <option value="<?php echo esc_attr($g->slug); ?>" <?php selected($current_genre, $g->slug); ?>>
                        <?php echo esc_html($g->name); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="filter-group">
                <label class="filter-label">Price</label>
                <select name="price" class="filter-select">
                    <option value="">All Prices</option>
                    <option value="free" <?php selected(isset($_GET['price']) ? $_GET['price'] : '', 'free'); ?>>Free Only</option>
                </select>
            </div>

            <div class="filter-group">
                <label class="filter-label">Features</label>
                <select name="offline" class="filter-select">
                    <option value="">All Games</option>
                    <option value="1" <?php selected(isset($_GET['offline']) ? $_GET['offline'] : '', '1'); ?>>Offline Playable</option>
                </select>
            </div>

            <div class="filter-group">
                <button type="submit" class="filter-apply">Apply Filters</button>
            </div>
            
            <?php if (!empty($_GET)): ?>
            <div class="filter-group">
                <a href="<?php echo esc_url(get_post_type_archive_link('android_game')); ?>" style="color:var(--text-3);font-size:0.875rem;padding:10px;">Clear</a>
            </div>
            <?php endif; ?>
        </div>
    </form>

    <!-- Grid -->
    <?php if ($games_query->have_posts()): ?>
        <div class="games-grid" id="games-grid">
            <?php while ($games_query->have_posts()): $games_query->the_post(); ?>
                <?php echo steppa_game_card(get_the_ID()); ?>
            <?php endwhile; ?>
        </div>

        <?php if ($games_query->max_num_pages > 1): ?>
        <div class="load-more-wrap">
            <button id="load-more-btn" class="btn-load-more" 
                    data-genre="<?php echo esc_attr($current_genre); ?>" 
                    data-sort="<?php echo esc_attr($current_sort); ?>"
                    data-per_page="24"
                    data-install_filter="<?php echo esc_attr($current_install); ?>"
            >
                <span class="spinner"></span>
                <span class="btn-label">Load More Games</span>
            </button>
        </div>
        <?php endif; ?>

    <?php else: ?>
        <div style="text-align:center;padding:80px 20px;background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius);">
            <div style="font-size:3rem;margin-bottom:16px;">🔍</div>
            <h3 style="font-size:1.5rem;font-weight:700;margin-bottom:8px;">No games found</h3>
            <p style="color:var(--text-2);">Try adjusting your filters to find what you're looking for.</p>
            <a href="<?php echo esc_url(get_post_type_archive_link('android_game')); ?>" class="btn-secondary" style="margin-top:24px;">Clear Filters</a>
        </div>
    <?php endif; ?>
    <?php wp_reset_postdata(); ?>

</div>

<?php get_footer(); ?>
