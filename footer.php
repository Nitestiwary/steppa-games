<?php
/**
 * Steppa Discover - footer.php
 */
$genre_list = get_terms(['taxonomy' => 'game_genre', 'hide_empty' => true, 'number' => 10, 'orderby' => 'count', 'order' => 'DESC']);
$total_games = wp_count_posts('android_game')->publish;
?>

<!-- FOOTER -->
<footer class="site-footer" role="contentinfo">
    <div class="container">
        <div class="footer-grid">

            <!-- Brand Column -->
            <div>
                <a href="<?php echo esc_url(home_url('/')); ?>" class="footer-logo">🎮 Steppa</a>
                <p class="footer-tagline">Your ultimate destination to discover the best Android mobile games. Browse trending, top-rated, and newly released games across all genres.</p>
                <div style="display:flex;gap:10px;margin-top:16px;">
                    <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:8px;padding:10px 16px;text-align:center;">
                        <div style="font-family:'Poppins',sans-serif;font-weight:800;font-size:1.2rem;background:linear-gradient(135deg,var(--purple-ll),var(--blue-l));-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;"><?php echo number_format($total_games); ?>+</div>
                        <div style="font-size:0.72rem;color:var(--text-3);font-weight:500;">Games Listed</div>
                    </div>
                    <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:8px;padding:10px 16px;text-align:center;">
                        <div style="font-family:'Poppins',sans-serif;font-weight:800;font-size:1.2rem;background:linear-gradient(135deg,var(--purple-ll),var(--blue-l));-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;">15+</div>
                        <div style="font-size:0.72rem;color:var(--text-3);font-weight:500;">Categories</div>
                    </div>
                    <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:8px;padding:10px 16px;text-align:center;">
                        <div style="font-family:'Poppins',sans-serif;font-weight:800;font-size:1.2rem;background:linear-gradient(135deg,var(--purple-ll),var(--blue-l));-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;">Daily</div>
                        <div style="font-size:0.72rem;color:var(--text-3);font-weight:500;">Updates</div>
                    </div>
                </div>
            </div>

            <!-- Genres Column -->
            <div>
                <div class="footer-heading">Game Genres</div>
                <div class="footer-links">
                    <?php if (!is_wp_error($genre_list)): foreach ($genre_list as $genre): ?>
                    <a href="<?php echo esc_url(get_term_link($genre)); ?>" class="footer-link">
                        <?php echo function_exists('steppa_genre_icon') ? steppa_genre_icon($genre->name) : '🎮'; ?>
                        <?php echo esc_html($genre->name); ?>
                    </a>
                    <?php endforeach; endif; ?>
                </div>
            </div>

            <!-- Quick Links Column -->
            <div>
                <div class="footer-heading">Discover</div>
                <div class="footer-links">
                    <a href="<?php echo esc_url(get_post_type_archive_link('android_game')); ?>" class="footer-link">🎮 All Games</a>
                    <a href="<?php echo esc_url(get_post_type_archive_link('android_game')); ?>?sort=trending" class="footer-link">🔥 Trending Games</a>
                    <a href="<?php echo esc_url(get_post_type_archive_link('android_game')); ?>?sort=rating" class="footer-link">⭐ Top Rated</a>
                    <a href="<?php echo esc_url(get_post_type_archive_link('android_game')); ?>?sort=installs" class="footer-link">📥 Most Installed</a>
                    <a href="<?php echo esc_url(get_post_type_archive_link('android_game')); ?>?sort=newest" class="footer-link">🆕 Newly Added</a>
                    <a href="<?php echo esc_url(get_post_type_archive_link('android_game')); ?>?price=free" class="footer-link">🆓 Free Games</a>
                    <a href="<?php echo esc_url(get_post_type_archive_link('android_game')); ?>?offline=1" class="footer-link">📴 Offline Games</a>
                </div>
            </div>

            <!-- Company Column -->
            <div>
                <div class="footer-heading">Company</div>
                <div class="footer-links">
                    <a href="<?php echo esc_url(home_url('/about/')); ?>" class="footer-link">ℹ️ About Steppa</a>
                    <a href="<?php echo esc_url(home_url('/contact/')); ?>" class="footer-link">✉️ Contact Us</a>
                    <a href="<?php echo esc_url(home_url('/privacy-policy/')); ?>" class="footer-link">🔒 Privacy Policy</a>
                    <a href="<?php echo esc_url(home_url('/terms/')); ?>" class="footer-link">📋 Terms of Use</a>
                    <a href="<?php echo esc_url(home_url('/disclaimer/')); ?>" class="footer-link">⚠️ Disclaimer</a>
                    <a href="<?php echo esc_url(get_search_link()); ?>" class="footer-link">🔍 Search Games</a>
                </div>
            </div>

        </div>

        <!-- Footer Bottom -->
        <div class="footer-bottom">
            <p class="footer-copy">
                &copy; <?php echo date('Y'); ?> Steppa — Discover The Best Mobile Games.
                Not affiliated with Google Play Store or any game developer.
            </p>
            <p class="footer-powered">
                Powered by <a href="https://monetiscope.com" target="_blank" rel="noopener">Monetiscope</a>
            </p>
        </div>
    </div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
