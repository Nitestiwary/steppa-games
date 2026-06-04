<?php
// header.php
$genres = get_terms(['taxonomy' => 'game_genre', 'hide_empty' => true, 'number' => 20]);
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo('charset'); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="index, follow">
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>

<!-- Mobile Overlay -->
<div class="overlay" id="overlay"></div>

<!-- Mobile Sidebar -->
<nav class="mobile-sidebar" id="mobile-sidebar" aria-label="Mobile Navigation">
  <div class="sidebar-header">
    <a class="sidebar-logo" href="<?php echo home_url('/'); ?>">⚡ Steppa</a>
    <button class="sidebar-close" id="sidebar-close" aria-label="Close menu">✕</button>
  </div>
  <div class="sidebar-search">
    <input type="text" id="mobile-search-input" placeholder="Search games..." autocomplete="off" aria-label="Search games">
  </div>
  <div class="sidebar-section-title">Categories</div>
  <?php if (!is_wp_error($genres) && !empty($genres)): foreach($genres as $g): ?>
  <a href="<?php echo esc_url(get_term_link($g)); ?>" class="sidebar-link">
    <span class="ico"><?php echo steppa_genre_icon($g->name); ?></span>
    <?php echo esc_html($g->name); ?>
    <span class="count"><?php echo $g->count; ?></span>
  </a>
  <?php endforeach; endif; ?>
  <div class="sidebar-section-title">Pages</div>
  <a href="<?php echo home_url('/about'); ?>" class="sidebar-link"><span class="ico">ℹ️</span> About Us</a>
  <a href="<?php echo home_url('/contact'); ?>" class="sidebar-link"><span class="ico">📧</span> Contact</a>
  <a href="<?php echo home_url('/privacy-policy'); ?>" class="sidebar-link"><span class="ico">🔒</span> Privacy Policy</a>
  <a href="<?php echo home_url('/terms'); ?>" class="sidebar-link"><span class="ico">📄</span> Terms of Service</a>
  <a href="<?php echo home_url('/dmca'); ?>" class="sidebar-link"><span class="ico">⚖️</span> DMCA</a>
</nav>

<!-- Site Header -->
<header class="site-header" id="site-header">
  <div class="container header-inner">
    <a class="site-logo" href="<?php echo home_url('/'); ?>">
      <span class="logo-icon">⚡</span> Steppa
    </a>

    <!-- Desktop Search -->
    <div class="header-search">
      <span class="search-ico">🔍</span>
      <input type="text" id="header-search-input" placeholder="Search games, categories..." autocomplete="off" aria-label="Search games">
      <div class="search-dropdown" id="search-dropdown" role="listbox"></div>
    </div>

    <!-- Desktop Nav -->
    <nav class="header-nav" aria-label="Primary Navigation">
      <a href="<?php echo home_url('/'); ?>" <?php if(is_front_page()) echo 'class="active"'; ?>>Home</a>
      <a href="<?php echo get_post_type_archive_link('android_game'); ?>" <?php if(is_post_type_archive('android_game')) echo 'class="active"'; ?>>All Games</a>
      <?php if (!is_wp_error($genres) && !empty($genres)): foreach(array_slice($genres,0,5) as $g): ?>
      <a href="<?php echo esc_url(get_term_link($g)); ?>" <?php if(is_tax('game_genre',$g->slug)) echo 'class="active"'; ?>><?php echo esc_html($g->name); ?></a>
      <?php endforeach; endif; ?>
    </nav>

    <!-- Hamburger -->
    <button class="hamburger" id="hamburger-btn" aria-label="Open menu" aria-expanded="false">
      <span></span><span></span><span></span>
    </button>
  </div>
</header>

<!-- Category Bar -->
<div class="cat-bar">
  <div class="container">
    <div class="cat-bar-inner">
      <a href="<?php echo get_post_type_archive_link('android_game'); ?>" class="cat-link <?php if(is_post_type_archive('android_game')) echo 'active'; ?>">🎮 All</a>
      <?php if (!is_wp_error($genres) && !empty($genres)): foreach($genres as $g): ?>
      <a href="<?php echo esc_url(get_term_link($g)); ?>" class="cat-link <?php if(is_tax('game_genre',$g->slug)) echo 'active'; ?>">
        <?php echo steppa_genre_icon($g->name) . ' ' . esc_html($g->name); ?>
      </a>
      <?php endforeach; endif; ?>
    </div>
  </div>
</div>
