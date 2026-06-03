<?php
/**
 * Steppa Discover — single-android_game.php
 * Premium single game page with full metadata, screenshots, FAQ, similar games
 */
get_header();

if (!have_posts()) {
    echo '<div class="container"><p style="padding:60px 0;text-align:center;color:var(--text-2);">Game not found.</p></div>';
    get_footer();
    exit;
}

the_post();
$post_id   = get_the_ID();
$icon      = get_post_meta($post_id, '_game_icon_url', true);
$rating    = (float) get_post_meta($post_id, '_game_rating', true);
$reviews   = get_post_meta($post_id, '_game_reviews', true);
$installs  = get_post_meta($post_id, '_game_installs', true);
$size      = get_post_meta($post_id, '_game_size', true);
$version   = get_post_meta($post_id, '_game_version', true);
$updated   = get_post_meta($post_id, '_game_updated', true);
$price     = get_post_meta($post_id, '_game_price', true) ?: 'Free';
$is_offline = get_post_meta($post_id, '_game_is_offline', true);
$developer  = get_post_meta($post_id, '_game_developer', true);
$package_id = get_post_meta($post_id, '_game_package_id', true);
$playstore  = get_post_meta($post_id, '_game_playstore_url', true);
$trending   = get_post_meta($post_id, '_game_trending_score', true);
$screenshots_json = get_post_meta($post_id, '_game_screenshots', true);
$screenshots = $screenshots_json ? json_decode($screenshots_json, true) : [];
$genres     = get_the_terms($post_id, 'game_genre');
$genre_names = [];
if (!is_wp_error($genres) && !empty($genres)) {
    foreach ($genres as $g) $genre_names[] = $g->name;
}

// Star rating HTML
function steppa_stars($rating) {
    $html = '<span class="stars-row" aria-label="Rating: ' . number_format($rating, 1) . ' out of 5">';
    for ($i = 1; $i <= 5; $i++) {
        if ($rating >= $i) $html .= '<span aria-hidden="true">⭐</span>';
        elseif ($rating >= $i - 0.5) $html .= '<span aria-hidden="true" style="opacity:0.6">⭐</span>';
        else $html .= '<span aria-hidden="true" style="opacity:0.2">⭐</span>';
    }
    $html .= '<span class="star-count">(' . number_format((float)$rating, 1) . ')</span></span>';
    return $html;
}

// FAQs
$faqs = [
    [
        'q' => 'Is ' . get_the_title() . ' free to play on Android?',
        'a' => ($price === 'Free')
            ? get_the_title() . ' is completely free to download and play on Android. The game may include optional in-app purchases for cosmetic items or premium features, but the core gameplay experience is available at no cost.'
            : get_the_title() . ' is a premium game priced at ' . $price . ' on the Google Play Store. Once purchased, you get full access to all content without any additional required purchases.',
    ],
    [
        'q' => 'Can I play ' . get_the_title() . ' offline?',
        'a' => ($is_offline === '1')
            ? 'Yes! ' . get_the_title() . ' supports offline play. You can enjoy the full gaming experience without an active internet connection, making it perfect for travel, commutes, or areas with limited connectivity.'
            : get_the_title() . ' requires an active internet connection to play. This is because the game features real-time multiplayer elements, cloud saves, and live content updates that require server connectivity.',
    ],
    [
        'q' => 'What Android version is required for ' . get_the_title() . '?',
        'a' => get_the_title() . ' requires Android 5.0 (Lollipop) or later to run smoothly. For the best experience, we recommend using a device running Android 8.0 or above with at least 3GB of RAM and a modern processor.',
    ],
    [
        'q' => 'How large is the download size for ' . get_the_title() . '?',
        'a' => ($size)
            ? 'The initial download size of ' . get_the_title() . ' is approximately ' . $size . '. Additional game data may be downloaded after installation, so make sure you have sufficient storage space and a stable Wi-Fi connection during installation.'
            : 'The download size varies depending on your device and Android version. We recommend ensuring you have at least 500MB of free storage space before downloading ' . get_the_title() . '.',
    ],
    [
        'q' => 'Where can I download ' . get_the_title() . ' safely?',
        'a' => 'The safest and most reliable way to download ' . get_the_title() . ' is through the official Google Play Store. Steppa links directly to the official Play Store listing to ensure you get the authentic, malware-free version of the game.',
    ],
];
?>

<!-- ===== BREADCRUMBS ===== -->
<div style="background:var(--bg-2);border-bottom:1px solid var(--border);">
    <div class="container">
        <nav class="breadcrumbs" aria-label="Breadcrumb">
            <a href="<?php echo esc_url(home_url('/')); ?>">🏠 Home</a>
            <span class="sep" aria-hidden="true">›</span>
            <a href="<?php echo esc_url(get_post_type_archive_link('android_game')); ?>">Games</a>
            <?php if (!empty($genre_names)): ?>
            <span class="sep" aria-hidden="true">›</span>
            <a href="<?php echo esc_url(get_term_link($genres[0])); ?>"><?php echo esc_html($genre_names[0]); ?></a>
            <?php endif; ?>
            <span class="sep" aria-hidden="true">›</span>
            <span class="current" aria-current="page"><?php the_title(); ?></span>
        </nav>
    </div>
</div>

<!-- ===== GAME HERO ===== -->
<div class="game-hero">
    <div class="container">
        <div class="game-hero-inner">

            <!-- Game Icon -->
            <img class="game-icon-large"
                 src="<?php echo esc_url($icon ?: 'https://ui-avatars.com/api/?name=' . urlencode(get_the_title()) . '&background=7c3aed&color=fff&size=256&bold=true'); ?>"
                 alt="<?php the_title_attribute(); ?> icon"
                 onerror="this.src='https://ui-avatars.com/api/?name=Game&background=7c3aed&color=fff&size=256'"
            >

            <!-- Game Info -->
            <div class="game-hero-info">
                <!-- Genre Tags -->
                <div class="game-genre-tags">
                    <?php foreach ($genre_names as $gn): ?>
                    <?php
                        $genre_obj = get_term_by('name', $gn, 'game_genre');
                        $genre_link = $genre_obj ? get_term_link($genre_obj) : '#';
                    ?>
                    <a href="<?php echo esc_url($genre_link); ?>" class="genre-tag"><?php echo esc_html($gn); ?></a>
                    <?php endforeach; ?>
                    <?php if ($price !== 'Free'): ?><span class="badge badge-paid">Paid</span><?php else: ?><span class="badge badge-free">Free</span><?php endif; ?>
                    <?php if ($is_offline === '1'): ?><span class="badge badge-offline">Offline</span><?php endif; ?>
                </div>

                <h1 class="game-title-large"><?php the_title(); ?></h1>
                <p class="game-developer-link">
                    🏢 <?php echo esc_html($developer ?: 'Unknown Developer'); ?>
                </p>

                <!-- Stats Row -->
                <div class="game-stats-row">
                    <div class="game-stat">
                        <span class="game-stat-value" style="color:var(--star);">
                            <?php echo steppa_stars($rating); ?>
                        </span>
                        <span class="game-stat-label">Rating</span>
                    </div>
                    <?php if ($reviews): ?>
                    <div class="game-stat">
                        <span class="game-stat-value">💬 <?php echo esc_html($reviews); ?></span>
                        <span class="game-stat-label">Reviews</span>
                    </div>
                    <?php endif; ?>
                    <?php if ($installs): ?>
                    <div class="game-stat">
                        <span class="game-stat-value" style="color:var(--green);">📥 <?php echo esc_html($installs); ?></span>
                        <span class="game-stat-label">Installs</span>
                    </div>
                    <?php endif; ?>
                    <?php if ($size): ?>
                    <div class="game-stat">
                        <span class="game-stat-value">📦 <?php echo esc_html($size); ?></span>
                        <span class="game-stat-label">Size</span>
                    </div>
                    <?php endif; ?>
                    <div class="game-stat">
                        <span class="game-stat-value"><?php echo $price === 'Free' ? '🆓 Free' : '💰 ' . esc_html($price); ?></span>
                        <span class="game-stat-label">Price</span>
                    </div>
                </div>
            </div>

            <!-- CTA Actions -->
            <div class="game-hero-actions" style="display:flex;flex-direction:column;gap:12px;align-items:flex-start;">
                <?php if ($playstore): ?>
                <a href="<?php echo esc_url($playstore); ?>" target="_blank" rel="noopener nofollow" class="btn-playstore" id="playstore-btn">
                    <span style="font-size:1.4rem;">▶</span>
                    <span>
                        <span style="font-size:0.7rem;display:block;opacity:0.8;">GET IT ON</span>
                        Google Play
                    </span>
                </a>
                <?php endif; ?>
                <div class="share-row">
                    <button class="share-btn share-twitter" data-share="twitter" aria-label="Share on Twitter">🐦 Tweet</button>
                    <button class="share-btn share-copy" data-share="copy" aria-label="Copy link">🔗 Copy Link</button>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- ===== MAIN CONTENT ===== -->
<div class="container" style="padding-top:48px;padding-bottom:48px;">
    <div class="page-layout">

        <!-- Main Column -->
        <div>

            <!-- Screenshots -->
            <?php if (!empty($screenshots)): ?>
            <section aria-labelledby="screenshots-heading" style="margin-bottom:48px;">
                <h2 style="font-size:1.3rem;font-weight:700;margin-bottom:16px;" id="screenshots-heading">📸 Screenshots</h2>
                <div class="screenshots-row" aria-label="Game screenshots">
                    <?php foreach ($screenshots as $screenshot): ?>
                    <img class="screenshot-img"
                         src="<?php echo esc_url($screenshot); ?>"
                         alt="<?php the_title_attribute(); ?> screenshot"
                         loading="lazy"
                    >
                    <?php endforeach; ?>
                </div>
            </section>
            <?php endif; ?>

            <!-- Description -->
            <section aria-labelledby="desc-heading" style="margin-bottom:48px;">
                <h2 style="font-size:1.3rem;font-weight:700;margin-bottom:16px;" id="desc-heading">📖 About <?php the_title(); ?></h2>
                <div style="color:var(--text-2);line-height:1.8;font-size:0.95rem;">
                    <?php the_content(); ?>
                </div>
            </section>

            <!-- Game Details Table -->
            <section aria-labelledby="details-heading" style="margin-bottom:48px;">
                <h2 style="font-size:1.3rem;font-weight:700;margin-bottom:16px;" id="details-heading">🔍 Game Details</h2>
                <table class="details-table">
                    <tbody>
                        <?php if ($developer): ?>
                        <tr>
                            <td>Developer</td>
                            <td><?php echo esc_html($developer); ?></td>
                        </tr>
                        <?php endif; ?>
                        <?php if (!empty($genre_names)): ?>
                        <tr>
                            <td>Genre</td>
                            <td><?php echo esc_html(implode(', ', $genre_names)); ?></td>
                        </tr>
                        <?php endif; ?>
                        <?php if ($version): ?>
                        <tr>
                            <td>Latest Version</td>
                            <td><?php echo esc_html($version); ?></td>
                        </tr>
                        <?php endif; ?>
                        <?php if ($updated): ?>
                        <tr>
                            <td>Last Updated</td>
                            <td><?php echo esc_html($updated); ?></td>
                        </tr>
                        <?php endif; ?>
                        <?php if ($size): ?>
                        <tr>
                            <td>Download Size</td>
                            <td><?php echo esc_html($size); ?></td>
                        </tr>
                        <?php endif; ?>
                        <tr>
                            <td>Price</td>
                            <td><?php echo esc_html($price); ?></td>
                        </tr>
                        <tr>
                            <td>Offline Play</td>
                            <td><?php echo $is_offline === '1' ? '✅ Yes' : '❌ Requires Internet'; ?></td>
                        </tr>
                        <?php if ($package_id): ?>
                        <tr>
                            <td>Package ID</td>
                            <td style="font-family:monospace;font-size:0.85rem;"><?php echo esc_html($package_id); ?></td>
                        </tr>
                        <?php endif; ?>
                        <?php if ($rating): ?>
                        <tr>
                            <td>Play Store Rating</td>
                            <td>⭐ <?php echo esc_html(number_format($rating, 1)); ?> / 5.0 <?php echo $reviews ? '(' . esc_html($reviews) . ' reviews)' : ''; ?></td>
                        </tr>
                        <?php endif; ?>
                        <?php if ($installs): ?>
                        <tr>
                            <td>Total Installs</td>
                            <td><?php echo esc_html($installs); ?></td>
                        </tr>
                        <?php endif; ?>
                        <tr>
                            <td>Platform</td>
                            <td>Android (Google Play Store)</td>
                        </tr>
                    </tbody>
                </table>
            </section>

            <!-- FAQ Section -->
            <section aria-labelledby="faq-heading" style="margin-bottom:48px;">
                <h2 style="font-size:1.3rem;font-weight:700;margin-bottom:16px;" id="faq-heading">❓ Frequently Asked Questions</h2>
                <div class="faq-list">
                    <?php foreach ($faqs as $i => $faq): ?>
                    <div class="faq-item">
                        <button class="faq-question <?php echo $i === 0 ? 'open' : ''; ?>" aria-expanded="<?php echo $i === 0 ? 'true' : 'false'; ?>">
                            <span><?php echo esc_html($faq['q']); ?></span>
                            <svg class="faq-arrow" width="20" height="20" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"/>
                            </svg>
                        </button>
                        <div class="faq-answer <?php echo $i === 0 ? 'open' : ''; ?>">
                            <div class="faq-answer-inner"><?php echo esc_html($faq['a']); ?></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </section>

        </div>

        <!-- Sidebar -->
        <aside class="sidebar-sticky">
            <!-- Play Store CTA Card -->
            <?php if ($playstore): ?>
            <div class="sidebar-card" style="text-align:center;background:linear-gradient(135deg,rgba(124,58,237,0.1),rgba(37,99,235,0.1));border-color:rgba(124,58,237,0.2);">
                <img src="<?php echo esc_url($icon ?: ''); ?>" alt="<?php the_title_attribute(); ?>" style="width:80px;height:80px;border-radius:18px;object-fit:cover;margin:0 auto 12px;border:2px solid var(--border-2);" onerror="this.src='https://ui-avatars.com/api/?name=Game&background=7c3aed&color=fff&size=256'">
                <div style="font-weight:700;font-size:1rem;margin-bottom:4px;"><?php the_title(); ?></div>
                <div style="font-size:0.8rem;color:var(--text-2);margin-bottom:12px;"><?php echo esc_html($developer); ?></div>
                <?php echo steppa_stars($rating); ?>
                <div style="margin-top:4px;font-size:0.8rem;color:var(--text-3);"><?php echo esc_html($reviews ?: ''); ?> ratings</div>
                <a href="<?php echo esc_url($playstore); ?>" target="_blank" rel="noopener nofollow" class="btn-playstore" style="margin-top:16px;width:100%;justify-content:center;">
                    <span>▶</span>
                    <span>
                        <span style="font-size:0.65rem;display:block;opacity:0.8;">GET IT ON</span>
                        Google Play
                    </span>
                </a>
                <?php if ($price === 'Free'): ?>
                <div style="margin-top:8px;font-size:0.78rem;color:var(--green);font-weight:600;">✓ Free Download</div>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <!-- Quick Stats Card -->
            <div class="sidebar-card">
                <div class="sidebar-card-title">📊 Game Stats</div>
                <div style="display:flex;flex-direction:column;gap:10px;">
                    <?php
                    $stats = [
                        ['label' => 'Rating',   'val' => number_format($rating, 1) . ' / 5.0', 'icon' => '⭐'],
                        ['label' => 'Installs',  'val' => $installs,   'icon' => '📥'],
                        ['label' => 'Size',      'val' => $size,        'icon' => '📦'],
                        ['label' => 'Version',   'val' => $version,     'icon' => '🔢'],
                        ['label' => 'Updated',   'val' => $updated,     'icon' => '📅'],
                        ['label' => 'Offline',   'val' => $is_offline === '1' ? 'Yes ✅' : 'No ❌', 'icon' => '📴'],
                    ];
                    foreach ($stats as $s):
                        if (!$s['val']) continue;
                    ?>
                    <div style="display:flex;justify-content:space-between;align-items:center;padding:8px 0;border-bottom:1px solid var(--border);">
                        <span style="font-size:0.82rem;color:var(--text-2);"><?php echo $s['icon']; ?> <?php echo $s['label']; ?></span>
                        <span style="font-size:0.82rem;font-weight:600;"><?php echo esc_html($s['val']); ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Genre Tags Card -->
            <?php if (!empty($genre_names)): ?>
            <div class="sidebar-card">
                <div class="sidebar-card-title">📂 Categories</div>
                <div style="display:flex;flex-wrap:wrap;gap:8px;">
                    <?php foreach ($genres as $g): ?>
                    <a href="<?php echo esc_url(get_term_link($g)); ?>" class="genre-tag">
                        <?php echo function_exists('steppa_genre_icon') ? steppa_genre_icon($g->name) : '🎮'; ?>
                        <?php echo esc_html($g->name); ?>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </aside>

    </div>

    <!-- Similar Games -->
    <?php
    $similar_query = new WP_Query([
        'post_type'      => 'android_game',
        'posts_per_page' => 8,
        'post__not_in'   => [$post_id],
        'orderby'        => 'rand',
        'tax_query'      => [
            [
                'taxonomy' => 'game_genre',
                'field'    => 'slug',
                'terms'    => !is_wp_error($genres) ? array_map(function($g) { return $g->slug; }, $genres) : [],
            ]
        ],
    ]);
    ?>
    <?php if ($similar_query->have_posts()): ?>
    <section style="margin-top:64px;" aria-labelledby="similar-heading">
        <div class="section-header">
            <h2 class="section-title" id="similar-heading">
                <span class="section-title-icon">🎯</span>
                Similar Games You'll Love
            </h2>
            <?php if (!empty($genre_names)): ?>
            <a href="<?php echo esc_url(get_term_link($genres[0])); ?>" class="view-all">More <?php echo esc_html($genre_names[0]); ?> →</a>
            <?php endif; ?>
        </div>
        <div class="games-grid">
            <?php while ($similar_query->have_posts()): $similar_query->the_post(); ?>
            <?php
            $s_icon     = get_post_meta(get_the_ID(), '_game_icon_url', true);
            $s_rating   = get_post_meta(get_the_ID(), '_game_rating', true);
            $s_installs = get_post_meta(get_the_ID(), '_game_installs', true);
            $s_dev      = get_post_meta(get_the_ID(), '_game_developer', true);
            $s_price    = get_post_meta(get_the_ID(), '_game_price', true) ?: 'Free';
            $s_genres   = get_the_terms(get_the_ID(), 'game_genre');
            $s_genre    = (!is_wp_error($s_genres) && !empty($s_genres)) ? $s_genres[0]->name : 'Game';
            $s_screens  = json_decode(get_post_meta(get_the_ID(), '_game_screenshots', true), true);
            $s_thumb    = (!empty($s_screens)) ? $s_screens[0] : '';
            ?>
            <a href="<?php the_permalink(); ?>" class="game-card">
                <div class="game-card-thumb">
                    <?php if ($s_thumb): ?>
                    <img src="<?php echo esc_url($s_thumb); ?>" alt="<?php the_title_attribute(); ?>" loading="lazy">
                    <?php else: ?>
                    <div style="position:absolute;inset:0;background:linear-gradient(135deg,var(--bg-card-h),var(--bg-3));display:flex;align-items:center;justify-content:center;font-size:3rem;">🎮</div>
                    <?php endif; ?>
                </div>
                <div class="game-card-body">
                    <img class="game-card-icon" src="<?php echo esc_url($s_icon ?: 'https://ui-avatars.com/api/?name=' . urlencode(get_the_title()) . '&background=7c3aed&color=fff&size=256'); ?>" alt="<?php the_title_attribute(); ?>" loading="lazy" onerror="this.src='https://ui-avatars.com/api/?name=Game&background=7c3aed&color=fff&size=256'">
                    <div class="game-card-title"><?php the_title(); ?></div>
                    <div class="game-card-dev"><?php echo esc_html($s_dev ?: 'Unknown'); ?></div>
                    <div class="game-card-meta">
                        <span class="game-card-rating">⭐ <?php echo esc_html(number_format((float)$s_rating, 1)); ?></span>
                        <span class="game-card-installs">📥 <?php echo esc_html($s_installs ?: 'N/A'); ?></span>
                    </div>
                    <div style="margin-top:8px;">
                        <span class="game-card-genre"><?php echo esc_html($s_genre); ?></span>
                    </div>
                </div>
            </a>
            <?php endwhile; wp_reset_postdata(); ?>
        </div>
    </section>
    <?php endif; ?>

</div>

<?php get_footer(); ?>
