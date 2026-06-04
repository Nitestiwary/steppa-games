<?php
/**
 * Template Name: About Us
 */
get_header();
?>

<div class="page-content" style="max-width: 840px;">
  <h1>About Us</h1>
  <p class="hero-sub" style="color:var(--text2); font-size:1.1rem; margin-top:-10px; margin-bottom:24px;">Welcome to <strong>steppa.in</strong> — the ultimate discovery hub for Android gamers.</p>

  <p>
    Our mission is simple: to make finding and downloading high-quality mobile games easy, safe, and exciting. Whether you are into action-packed battle royales, brain-bending puzzles, deep role-playing adventures, or quick offline casual games, we have curated the best selections just for you.
  </p>

  <h2>Who We Are</h2>
  <p>
    We are a small, passionate team of mobile gamers, developers, and writers who were tired of navigating cluttered app stores. We wanted a clean, fast, and SEO-optimized directory that puts gameplay reviews and honest overviews first. That is why we built Steppa.
  </p>

  <h2>Why Choose Steppa?</h2>
  <ul>
    <li><strong>100% Safe Downloads:</strong> We only link to official sources (like the Google Play Store), ensuring every game is verified and safe for your device.</li>
    <li><strong>Editor Reviews:</strong> Get real insights into gameplay mechanics, pros, and cons before you hit download.</li>
    <li><strong>Fast Filtering & Search:</strong> Quickly find games by category, rating, install numbers, and even offline play capability.</li>
    <li><strong>No Ads Clutter:</strong> We keep our layout clean and responsive, putting the gaming content front and center.</li>
  </ul>

  <h2>Get In Touch</h2>
  <p>
    We are always working to improve Steppa. If you have any suggestions, feedback, or game submissions, don't hesitate to reach out to us via our <a href="<?php echo home_url('/contact'); ?>" style="color:var(--green)">Contact Us</a> page.
  </p>

  <div style="margin-top: 30px; text-align: center;">
    <a href="<?php echo get_post_type_archive_link('android_game'); ?>" class="btn-download" style="display: inline-block;">Browse All Games</a>
  </div>
</div>

<?php get_footer(); ?>
