<?php get_header(); ?>
<?php
$paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
$sort  = sanitize_text_field($_GET['sort'] ?? 'trending');
$args  = ['post_type'=>'android_game','posts_per_page'=>24,'paged'=>$paged];
if($sort==='rating') { $args['meta_key']='_game_rating'; $args['orderby']='meta_value_num'; $args['order']='DESC'; }
elseif($sort==='newest') { $args['orderby']='date'; $args['order']='DESC'; }
else { $args['meta_key']='_game_trending'; $args['orderby']='meta_value_num'; $args['order']='DESC'; }
$q = new WP_Query($args);
$all_genres = get_terms(['taxonomy'=>'game_genre','hide_empty'=>true]);
?>

<div class="archive-header">
  <div class="container">
    <nav class="breadcrumb"><a href="<?php echo home_url('/'); ?>">Home</a><span class="sep">›</span><span class="current">All Games</span></nav>
    <h1>🎮 All Android Games</h1>
    <p>Browse and download the best free Android games. Updated daily with new titles across all categories.</p>
    <span class="archive-count-badge"><?php echo $q->found_posts; ?> games available</span>
  </div>
</div>

<div class="filter-bar">
  <div class="container filter-inner">
    <span class="filter-label">Sort by:</span>
    <a href="?sort=trending" class="filter-tab <?php if($sort==='trending') echo 'active'; ?>">🔥 Trending</a>
    <a href="?sort=rating"   class="filter-tab <?php if($sort==='rating')   echo 'active'; ?>">⭐ Top Rated</a>
    <a href="?sort=newest"   class="filter-tab <?php if($sort==='newest')   echo 'active'; ?>">🆕 Newest</a>
  </div>
</div>

<div class="container">
<div class="layout-wrap">
  <main>
    <?php if($q->have_posts()): ?>
    <div class="games-grid">
      <?php while($q->have_posts()): $q->the_post(); echo steppa_card(get_the_ID()); endwhile; wp_reset_postdata(); ?>
    </div>
    <div style="margin-top:24px;">
      <?php echo paginate_links(['total'=>$q->max_num_pages,'current'=>$paged,'prev_text'=>'← Prev','next_text'=>'Next →']); ?>
    </div>
    <?php else: ?>
    <div class="empty-state"><div class="ico">🎮</div><h3>No games yet</h3><p>Check back soon — games are being added!</p></div>
    <?php endif; ?>
  </main>

  <aside>
    <?php if(!is_wp_error($all_genres) && !empty($all_genres)): ?>
    <div class="sidebar-widget">
      <div class="widget-head">🏷️ Categories</div>
      <div class="widget-body">
        <a href="<?php echo get_post_type_archive_link('android_game'); ?>" class="widget-link active">
          <span class="ico">🎮</span> All Games <span class="cnt"><?php echo $q->found_posts; ?></span>
        </a>
        <?php foreach($all_genres as $g): ?>
        <a href="<?php echo esc_url(get_term_link($g)); ?>" class="widget-link">
          <span class="ico"><?php echo steppa_genre_icon($g->name); ?></span>
          <?php echo esc_html($g->name); ?> <span class="cnt"><?php echo $g->count; ?></span>
        </a>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>
  </aside>
</div>
</div>

<?php get_footer(); ?>
