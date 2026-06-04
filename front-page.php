<?php get_header(); ?>

<?php
// Gather genre list for nav
$genres = get_terms(['taxonomy' => 'game_genre', 'hide_empty' => true, 'number' => 20]);
?>

<!-- Hero -->
<section class="hero-section">
  <div class="container">
    <h1 class="hero-title">Download <span class="hl">Top Mobile Games</span><br>Free for Android</h1>
    <p class="hero-sub">Discover trending, top-rated, and new Android games across all categories. 100% safe downloads.</p>
    <div class="hero-stats">
      <?php
      $total = wp_count_posts('android_game')->publish;
      $genre_count = wp_count_terms(['taxonomy' => 'game_genre']);
      ?>
      <div class="hero-stat"><div class="num"><?php echo number_format($total); ?>+</div><div class="lbl">Games</div></div>
      <div class="hero-stat"><div class="num"><?php echo $genre_count; ?>+</div><div class="lbl">Categories</div></div>
      <div class="hero-stat"><div class="num">100%</div><div class="lbl">Safe & Free</div></div>
      <div class="hero-stat"><div class="num">1B+</div><div class="lbl">Downloads</div></div>
    </div>
  </div>
</section>

<div class="container">

<!-- Featured / Trending -->
<?php
$trending_q = new WP_Query(['post_type'=>'android_game','posts_per_page'=>12,'meta_key'=>'_game_trending','orderby'=>'meta_value_num','order'=>'DESC']);
if ($trending_q->have_posts()):
?>
<section class="section">
  <div class="section-head">
    <h2 class="section-title">🔥 Trending Now</h2>
    <a href="<?php echo get_post_type_archive_link('android_game'); ?>" class="view-all">View All</a>
  </div>
  <div class="scroll-row">
    <?php while($trending_q->have_posts()): $trending_q->the_post(); echo steppa_card(get_the_ID(),'trending'); endwhile; wp_reset_postdata(); ?>
  </div>
</section>
<?php endif; ?>

<!-- By Genre Sections -->
<?php if (!is_wp_error($genres) && !empty($genres)):
  foreach ($genres as $genre):
    $gq = new WP_Query(['post_type'=>'android_game','posts_per_page'=>10,'tax_query'=>[['taxonomy'=>'game_genre','field'=>'term_id','terms'=>$genre->term_id]],'meta_key'=>'_game_trending','orderby'=>'meta_value_num','order'=>'DESC']);
    if (!$gq->have_posts()) continue;
?>
<section class="section">
  <div class="section-head">
    <h2 class="section-title"><?php echo steppa_genre_icon($genre->name) . ' ' . esc_html($genre->name); ?></h2>
    <a href="<?php echo get_term_link($genre); ?>" class="view-all">View All</a>
  </div>
  <div class="scroll-row">
    <?php while($gq->have_posts()): $gq->the_post(); echo steppa_card(get_the_ID()); endwhile; wp_reset_postdata(); ?>
  </div>
</section>
<?php endforeach; endif; ?>

<!-- All Games Grid -->
<section class="section">
  <div class="section-head">
    <h2 class="section-title">🎮 All Games</h2>
  </div>
  <div class="games-grid" id="main-games-grid">
    <?php
    $all_q = new WP_Query(['post_type'=>'android_game','posts_per_page'=>20,'orderby'=>'date','order'=>'DESC']);
    if ($all_q->have_posts()):
      while($all_q->have_posts()): $all_q->the_post(); echo steppa_card(get_the_ID()); endwhile;
      wp_reset_postdata();
    endif;
    ?>
  </div>
  <div class="load-more-wrap">
    <button class="btn-load-more" id="load-more-btn" data-page="2" data-genre="" data-sort="newest">
      <span class="spin"></span> Load More Games
    </button>
  </div>
</section>

</div><!-- .container -->

<?php get_footer(); ?>
