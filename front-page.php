<?php
/**
 * Steppa Discover — front-page.php
 * Homepage: Hero → Recently Added → Trending → Top Installed → Top Rated → Categories
 */
get_header();

// --- DATA QUERIES ---
$featured_games = new WP_Query([
    'post_type'      => 'android_game',
    'posts_per_page' => 6,
    'meta_key'       => '_game_featured',
    'meta_value'     => '1',
    'orderby'        => 'meta_value_num',
    'order'          => 'DESC',
]);

$recent_games = new WP_Query([
    'post_type'      => 'android_game',
    'posts_per_page' => 24,
    'orderby'        => 'date',
    'order'          => 'DESC',
]);

$trending_games = new WP_Query([
    'post_type'      => 'android_game',
    'posts_per_page' => 10,
    'meta_key'       => '_game_trending_score',
    'orderby'        => 'meta_value_num',
    'order'          => 'DESC',
]);

$top_rated = new WP_Query([
    'post_type'      => 'android_game',
    'posts_per_page' => 12,
    'meta_key'       => '_game_rating',
    'orderby'        => 'meta_value_num',
    'order'          => 'DESC',
    'meta_query'     => [['key' => '_game_rating', 'value' => '4.4', 'compare' => '>=', 'type' => 'DECIMAL']],
]);

$top_installed = new WP_Query([
    'post_type'      => 'android_game',
    'posts_per_page' => 12,
    'meta_key'       => '_game_installs_raw',
    'orderby'        => 'meta_value_num',
    'order'          => 'DESC',
]);

$all_genres = get_terms([
    'taxonomy'   => 'game_genre',
    'hide_empty' => true,
    'orderby'    => 'count',
    'order'      => 'DESC',
]);

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
?>

<!-- ===== HERO SECTION ===== -->
<section class="hero" aria-label="Hero">
    <div class="hero-bg"></div>
    <div class="hero-particles" aria-hidden="true">
        <?php
        $particle_colors = ['rgba(124,58,237,0.6)', 'rgba(37,99,235,0.5)', 'rgba(6,182,212,0.4)', 'rgba(139,92,246,0.5)'];
        $particle_sizes  = [4,6,8,5,7,4,6];
        for ($i = 0; $i < 15; $i++): ?>
        <div class="particle" style="
            width: <?php echo $particle_sizes[$i % 7]; ?>px;
            height: <?php echo $particle_sizes[$i % 7]; ?>px;
            background: <?php echo $particle_colors[$i % 4]; ?>;
            left: <?php echo rand(5, 95); ?>%;
            top: <?php echo rand(10, 90); ?>%;
            animation-delay: <?php echo ($i * 0.5); ?>s;
            animation-duration: <?php echo (6 + ($i % 4)); ?>s;
        "></div>
        <?php endfor; ?>
    </div>

    <div class="container">
        <div class="hero-content fade-in-up">
            <div class="hero-eyebrow">
                <span>🎮</span> #1 Android Game Discovery Platform
            </div>
            <h1 class="hero-title font-display">
                Discover The<br>
                <span class="gradient-text">Best Mobile Games</span><br>
                For Android
            </h1>
            <p class="hero-subtitle">
                Find trending, top-rated, and newly released Android games across action, RPG, puzzle, racing, and 11 more categories. Updated daily.
            </p>
            <div class="hero-actions">
                <a href="<?php echo esc_url(get_post_type_archive_link('android_game')); ?>" class="btn-primary">
                    <span>🔥</span> <span>Browse All Games</span>
                </a>
                <a href="<?php echo esc_url(get_post_type_archive_link('android_game')); ?>?sort=trending" class="btn-secondary">
                    📈 Trending Now
                </a>
            </div>
            <div class="hero-stats">
                <div class="hero-stat">
                    <span class="hero-stat-num"><?php echo number_format(wp_count_posts('android_game')->publish); ?>+</span>
                    <span class="hero-stat-label">Games Listed</span>
                </div>
                <div class="hero-stat">
                    <span class="hero-stat-num">15+</span>
                    <span class="hero-stat-label">Categories</span>
                </div>
                <div class="hero-stat">
                    <span class="hero-stat-num">Daily</span>
                    <span class="hero-stat-label">Updates</span>
                </div>
                <div class="hero-stat">
                    <span class="hero-stat-num">100%</span>
                    <span class="hero-stat-label">Free Discovery</span>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ===== RECENTLY ADDED ===== -->
<section class="section" aria-labelledby="recent-heading">
    <div class="container">
        <div class="section-header">
            <div>
                <h2 class="section-title" id="recent-heading">
                    <span class="section-title-icon">🆕</span>
                    Recently Added
                </h2>
                <p class="section-subtitle">The latest Android games added to our database</p>
            </div>
            <a href="<?php echo esc_url(get_post_type_archive_link('android_game')); ?>?sort=newest" class="view-all">
                View All <span>→</span>
            </a>
        </div>

        <div class="games-grid" id="games-grid">
            <?php if ($recent_games->have_posts()): while ($recent_games->have_posts()): $recent_games->the_post(); ?>
                <?php echo steppa_game_card(get_the_ID(), 'new'); ?>
            <?php endwhile; wp_reset_postdata(); endif; ?>
        </div>

        <?php if ($recent_games->max_num_pages > 1): ?>
        <div class="load-more-wrap">
            <button id="load-more-btn" class="btn-load-more" data-sort="newest" data-genre="" data-per_page="24">
                <span class="spinner"></span>
                <span class="btn-label">Load More Games</span>
            </button>
        </div>
        <?php endif; ?>
    </div>
</section>

<hr class="divider">

<!-- ===== TRENDING GAMES ===== -->
<section class="section" aria-labelledby="trending-heading">
    <div class="container">
        <div class="section-header">
            <div>
                <h2 class="section-title" id="trending-heading">
                    <span class="section-title-icon">🔥</span>
                    Trending Games
                </h2>
                <p class="section-subtitle">What gamers are playing right now</p>
            </div>
            <div class="tab-group">
                <button class="tab-btn trending-tab active" data-period="week">This Week</button>
                <button class="tab-btn trending-tab" data-period="month">This Month</button>
                <button class="tab-btn trending-tab" data-period="all">All Time</button>
            </div>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:32px;">
            <!-- Trending List (left) -->
            <div class="trending-container" id="trending-week">
                <div class="trending-list">
                    <?php
                    $trending_games->rewind_posts();
                    $rank = 1;
                    if ($trending_games->have_posts()): while ($trending_games->have_posts()): $trending_games->the_post();
                        $icon     = get_post_meta(get_the_ID(), '_game_icon_url', true);
                        $rating   = get_post_meta(get_the_ID(), '_game_rating', true);
                        $installs = get_post_meta(get_the_ID(), '_game_installs', true);
                        $genres   = get_the_terms(get_the_ID(), 'game_genre');
                        $genre    = (!is_wp_error($genres) && !empty($genres)) ? $genres[0]->name : '';
                    ?>
                    <a href="<?php the_permalink(); ?>" class="trending-item">
                        <span class="trending-rank <?php echo $rank <= 3 ? 'top-3' : ''; ?>">#<?php echo $rank; ?></span>
                        <img class="trending-icon"
                             src="<?php echo esc_url($icon ?: 'https://ui-avatars.com/api/?name=' . urlencode(get_the_title()) . '&background=7c3aed&color=fff&size=256'); ?>"
                             alt="<?php the_title_attribute(); ?>"
                             loading="lazy">
                        <div class="trending-info">
                            <div class="trending-title"><?php the_title(); ?></div>
                            <div class="trending-sub">
                                <span>⭐ <?php echo esc_html(number_format((float)$rating, 1)); ?></span>
                                <?php if ($genre): ?><span>📂 <?php echo esc_html($genre); ?></span><?php endif; ?>
                            </div>
                        </div>
                        <span class="trending-installs">📥 <?php echo esc_html($installs ?: 'N/A'); ?></span>
                    </a>
                    <?php $rank++; endwhile; wp_reset_postdata(); endif; ?>
                </div>
            </div>

            <!-- Top Rated Quick View (right) -->
            <div>
                <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius);padding:24px;">
                    <div style="font-weight:700;font-size:0.875rem;text-transform:uppercase;letter-spacing:0.5px;color:var(--text-2);margin-bottom:16px;">⭐ Top Rated Right Now</div>
                    <div style="display:flex;flex-direction:column;gap:12px;">
                        <?php
                        $quick_top = new WP_Query(['post_type' => 'android_game', 'posts_per_page' => 5, 'meta_key' => '_game_rating', 'orderby' => 'meta_value_num', 'order' => 'DESC']);
                        if ($quick_top->have_posts()): $qrank = 1; while ($quick_top->have_posts()): $quick_top->the_post();
                            $icon   = get_post_meta(get_the_ID(), '_game_icon_url', true);
                            $rating = get_post_meta(get_the_ID(), '_game_rating', true);
                        ?>
                        <a href="<?php the_permalink(); ?>" style="display:flex;align-items:center;gap:12px;padding:8px;border-radius:8px;transition:var(--trans-fast);" onmouseover="this.style.background='var(--bg-card-h)'" onmouseout="this.style.background='transparent'">
                            <span style="font-size:0.9rem;font-weight:700;color:var(--text-4);width:20px;"><?php echo $qrank; ?>.</span>
                            <img src="<?php echo esc_url($icon ?: 'https://ui-avatars.com/api/?name=' . urlencode(get_the_title()) . '&background=7c3aed&color=fff&size=256'); ?>" alt="<?php the_title_attribute(); ?>" style="width:40px;height:40px;border-radius:10px;object-fit:cover;flex-shrink:0;" loading="lazy">
                            <div style="flex:1;min-width:0;">
                                <div style="font-weight:600;font-size:0.875rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?php the_title(); ?></div>
                                <div style="font-size:0.78rem;color:var(--star);">⭐ <?php echo esc_html(number_format((float)$rating, 1)); ?></div>
                            </div>
                        </a>
                        <?php $qrank++; endwhile; wp_reset_postdata(); endif; ?>
                    </div>
                    <a href="<?php echo esc_url(get_post_type_archive_link('android_game')); ?>?sort=rating" class="view-all" style="margin-top:16px;display:inline-flex;">View All Top Rated →</a>
                </div>
            </div>
        </div>
    </div>
</section>

<hr class="divider">

<!-- ===== GAME CATEGORIES ===== -->
<section class="section" aria-labelledby="genres-heading">
    <div class="container">
        <div class="section-header">
            <div>
                <h2 class="section-title" id="genres-heading">
                    <span class="section-title-icon">🗂️</span>
                    Browse by Genre
                </h2>
                <p class="section-subtitle">Find your perfect game in any category</p>
            </div>
            <a href="<?php echo esc_url(get_post_type_archive_link('android_game')); ?>" class="view-all">All Categories →</a>
        </div>

        <div class="categories-grid">
            <?php
            $genre_icon_map = [
                'Action'        => ['icon' => '⚔️', 'color' => '#ef4444'],
                'Arcade'        => ['icon' => '🕹️', 'color' => '#f97316'],
                'Puzzle'        => ['icon' => '🧩', 'color' => '#eab308'],
                'RPG'           => ['icon' => '🐉', 'color' => '#8b5cf6'],
                'Racing'        => ['icon' => '🏎️', 'color' => '#3b82f6'],
                'Sports'        => ['icon' => '⚽', 'color' => '#10b981'],
                'Strategy'      => ['icon' => '♟️', 'color' => '#6366f1'],
                'Casual'        => ['icon' => '😊', 'color' => '#ec4899'],
                'Simulation'    => ['icon' => '🏙️', 'color' => '#14b8a6'],
                'Adventure'     => ['icon' => '🗺️', 'color' => '#84cc16'],
                'Educational'   => ['icon' => '📚', 'color' => '#06b6d4'],
                'Battle Royale' => ['icon' => '🎯', 'color' => '#dc2626'],
                'Open World'    => ['icon' => '🌍', 'color' => '#22c55e'],
                'Multiplayer'   => ['icon' => '👥', 'color' => '#a855f7'],
                'Offline'       => ['icon' => '📴', 'color' => '#64748b'],
            ];

            if (!is_wp_error($all_genres) && !empty($all_genres)):
                foreach ($all_genres as $genre):
                    $meta = $genre_icon_map[$genre->name] ?? ['icon' => '🎮', 'color' => '#7c3aed'];
            ?>
            <a href="<?php echo esc_url(get_term_link($genre)); ?>" class="category-card">
                <span class="category-icon"><?php echo $meta['icon']; ?></span>
                <div class="category-name"><?php echo esc_html($genre->name); ?></div>
                <div class="category-count"><?php echo (int)$genre->count; ?> games</div>
            </a>
            <?php endforeach; endif; ?>
        </div>
    </div>
</section>

<hr class="divider">

<!-- ===== TOP INSTALLED ===== -->
<section class="section" aria-labelledby="installed-heading">
    <div class="container">
        <div class="section-header">
            <div>
                <h2 class="section-title" id="installed-heading">
                    <span class="section-title-icon">📥</span>
                    Most Installed
                </h2>
                <p class="section-subtitle">Android games with the most downloads worldwide</p>
            </div>
            <div class="tab-group" role="tablist">
                <button class="tab-btn install-tab active" data-filter="" role="tab" aria-selected="true">All</button>
                <button class="tab-btn install-tab" data-filter="1000000000" role="tab">1B+</button>
                <button class="tab-btn install-tab" data-filter="100000000" role="tab">100M+</button>
                <button class="tab-btn install-tab" data-filter="10000000" role="tab">10M+</button>
                <button class="tab-btn install-tab" data-filter="1000000" role="tab">1M+</button>
            </div>
        </div>

        <div class="games-grid" id="installed-grid">
            <?php if ($top_installed->have_posts()): while ($top_installed->have_posts()): $top_installed->the_post(); ?>
                <?php echo steppa_game_card(get_the_ID()); ?>
            <?php endwhile; wp_reset_postdata(); endif; ?>
        </div>

        <div style="text-align:center;margin-top:32px;">
            <a href="<?php echo esc_url(get_post_type_archive_link('android_game')); ?>?sort=installs" class="btn-secondary">
                📥 View All Most Installed →
            </a>
        </div>
    </div>
</section>

<hr class="divider">

<!-- ===== TOP RATED ===== -->
<section class="section" aria-labelledby="rated-heading">
    <div class="container">
        <div class="section-header">
            <div>
                <h2 class="section-title" id="rated-heading">
                    <span class="section-title-icon">⭐</span>
                    Top Rated Games
                </h2>
                <p class="section-subtitle">Android games rated 4.4 and above by millions of players</p>
            </div>
            <a href="<?php echo esc_url(get_post_type_archive_link('android_game')); ?>?sort=rating" class="view-all">View All ⭐ →</a>
        </div>

        <div class="games-grid">
            <?php if ($top_rated->have_posts()): while ($top_rated->have_posts()): $top_rated->the_post(); ?>
                <?php echo steppa_game_card(get_the_ID(), 'top'); ?>
            <?php endwhile; wp_reset_postdata(); endif; ?>
        </div>
    </div>
</section>

<?php get_footer(); ?>
