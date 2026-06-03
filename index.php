<?php
/**
 * Steppa Discover — index.php
 * Fallback template
 */
get_header();
?>

<div class="container" style="padding:60px 0;">
    <?php
    if ( have_posts() ) {
        while ( have_posts() ) {
            the_post();
            the_content();
        }
    } else {
        echo '<p>Nothing found.</p>';
    }
    ?>
</div>

<?php get_footer(); ?>
