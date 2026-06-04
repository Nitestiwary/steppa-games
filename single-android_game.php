<?php get_header(); ?>
<?php
if (!have_posts()) { get_footer(); exit; }
the_post();
$pid       = get_the_ID();
$icon      = get_post_meta($pid, '_game_icon', true);
$dev       = get_post_meta($pid, '_game_dev', true) ?: 'Unknown';
$rating    = get_post_meta($pid, '_game_rating', true) ?: 4.5;
$reviews   = get_post_meta($pid, '_game_reviews', true) ?: '1K+';
$installs  = get_post_meta($pid, '_game_installs', true) ?: '1M+';
$size      = get_post_meta($pid, '_game_size', true) ?: '—';
$version   = get_post_meta($pid, '_game_version', true) ?: '—';
$updated   = get_post_meta($pid, '_game_updated', true) ?: '—';
$price     = get_post_meta($pid, '_game_price', true) ?: 'Free';
$offline   = get_post_meta($pid, '_game_offline', true);
$age       = get_post_meta($pid, '_game_age', true) ?: '3+';
$playstore = get_post_meta($pid, '_game_playstore', true);
$pkg       = get_post_meta($pid, '_game_pkg', true);
$how       = get_post_meta($pid, '_game_how_to_play', true);
$review    = get_post_meta($pid, '_game_editor_review', true);
$genres    = get_the_terms($pid, 'game_genre');
$fallback  = 'https://ui-avatars.com/api/?name='.urlencode(get_the_title()).'&background=3dbb85&color=fff&size=256&bold=true';
$icon_url  = $icon ?: $fallback;
$ps_link   = $playstore ?: ($pkg ? 'https://play.google.com/store/apps/details?id='.esc_attr($pkg) : '#');
?>

<!-- Game Header -->
<div class="game-header">
  <div class="container">
    <nav class="breadcrumb" aria-label="Breadcrumb">
      <a href="<?php echo home_url('/'); ?>">Home</a>
      <span class="sep">›</span>
      <a href="<?php echo get_post_type_archive_link('android_game'); ?>">Games</a>
      <?php if (!is_wp_error($genres) && !empty($genres)): ?>
      <span class="sep">›</span>
      <a href="<?php echo esc_url(get_term_link($genres[0])); ?>"><?php echo esc_html($genres[0]->name); ?></a>
      <?php endif; ?>
      <span class="sep">›</span>
      <span class="current"><?php the_title(); ?></span>
    </nav>

    <div class="game-header-inner">
      <div class="game-icon-wrap">
        <img class="game-icon-xl" src="<?php echo esc_url($icon_url); ?>"
             alt="<?php the_title(); ?>"
             onerror="this.src='<?php echo esc_url($fallback); ?>'">
      </div>
      <div class="game-meta-col">
        <h1 class="game-title-xl"><?php the_title(); ?></h1>
        <p class="game-developer"><?php echo esc_html($dev); ?></p>
        <div class="game-tags">
          <?php if (!is_wp_error($genres) && !empty($genres)): foreach($genres as $g): ?>
          <a href="<?php echo esc_url(get_term_link($g)); ?>" class="game-tag genre"><?php echo esc_html($g->name); ?></a>
          <?php endforeach; endif; ?>
          <span class="game-tag <?php echo $price === 'Free' ? 'free' : 'paid'; ?>"><?php echo esc_html($price); ?></span>
          <span class="game-tag">Android</span>
          <?php if ($offline): ?><span class="game-tag offline">Offline</span><?php endif; ?>
          <span class="game-tag">Age <?php echo esc_html($age); ?></span>
        </div>
        <div class="game-quick-stats">
          <div class="qs-item"><div class="qs-val"><?php echo esc_html($rating); ?></div><div class="qs-lbl">Rating</div></div>
          <div class="qs-item"><div class="qs-val"><?php echo esc_html($installs); ?></div><div class="qs-lbl">Installs</div></div>
          <div class="qs-item"><div class="qs-val"><?php echo esc_html($size); ?></div><div class="qs-lbl">Size</div></div>
          <div class="qs-item"><div class="qs-val"><?php echo esc_html($version); ?></div><div class="qs-lbl">Version</div></div>
          <div class="qs-item"><div class="qs-val"><?php echo esc_html($age); ?></div><div class="qs-lbl">Age</div></div>
          <div class="qs-item"><div class="qs-val">Android</div><div class="qs-lbl">Platform</div></div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Single Body -->
<div class="container">
<div class="single-layout">

  <!-- Main Content -->
  <main class="single-main">

    <!-- About -->
    <div class="content-section">
      <h2>📖 About <?php the_title(); ?></h2>
      <div class="prose">
        <?php
        $content = get_the_content();
        if (empty(trim(strip_tags($content)))) {
            $content = get_the_excerpt();
        }
        echo wpautop(wp_kses_post($content));
        ?>
      </div>
    </div>

    <!-- Editor's Review (original, not copied) -->
    <?php if ($review): ?>
    <div class="content-section">
      <h2>⭐ Editor's Review</h2>
      <div class="rating-row" style="margin-bottom:12px;">
        <span class="stars-lg"><?php echo steppa_rating_stars($rating); ?></span>
        <span class="rating-score"><?php echo number_format((float)$rating, 1); ?></span>
        <span class="rating-count">(<?php echo esc_html($reviews); ?> reviews)</span>
      </div>
      <div class="prose">
        <?php echo wpautop(wp_kses_post($review)); ?>
      </div>
    </div>
    <?php endif; ?>

    <!-- How to Play (original, not copied) -->
    <?php if ($how): ?>
    <div class="content-section">
      <h2>🎮 How to Play <?php the_title(); ?></h2>
      <div class="prose">
        <?php echo wpautop(wp_kses_post($how)); ?>
      </div>
    </div>
    <?php endif; ?>

    <!-- Game Details Table -->
    <div class="content-section">
      <h2>📋 Game Details</h2>
      <table class="details-table">
        <tr><td>Developer</td><td><?php echo esc_html($dev); ?></td></tr>
        <tr><td>Category</td><td>
          <?php if (!is_wp_error($genres) && !empty($genres)): foreach($genres as $i=>$g): if($i>0) echo ', '; ?>
          <a href="<?php echo esc_url(get_term_link($g)); ?>"><?php echo esc_html($g->name); ?></a>
          <?php endforeach; endif; ?>
        </td></tr>
        <tr><td>Platform</td><td>Android</td></tr>
        <tr><td>Price</td><td><?php echo esc_html($price); ?></td></tr>
        <tr><td>Installs</td><td><?php echo esc_html($installs); ?></td></tr>
        <tr><td>Size</td><td><?php echo esc_html($size); ?></td></tr>
        <tr><td>Version</td><td><?php echo esc_html($version); ?></td></tr>
        <tr><td>Updated</td><td><?php echo esc_html($updated); ?></td></tr>
        <tr><td>Age Rating</td><td><?php echo esc_html($age); ?>+</td></tr>
        <tr><td>Works Offline</td><td><?php echo $offline ? '✅ Yes' : '❌ No'; ?></td></tr>
        <?php if ($pkg): ?>
        <tr><td>Package ID</td><td><?php echo esc_html($pkg); ?></td></tr>
        <?php endif; ?>
      </table>
    </div>

    <!-- FAQ -->
    <div class="content-section">
      <h2>❓ Frequently Asked Questions</h2>
      <?php
      $faqs = [
        ["Is {$dev}'s " . get_the_title() . " free to download?", $price === 'Free' ? get_the_title().' is completely free to download from the Google Play Store. Some in-app purchases may be available.' : get_the_title().' is a paid app priced at '.$price.'. In-app purchases may also be available.'],
        ["Does ".get_the_title()." work offline?", $offline ? get_the_title()." can be played offline without an internet connection. Some features may still require connectivity." : get_the_title()." requires an active internet connection to play."],
        ["Is ".get_the_title()." safe to download?", get_the_title()." is available on the official Google Play Store and is safe to download. It is verified and regularly updated by ".esc_html($dev)."."],
        ["What Android version is needed for ".get_the_title()."?", get_the_title()." supports modern Android versions. Check the Google Play listing for the minimum Android version requirement."],
        ["How many people play ".get_the_title()."?", get_the_title()." has been installed by ".esc_html($installs)." users globally, making it one of the popular games in its category."],
      ];
      foreach ($faqs as $faq): ?>
      <div class="faq-item">
        <button class="faq-q" aria-expanded="false"><?php echo esc_html($faq[0]); ?><span class="arr">▼</span></button>
        <div class="faq-a"><?php echo esc_html($faq[1]); ?></div>
      </div>
      <?php endforeach; ?>
    </div>

    <!-- Similar Games -->
    <?php
    $sim_args = ['post_type'=>'android_game','posts_per_page'=>10,'post__not_in'=>[$pid],'orderby'=>'rand'];
    if (!is_wp_error($genres) && !empty($genres)) {
      $sim_args['tax_query'] = [['taxonomy'=>'game_genre','field'=>'term_id','terms'=>wp_list_pluck($genres,'term_id')]];
    }
    $sim_q = new WP_Query($sim_args);
    if ($sim_q->have_posts()):
    ?>
    <div class="section" style="padding:0;">
      <div class="section-head"><h2 class="section-title">👾 Similar Games</h2></div>
      <div class="games-grid">
        <?php while($sim_q->have_posts()): $sim_q->the_post(); echo steppa_card(get_the_ID()); endwhile; wp_reset_postdata(); ?>
      </div>
    </div>
    <?php endif; ?>

  </main>

  <!-- Sidebar -->
  <aside class="single-sidebar">
    <!-- Download Card -->
    <div class="download-card">
      <img class="dc-icon" src="<?php echo esc_url($icon_url); ?>" alt="<?php the_title(); ?>" onerror="this.src='<?php echo esc_url($fallback); ?>'">
      <div class="dc-name"><?php the_title(); ?></div>
      <div class="dc-dev"><?php echo esc_html($dev); ?></div>
      <div class="rating-row dc-rating">
        <span class="stars-sm"><?php echo steppa_rating_stars($rating); ?></span>
        <span><?php echo number_format((float)$rating,1); ?></span>
      </div>
      <a href="<?php echo esc_url($ps_link); ?>" target="_blank" rel="noopener" class="btn-download" id="download-btn-main">
        <span class="play-ico">▶</span> Get on Play Store
      </a>
      <a href="<?php echo esc_url($ps_link); ?>" target="_blank" rel="noopener" class="btn-download-sm">
        Free Download · <?php echo esc_html($price); ?>
      </a>
    </div>

    <!-- Quick Info Widget -->
    <div class="sidebar-widget">
      <div class="widget-head">📊 Quick Info</div>
      <div class="widget-body">
        <div class="widget-link"><span class="ico">⭐</span>Rating<span class="cnt"><?php echo number_format((float)$rating,1); ?></span></div>
        <div class="widget-link"><span class="ico">📥</span>Installs<span class="cnt"><?php echo esc_html($installs); ?></span></div>
        <div class="widget-link"><span class="ico">💾</span>Size<span class="cnt"><?php echo esc_html($size); ?></span></div>
        <div class="widget-link"><span class="ico">🔄</span>Version<span class="cnt"><?php echo esc_html($version); ?></span></div>
        <div class="widget-link"><span class="ico">📅</span>Updated<span class="cnt" style="font-size:.68rem"><?php echo esc_html($updated); ?></span></div>
        <div class="widget-link"><span class="ico">🔞</span>Age<span class="cnt"><?php echo esc_html($age); ?>+</span></div>
        <div class="widget-link"><span class="ico">📶</span>Offline<span class="cnt"><?php echo $offline ? 'Yes' : 'No'; ?></span></div>
      </div>
    </div>

    <!-- Category Widget -->
    <?php if (!is_wp_error($genres) && !empty($genres)): ?>
    <div class="sidebar-widget">
      <div class="widget-head">🏷️ Categories</div>
      <div class="widget-body">
        <?php foreach($genres as $g): ?>
        <a href="<?php echo esc_url(get_term_link($g)); ?>" class="widget-link">
          <span class="ico"><?php echo steppa_genre_icon($g->name); ?></span>
          <?php echo esc_html($g->name); ?>
          <span class="cnt"><?php echo $g->count; ?></span>
        </a>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>

  </aside>
</div><!-- .single-layout -->
</div><!-- .container -->

<?php get_footer(); ?>
