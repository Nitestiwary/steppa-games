<?php get_header(); ?>

<div class="archive-header">
  <div class="container">
    <nav class="breadcrumb">
      <a href="<?php echo home_url('/'); ?>">Home</a>
      <span class="sep">›</span>
      <span class="current">Search Results</span>
    </nav>
    <h1>🔍 Search Results for: <?php echo esc_html(get_search_query()); ?></h1>
    <p>Explore matching mobile game titles and categories for your search query.</p>
    <span class="archive-count-badge"><?php echo $wp_query->found_posts; ?> matches found</span>
  </div>
</div>

<div class="container" style="padding: 24px 0 48px;">
  <div class="layout-wrap">
    <main>
      <?php if (have_posts()) : ?>
        <div class="games-grid">
          <?php while (have_posts()) : the_post(); ?>
            <?php echo steppa_card(get_the_ID()); ?>
          <?php endwhile; ?>
        </div>
        <div style="margin-top: 24px;">
          <?php echo paginate_links([
              'prev_text' => '← Prev',
              'next_text' => 'Next →'
          ]); ?>
        </div>
      <?php else : ?>
        <div class="empty-state">
          <div class="ico">🔍</div>
          <h3>No matches found</h3>
          <p>We couldn't find any games matching "<strong><?php echo esc_html(get_search_query()); ?></strong>". Try searching for a different game, developer, or genre.</p>
          <div style="margin-top: 20px; max-width: 400px; margin-left: auto; margin-right: auto;">
            <form action="<?php echo home_url('/'); ?>" method="get">
              <div class="header-search" style="max-width: 100%; position: relative;">
                <input type="text" name="s" placeholder="Search games..." value="<?php echo esc_attr(get_search_query()); ?>" style="background: rgba(0,0,0,0.05); color: #222; border: 1px solid var(--border); padding-left: 16px;">
                <button type="submit" style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); color: var(--text3);">🔍</button>
              </div>
            </form>
          </div>
        </div>
      <?php endif; ?>
    </main>

    <aside>
      <?php
      $genres = get_terms(['taxonomy' => 'game_genre', 'hide_empty' => true, 'number' => 12]);
      if (!is_wp_error($genres) && !empty($genres)) :
      ?>
        <div class="sidebar-widget">
          <div class="widget-head">🏷️ Top Categories</div>
          <div class="widget-body">
            <?php foreach ($genres as $g) : ?>
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
  </div>
</div>

<?php get_footer(); ?>
