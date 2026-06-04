<?php get_header(); ?>
<?php
$term     = get_queried_object();
$genre    = $term ? $term->name : 'Games';
$paged    = (get_query_var('paged')) ? get_query_var('paged') : 1;
$sort     = sanitize_text_field($_GET['sort'] ?? 'trending');
$sort_args = ['meta_key'=>'_game_trending','orderby'=>'meta_value_num','order'=>'DESC'];
if($sort==='rating')  $sort_args = ['meta_key'=>'_game_rating','orderby'=>'meta_value_num','order'=>'DESC'];
if($sort==='newest')  $sort_args = ['orderby'=>'date','order'=>'DESC'];
$q = new WP_Query(array_merge(['post_type'=>'android_game','posts_per_page'=>24,'paged'=>$paged,'tax_query'=>[['taxonomy'=>'game_genre','field'=>'term_id','terms'=>$term->term_id]]],$sort_args));
?>

<div class="archive-header">
  <div class="container">
    <nav class="breadcrumb"><a href="<?php echo home_url('/'); ?>">Home</a><span class="sep">›</span><a href="<?php echo get_post_type_archive_link('android_game'); ?>">Games</a><span class="sep">›</span><span class="current"><?php echo esc_html($genre); ?></span></nav>
    <h1><?php echo steppa_genre_icon($genre).' '.esc_html($genre); ?> Games</h1>
    <p>Download the best free <?php echo esc_html(strtolower($genre)); ?> Android games. Top-rated, trending and newest titles.</p>
    <span class="archive-count-badge"><?php echo $q->found_posts; ?> games found</span>
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
      <div class="empty-state"><div class="ico">🎮</div><h3>No games found</h3><p>Try a different category or check back later.</p></div>
      <?php endif; ?>
    </main>

    <aside>
      <?php
      $all_genres = get_terms(['taxonomy'=>'game_genre','hide_empty'=>true]);
      if(!is_wp_error($all_genres) && !empty($all_genres)):
      ?>
      <div class="sidebar-widget">
        <div class="widget-head">🏷️ All Categories</div>
        <div class="widget-body">
          <?php foreach($all_genres as $g): ?>
          <a href="<?php echo esc_url(get_term_link($g)); ?>" class="widget-link <?php if($term && $term->term_id===$g->term_id) echo 'active'; ?>">
            <span class="ico"><?php echo steppa_genre_icon($g->name); ?></span>
            <?php echo esc_html($g->name); ?>
            <span class="cnt"><?php echo $g->count; ?></span>
          </a>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endif; ?>
    </aside>
  </div>
</div>

<?php get_footer(); ?>
