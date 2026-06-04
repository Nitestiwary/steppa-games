<?php
// footer.php
$genres = get_terms(['taxonomy' => 'game_genre', 'hide_empty' => true, 'number' => 10]);
?>
<footer class="site-footer">
  <div class="container">
    <div class="footer-grid">
      <div>
        <div class="footer-logo">⚡ Steppa</div>
        <p class="footer-tagline">Your go-to source for discovering and downloading the best free Android games. Browse 1000+ top-rated mobile games across all genres.</p>
        <p style="font-size:.78rem;color:rgba(255,255,255,.4);">© <?php echo date('Y'); ?> steppa.in — All rights reserved.</p>
      </div>
      <div class="footer-col">
        <h4>Categories</h4>
        <ul>
          <?php if (!is_wp_error($genres) && !empty($genres)): foreach(array_slice($genres,0,8) as $g): ?>
          <li><a href="<?php echo esc_url(get_term_link($g)); ?>"><?php echo steppa_genre_icon($g->name) . ' ' . esc_html($g->name); ?></a></li>
          <?php endforeach; endif; ?>
        </ul>
      </div>
      <div class="footer-col">
        <h4>Explore</h4>
        <ul>
          <li><a href="<?php echo home_url('/'); ?>">🏠 Home</a></li>
          <li><a href="<?php echo get_post_type_archive_link('android_game'); ?>">🎮 All Games</a></li>
          <li><a href="<?php echo home_url('/about'); ?>">ℹ️ About Us</a></li>
          <li><a href="<?php echo home_url('/contact'); ?>">📧 Contact Us</a></li>
        </ul>
      </div>
      <div class="footer-col">
        <h4>Legal</h4>
        <ul>
          <li><a href="<?php echo home_url('/privacy-policy'); ?>">🔒 Privacy Policy</a></li>
          <li><a href="<?php echo home_url('/terms'); ?>">📄 Terms of Service</a></li>
          <li><a href="<?php echo home_url('/dmca'); ?>">⚖️ DMCA</a></li>
        </ul>
      </div>
    </div>
    <div class="footer-bottom">
      <span>Powered by <strong style="color:var(--green);">steppa.in</strong></span>
      <span>
        <a href="<?php echo home_url('/privacy-policy'); ?>">Privacy</a> &nbsp;·&nbsp;
        <a href="<?php echo home_url('/terms'); ?>">Terms</a> &nbsp;·&nbsp;
        <a href="<?php echo home_url('/contact'); ?>">Contact</a>
      </span>
    </div>
  </div>
</footer>

<!-- Lightbox -->
<div class="lightbox" id="lightbox" role="dialog" aria-label="Screenshot viewer">
  <span class="lightbox-close" id="lightbox-close" aria-label="Close">✕</span>
  <img id="lightbox-img" src="" alt="Screenshot">
</div>

<!-- Toast -->
<div class="toast" id="toast"></div>

<?php wp_footer(); ?>
</body>
</html>
