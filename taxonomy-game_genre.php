<?php
/**
 * Steppa Discover — taxonomy-game_genre.php
 * Genre archive page
 */
get_header();

$term = get_queried_object();
$current_sort = isset($_GET['sort']) ? sanitize_text_field($_GET['sort']) : 'newest';

// Reusable card function fallback
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
        <a href="<?php echo esc_url(get_permalink($post_id)); ?>" class="game-card">
            <div class="game-card-thumb">
                <?php if ($thumb): ?>
                <img src="<?php echo esc_url($thumb); ?>" alt="<?php echo esc_attr(get_the_title($post_id)); ?> screenshot" loading="lazy">
                <?php else: ?>
                <div style="position:absolute;inset:0;background:linear-gradient(135deg,var(--bg-card-h),var(--bg-3));display:flex;align-items:center;justify-content:center;font-size:3rem;">🎮</div>
                <?php endif; ?>
            </div>
            <div class="game-card-body">
                <img class="game-card-icon" src="<?php echo esc_url($icon ?: 'https://ui-avatars.com/api/?name=' . urlencode(get_the_title($post_id)) . '&background=7c3aed&color=fff&size=256'); ?>" alt="" loading="lazy">
                <div class="game-card-title"><?php echo esc_html(get_the_title($post_id)); ?></div>
                <div class="game-card-dev"><?php echo esc_html($developer ?: 'Unknown'); ?></div>
                <div class="game-card-meta">
                    <span class="game-card-rating">⭐ <?php echo esc_html(number_format((float)$rating, 1)); ?></span>
                    <span class="game-card-installs">📥 <?php echo esc_html($installs ?: 'N/A'); ?></span>
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
        <div class="archive-icon"><?php echo function_exists('steppa_genre_icon') ? steppa_genre_icon($term->name) : '🎮'; ?></div>
        <h1 class="archive-title"><?php echo esc_html($term->name); ?> Games</h1>
        <p class="archive-desc">Discover the best and most popular <?php echo esc_html(strtolower($term->name)); ?> games for Android.</p>
        <div class="archive-count"><?php echo (int) $term->count; ?> games available</div>
    </div>
</div>

<div class="container" style="padding:48px 0;">
    
    <!-- Filter Bar -->
    <form id="filter-form" class="filter-bar" method="GET" action="<?php echo esc_url(get_term_link($term)); ?>">
        <div class="filter-row">
            <div class="filter-group">
                <label class="filter-label">Sort By</label>
                <select name="sort" class="filter-select" onchange="this.form.submit()">
                    <option value="newest" <?php selected($current_sort, 'newest'); ?>>Newest Added</option>
                    <option value="rating" <?php selected($current_sort, 'rating'); ?>>Highest Rated</option>
                    <option value="installs" <?php selected($current_sort, 'installs'); ?>>Most Installed</option>
                    <option value="trending" <?php selected($current_sort, 'trending'); ?>>Trending Now</option>
                </select>
            </div>
        </div>
    </form>

    <?php
    $args = [
        'post_type' => 'android_game',
        'posts_per_page' => 24,
        'paged' => get_query_var('paged') ? get_query_var('paged') : 1,
        'tax_query' => [
            ['taxonomy' => 'game_genre', 'field' => 'term_id', 'terms' => $term->term_id]
        ]
    ];
    if ($current_sort === 'rating') { $args['meta_key'] = '_game_rating'; $args['orderby'] = 'meta_value_num'; $args['order'] = 'DESC'; }
    elseif ($current_sort === 'installs') { $args['meta_key'] = '_game_installs_raw'; $args['orderby'] = 'meta_value_num'; $args['order'] = 'DESC'; }
    elseif ($current_sort === 'trending') { $args['meta_key'] = '_game_trending_score'; $args['orderby'] = 'meta_value_num'; $args['order'] = 'DESC'; }
    
    $genre_query = new WP_Query($args);
    ?>

    <?php if ($genre_query->have_posts()): ?>
        <div class="games-grid" id="games-grid">
            <?php while ($genre_query->have_posts()): $genre_query->the_post(); ?>
                <?php echo steppa_game_card(get_the_ID()); ?>
            <?php endwhile; ?>
        </div>
        
        <?php if ($genre_query->max_num_pages > 1): ?>
        <div class="load-more-wrap">
            <button id="load-more-btn" class="btn-load-more" 
                    data-genre="<?php echo esc_attr($term->slug); ?>" 
                    data-sort="<?php echo esc_attr($current_sort); ?>"
                    data-per_page="24"
            >
                <span class="spinner"></span>
                <span class="btn-label">Load More Games</span>
            </button>
        </div>
        <?php endif; ?>
        
    <?php else: ?>
        <p>No games found in this category.</p>
    <?php endif; wp_reset_postdata(); ?>

</div>

<?php get_footer(); ?>
