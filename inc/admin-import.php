<?php
/**
 * Steppa Discover — inc/admin-import.php
 *
 * WordPress admin page at Tools > Steppa Game Import.
 * Features:
 *  a) URL-based import from Google Play Store
 *  b) Manual game entry form
 *  c) Re-seed button
 *  d) Status dashboard (game count by genre)
 *
 * @package SteppaDiscover
 */

defined( 'ABSPATH' ) || exit;

// Only load admin-only code on admin requests
if ( ! is_admin() ) {
    return;
}


// ============================================================
// 1. REGISTER ADMIN MENU PAGE
// ============================================================

add_action( 'admin_menu', function() {
    add_management_page(
        __( 'Steppa Game Import', 'steppa-discover' ),
        __( 'Steppa Import',      'steppa-discover' ),
        'manage_options',
        'steppa-game-import',
        'steppa_admin_import_page'
    );
} );


// ============================================================
// 2. ADMIN PAGE STYLES (scoped inline)
// ============================================================

add_action( 'admin_head', function() {
    $screen = get_current_screen();
    if ( ! $screen || $screen->id !== 'tools_page_steppa-game-import' ) return;
    ?>
    <style>
    #steppa-import-wrap { max-width: 960px; }
    #steppa-import-wrap h1 { display:flex; align-items:center; gap:10px; }
    #steppa-import-wrap h1 .dashicons { font-size:28px; color:#7c3aed; }
    .steppa-tabs { display:flex; gap:0; margin-bottom:24px; border-bottom:2px solid #e0e0e0; }
    .steppa-tab  { padding:10px 22px; cursor:pointer; font-weight:600; border:none; background:none; color:#555;
                   border-bottom:3px solid transparent; margin-bottom:-2px; transition:color .2s,border-color .2s; }
    .steppa-tab.active, .steppa-tab:hover { color:#7c3aed; border-bottom-color:#7c3aed; }
    .steppa-panel { display:none; }
    .steppa-panel.active { display:block; }
    .steppa-card { background:#fff; border:1px solid #ddd; border-radius:8px; padding:24px; margin-bottom:20px; }
    .steppa-card h2 { margin-top:0; font-size:16px; color:#333; }
    .steppa-field { margin-bottom:16px; }
    .steppa-field label { display:block; font-weight:600; margin-bottom:6px; color:#333; font-size:13px; }
    .steppa-field input[type=text],
    .steppa-field input[type=url],
    .steppa-field input[type=number],
    .steppa-field select,
    .steppa-field textarea { width:100%; padding:8px 12px; border:1px solid #ccc; border-radius:4px; font-size:13px; }
    .steppa-field textarea { min-height:100px; resize:vertical; }
    .steppa-row { display:grid; grid-template-columns:1fr 1fr; gap:16px; }
    .steppa-row-3 { display:grid; grid-template-columns:1fr 1fr 1fr; gap:16px; }
    .steppa-stat-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(200px,1fr)); gap:16px; }
    .steppa-stat-box { background:#f9f9f9; border:1px solid #e0e0e0; border-radius:8px; padding:16px; text-align:center; }
    .steppa-stat-box .count { font-size:36px; font-weight:800; color:#7c3aed; }
    .steppa-stat-box .label { font-size:13px; color:#666; margin-top:4px; }
    .steppa-notice-success { background:#d4edda; border:1px solid #c3e6cb; color:#155724; padding:12px 16px; border-radius:4px; margin-bottom:16px; }
    .steppa-notice-error   { background:#f8d7da; border:1px solid #f5c6cb; color:#721c24; padding:12px 16px; border-radius:4px; margin-bottom:16px; }
    .steppa-notice-info    { background:#d1ecf1; border:1px solid #bee5eb; color:#0c5460; padding:12px 16px; border-radius:4px; margin-bottom:16px; }
    .btn-steppa { background:#7c3aed; color:#fff; border:none; padding:10px 20px; border-radius:6px; font-size:14px;
                  font-weight:600; cursor:pointer; transition:background .2s; }
    .btn-steppa:hover { background:#6d28d9; }
    .btn-steppa-danger { background:#dc3545; }
    .btn-steppa-danger:hover { background:#c82333; }
    .btn-steppa-secondary { background:#6c757d; }
    .btn-steppa-secondary:hover { background:#5a6268; }
    #import-progress { display:none; margin-top:12px; }
    #import-progress progress { width:100%; height:6px; }
    </style>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Tab switching
        document.querySelectorAll('.steppa-tab').forEach(function(btn) {
            btn.addEventListener('click', function() {
                var target = this.dataset.tab;
                document.querySelectorAll('.steppa-tab').forEach(function(b){ b.classList.remove('active'); });
                document.querySelectorAll('.steppa-panel').forEach(function(p){ p.classList.remove('active'); });
                this.classList.add('active');
                document.getElementById('panel-' + target).classList.add('active');
            });
        });
    });
    </script>
    <?php
} );


// ============================================================
// 3. PROCESS FORM SUBMISSIONS
// ============================================================

function steppa_process_admin_actions() {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    // ── URL Import ────────────────────────────────────────────
    if ( isset( $_POST['steppa_url_import_nonce'] )
        && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['steppa_url_import_nonce'] ) ), 'steppa_url_import' )
    ) {
        $url = sanitize_url( wp_unslash( $_POST['play_store_url'] ?? '' ) );
        return steppa_import_from_url( $url );
    }

    // ── Manual Import ─────────────────────────────────────────
    if ( isset( $_POST['steppa_manual_import_nonce'] )
        && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['steppa_manual_import_nonce'] ) ), 'steppa_manual_import' )
    ) {
        return steppa_process_manual_import( $_POST );
    }

    // ── Reseed ────────────────────────────────────────────────
    if ( isset( $_POST['steppa_reseed_nonce'] )
        && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['steppa_reseed_nonce'] ) ), 'steppa_reseed' )
    ) {
        if ( function_exists( 'steppa_reseed' ) ) {
            steppa_reseed();
            return [ 'type' => 'success', 'message' => __( 'All games deleted and re-seeded successfully!', 'steppa-discover' ) ];
        }
        return [ 'type' => 'error', 'message' => __( 'Seeder not available.', 'steppa-discover' ) ];
    }

    return null;
}


// ============================================================
// 4. URL IMPORT LOGIC
// ============================================================

function steppa_import_from_url( $url ) {
    // ── Extract package ID ────────────────────────────────────
    if ( empty( $url ) ) {
        return [ 'type' => 'error', 'message' => __( 'Please provide a Play Store URL.', 'steppa-discover' ) ];
    }

    $pkg_id = '';
    if ( preg_match( '/[?&]id=([a-zA-Z0-9_.]+)/', $url, $m ) ) {
        $pkg_id = $m[1];
    } elseif ( preg_match( '#/store/apps/details/([a-zA-Z0-9_.]+)#', $url, $m ) ) {
        $pkg_id = $m[1];
    }

    if ( empty( $pkg_id ) ) {
        return [ 'type' => 'error', 'message' => __( 'Could not extract package ID from URL. Ensure it is a valid Google Play URL.', 'steppa-discover' ) ];
    }

    // Check if already imported
    $existing = get_posts( [
        'post_type'   => 'android_game',
        'meta_key'    => '_game_package_id',
        'meta_value'  => $pkg_id,
        'post_status' => 'any',
        'numberposts' => 1,
        'fields'      => 'ids',
    ] );

    if ( ! empty( $existing ) ) {
        $edit_url = get_edit_post_link( $existing[0] );
        return [
            'type'    => 'info',
            'message' => sprintf(
                __( 'Game with package ID <code>%s</code> already exists. <a href="%s">Edit it here</a>.', 'steppa-discover' ),
                esc_html( $pkg_id ),
                esc_url( $edit_url )
            ),
        ];
    }

    // ── Fetch Play Store page ─────────────────────────────────
    $canonical_url = 'https://play.google.com/store/apps/details?id=' . $pkg_id . '&hl=en';

    $response = wp_remote_get( $canonical_url, [
        'timeout'    => 15,
        'user-agent' => 'Mozilla/5.0 (compatible; SteppaBot/2.0; +https://steppa.in)',
        'headers'    => [
            'Accept-Language' => 'en-US,en;q=0.9',
        ],
    ] );

    if ( is_wp_error( $response ) ) {
        return [
            'type'    => 'error',
            'message' => __( 'Failed to fetch Play Store page: ', 'steppa-discover' ) . $response->get_error_message(),
        ];
    }

    $http_code = wp_remote_retrieve_response_code( $response );
    if ( $http_code !== 200 ) {
        return [
            'type'    => 'error',
            'message' => sprintf( __( 'Play Store returned HTTP %d. The app may not exist or is geo-restricted.', 'steppa-discover' ), $http_code ),
        ];
    }

    $body = wp_remote_retrieve_body( $response );

    // ── Parse OG tags ─────────────────────────────────────────
    $game_data = steppa_parse_playstore_html( $body, $pkg_id, $canonical_url );

    if ( empty( $game_data['title'] ) ) {
        return [ 'type' => 'error', 'message' => __( 'Could not parse game title from Play Store page.', 'steppa-discover' ) ];
    }

    // ── Insert post ───────────────────────────────────────────
    $post_id = wp_insert_post( [
        'post_type'    => 'android_game',
        'post_status'  => 'draft',
        'post_title'   => $game_data['title'],
        'post_content' => $game_data['description'],
        'post_excerpt' => wp_trim_words( $game_data['description'], 30, '…' ),
    ] );

    if ( is_wp_error( $post_id ) ) {
        return [ 'type' => 'error', 'message' => $post_id->get_error_message() ];
    }

    // Set meta
    $meta_fields = [
        '_game_package_id'    => $pkg_id,
        '_game_icon_url'      => $game_data['icon'],
        '_game_playstore_url' => $canonical_url,
        '_game_price'         => $game_data['price'] ?? 'Free',
        '_game_rating'        => $game_data['rating'] ?? '',
        '_game_developer'     => $game_data['developer'] ?? '',
        '_game_installs'      => $game_data['installs'] ?? '',
        '_game_installs_raw'  => $game_data['installs_raw'] ?? 0,
        '_game_size'          => $game_data['size'] ?? '',
        '_game_version'       => $game_data['version'] ?? '',
        '_game_is_offline'    => '0',
        '_game_featured'      => '0',
        '_game_trending_score'=> 0,
        '_game_screenshots'   => json_encode( $game_data['screenshots'] ?? [] ),
    ];

    foreach ( $meta_fields as $key => $val ) {
        update_post_meta( $post_id, $key, $val );
    }

    // Set genre if detected
    if ( ! empty( $game_data['genre'] ) ) {
        wp_set_post_terms( $post_id, $game_data['genre'], 'game_genre' );
    }

    // Set developer
    if ( ! empty( $game_data['developer'] ) ) {
        wp_set_post_terms( $post_id, $game_data['developer'], 'game_developer_tax' );
    }

    $edit_url = get_edit_post_link( $post_id );
    return [
        'type'    => 'success',
        'message' => sprintf(
            __( '<strong>%s</strong> imported successfully as a Draft. <a href="%s">Review and publish</a>.', 'steppa-discover' ),
            esc_html( $game_data['title'] ),
            esc_url( $edit_url )
        ),
    ];
}


// ============================================================
// 5. PLAY STORE HTML PARSER
// ============================================================

function steppa_parse_playstore_html( $html, $pkg_id, $page_url ) {
    $data = [
        'title'       => '',
        'description' => '',
        'icon'        => '',
        'screenshots' => [],
        'rating'      => '',
        'installs'    => '',
        'installs_raw'=> 0,
        'developer'   => '',
        'genre'       => '',
        'size'        => '',
        'version'     => '',
        'price'       => 'Free',
    ];

    // Use DOMDocument to parse
    libxml_use_internal_errors( true );
    $doc = new DOMDocument();
    $doc->loadHTML( mb_convert_encoding( $html, 'HTML-ENTITIES', 'UTF-8' ) );
    libxml_clear_errors();

    $xpath = new DOMXPath( $doc );

    // ── og:title ──────────────────────────────────────────────
    $og_title = $xpath->query( '//meta[@property="og:title"]/@content' );
    if ( $og_title->length ) {
        $raw = $og_title->item(0)->nodeValue;
        // Remove " - Apps on Google Play" suffix
        $data['title'] = trim( preg_replace( '/\s*-\s*Apps on Google Play\s*$/i', '', $raw ) );
    }

    // ── og:image ──────────────────────────────────────────────
    $og_image = $xpath->query( '//meta[@property="og:image"]/@content' );
    if ( $og_image->length ) {
        $data['icon'] = $og_image->item(0)->nodeValue;
    }

    // ── og:description ────────────────────────────────────────
    $og_desc = $xpath->query( '//meta[@property="og:description"]/@content' );
    if ( $og_desc->length ) {
        $data['description'] = $og_desc->item(0)->nodeValue;
    }

    // Fallback: meta description
    if ( empty( $data['description'] ) ) {
        $meta_desc = $xpath->query( '//meta[@name="description"]/@content' );
        if ( $meta_desc->length ) {
            $data['description'] = $meta_desc->item(0)->nodeValue;
        }
    }

    // ── Title fallback (page title) ───────────────────────────
    if ( empty( $data['title'] ) ) {
        $page_title = $xpath->query( '//title' );
        if ( $page_title->length ) {
            $data['title'] = trim( preg_replace( '/\s*-\s*Apps on Google Play\s*$/i', '', $page_title->item(0)->textContent ) );
        }
    }

    // ── Structured data (JSON-LD) from Play Store ─────────────
    $scripts = $xpath->query( '//script[@type="application/ld+json"]' );
    foreach ( $scripts as $script ) {
        $json = json_decode( $script->textContent, true );
        if ( ! $json ) continue;

        if ( isset( $json['@type'] ) && $json['@type'] === 'SoftwareApplication' ) {
            $data['rating']    = $json['aggregateRating']['ratingValue'] ?? '';
            $data['developer'] = $json['author']['name'] ?? '';
            $data['genre']     = $json['applicationCategory'] ?? '';
            $data['price']     = isset( $json['offers']['price'] ) && $json['offers']['price'] == '0' ? 'Free' : ( $json['offers']['price'] ?? 'Free' );
        }
    }

    // ── Screenshots (og:image:secure_url or meta image variants) ─
    $img_nodes = $xpath->query( '//meta[@property="og:image:secure_url"]/@content' );
    foreach ( $img_nodes as $n ) {
        $data['screenshots'][] = $n->nodeValue;
    }

    return $data;
}


// ============================================================
// 6. MANUAL IMPORT PROCESSOR
// ============================================================

function steppa_process_manual_import( $post_data ) {
    $title       = sanitize_text_field( wp_unslash( $post_data['game_title']   ?? '' ) );
    $description = sanitize_textarea_field( wp_unslash( $post_data['game_description'] ?? '' ) );
    $developer   = sanitize_text_field( wp_unslash( $post_data['game_developer'] ?? '' ) );
    $genre       = sanitize_text_field( wp_unslash( $post_data['game_genre']   ?? '' ) );
    $pkg_id      = sanitize_text_field( wp_unslash( $post_data['package_id']   ?? '' ) );
    $icon_url    = sanitize_url( wp_unslash( $post_data['icon_url'] ?? '' ) );
    $rating      = (float) ( $post_data['rating']      ?? 0 );
    $reviews     = (int)   ( $post_data['reviews']     ?? 0 );
    $installs    = sanitize_text_field( wp_unslash( $post_data['installs']  ?? '' ) );
    $installs_raw= (int)   ( $post_data['installs_raw'] ?? 0 );
    $size        = sanitize_text_field( wp_unslash( $post_data['size']     ?? '' ) );
    $version     = sanitize_text_field( wp_unslash( $post_data['version']  ?? '' ) );
    $price       = sanitize_text_field( wp_unslash( $post_data['price']    ?? 'Free' ) );
    $is_offline  = ! empty( $post_data['is_offline'] ) ? '1' : '0';
    $is_featured = ! empty( $post_data['is_featured'] ) ? '1' : '0';
    $trending    = (int) ( $post_data['trending_score'] ?? 0 );

    if ( empty( $title ) ) {
        return [ 'type' => 'error', 'message' => __( 'Game title is required.', 'steppa-discover' ) ];
    }

    $post_id = wp_insert_post( [
        'post_type'    => 'android_game',
        'post_status'  => 'publish',
        'post_title'   => $title,
        'post_content' => $description,
        'post_excerpt' => wp_trim_words( $description, 30, '…' ),
    ] );

    if ( is_wp_error( $post_id ) ) {
        return [ 'type' => 'error', 'message' => $post_id->get_error_message() ];
    }

    $playstore_url = $pkg_id ? 'https://play.google.com/store/apps/details?id=' . $pkg_id : '';

    $metas = [
        '_game_package_id'    => $pkg_id,
        '_game_icon_url'      => $icon_url,
        '_game_rating'        => $rating,
        '_game_reviews'       => $reviews,
        '_game_installs'      => $installs,
        '_game_installs_raw'  => $installs_raw,
        '_game_size'          => $size,
        '_game_version'       => $version,
        '_game_price'         => $price,
        '_game_is_offline'    => $is_offline,
        '_game_featured'      => $is_featured,
        '_game_trending_score'=> $trending,
        '_game_playstore_url' => $playstore_url,
        '_game_developer'     => $developer,
        '_game_screenshots'   => '[]',
        '_game_updated'       => current_time( 'F Y' ),
    ];

    foreach ( $metas as $key => $val ) {
        update_post_meta( $post_id, $key, $val );
    }

    if ( $genre ) {
        wp_set_post_terms( $post_id, $genre, 'game_genre' );
    }
    if ( $developer ) {
        wp_set_post_terms( $post_id, $developer, 'game_developer_tax' );
    }

    return [
        'type'    => 'success',
        'message' => sprintf(
            __( '<strong>%s</strong> added successfully! <a href="%s">View</a> · <a href="%s">Edit</a>', 'steppa-discover' ),
            esc_html( $title ),
            esc_url( get_permalink( $post_id ) ),
            esc_url( get_edit_post_link( $post_id ) )
        ),
    ];
}


// ============================================================
// 7. ADMIN PAGE HTML
// ============================================================

function steppa_admin_import_page() {
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( __( 'You do not have permission to access this page.', 'steppa-discover' ) );
    }

    $notice = steppa_process_admin_actions();

    // Gather stats
    $total_games    = wp_count_posts( 'android_game' );
    $published      = (int) ( $total_games->publish ?? 0 );
    $draft          = (int) ( $total_games->draft   ?? 0 );
    $genre_terms    = get_terms( [ 'taxonomy' => 'game_genre', 'hide_empty' => false ] );
    $dev_terms      = get_terms( [ 'taxonomy' => 'game_developer_tax', 'hide_empty' => false ] );
    $genre_count    = is_array( $genre_terms ) ? count( $genre_terms ) : 0;
    $dev_count      = is_array( $dev_terms ) ? count( $dev_terms ) : 0;
    ?>
    <div class="wrap" id="steppa-import-wrap">

        <h1>
            <span class="dashicons dashicons-smartphone"></span>
            <?php esc_html_e( 'Steppa Game Import', 'steppa-discover' ); ?>
        </h1>
        <p class="description"><?php esc_html_e( 'Import games from Google Play or add them manually. Manage your game catalogue below.', 'steppa-discover' ); ?></p>

        <?php if ( $notice ) : ?>
        <div class="steppa-notice-<?php echo esc_attr( $notice['type'] ); ?>">
            <?php echo wp_kses_post( $notice['message'] ); ?>
        </div>
        <?php endif; ?>

        <!-- Tabs -->
        <div class="steppa-tabs">
            <button class="steppa-tab active" data-tab="url"><?php esc_html_e( '🔗 Import from URL',    'steppa-discover' ); ?></button>
            <button class="steppa-tab"        data-tab="manual"><?php esc_html_e( '✏️ Manual Entry',   'steppa-discover' ); ?></button>
            <button class="steppa-tab"        data-tab="dashboard"><?php esc_html_e( '📊 Dashboard',  'steppa-discover' ); ?></button>
            <button class="steppa-tab"        data-tab="tools"><?php esc_html_e( '⚙️ Tools',           'steppa-discover' ); ?></button>
        </div>

        <!-- ══ Tab: URL Import ═══════════════════════════════════════ -->
        <div id="panel-url" class="steppa-panel active">
            <div class="steppa-card">
                <h2><?php esc_html_e( 'Import from Google Play URL', 'steppa-discover' ); ?></h2>
                <p><?php esc_html_e( 'Paste a Google Play Store URL and we will fetch the game metadata automatically.', 'steppa-discover' ); ?></p>
                <p><strong><?php esc_html_e( 'Example:', 'steppa-discover' ); ?></strong>
                   <code>https://play.google.com/store/apps/details?id=com.supercell.clashofclans</code></p>

                <form method="post" action="">
                    <?php wp_nonce_field( 'steppa_url_import', 'steppa_url_import_nonce' ); ?>

                    <div class="steppa-field">
                        <label for="play_store_url"><?php esc_html_e( 'Google Play Store URL *', 'steppa-discover' ); ?></label>
                        <input type="url" id="play_store_url" name="play_store_url"
                               placeholder="https://play.google.com/store/apps/details?id=..."
                               required style="width:100%;max-width:700px;">
                    </div>

                    <button type="submit" class="btn-steppa">
                        <?php esc_html_e( '⬇ Import Game', 'steppa-discover' ); ?>
                    </button>

                    <div id="import-progress">
                        <progress></progress>
                        <p><?php esc_html_e( 'Fetching Play Store page…', 'steppa-discover' ); ?></p>
                    </div>
                </form>

                <hr style="margin:24px 0 16px;">
                <p class="description">
                    <?php esc_html_e( 'ℹ️ The game will be saved as a Draft. Review metadata, set the genre, and publish when ready.', 'steppa-discover' ); ?>
                </p>
            </div>
        </div><!-- /panel-url -->

        <!-- ══ Tab: Manual Entry ══════════════════════════════════════ -->
        <div id="panel-manual" class="steppa-panel">
            <div class="steppa-card">
                <h2><?php esc_html_e( 'Add Game Manually', 'steppa-discover' ); ?></h2>

                <form method="post" action="">
                    <?php wp_nonce_field( 'steppa_manual_import', 'steppa_manual_import_nonce' ); ?>

                    <!-- Row 1: Title + Package ID -->
                    <div class="steppa-row">
                        <div class="steppa-field">
                            <label for="game_title"><?php esc_html_e( 'Game Title *', 'steppa-discover' ); ?></label>
                            <input type="text" id="game_title" name="game_title" placeholder="e.g. Clash of Clans" required>
                        </div>
                        <div class="steppa-field">
                            <label for="package_id"><?php esc_html_e( 'Package ID', 'steppa-discover' ); ?></label>
                            <input type="text" id="package_id" name="package_id" placeholder="e.g. com.supercell.clashofclans">
                        </div>
                    </div>

                    <!-- Row 2: Developer + Genre -->
                    <div class="steppa-row">
                        <div class="steppa-field">
                            <label for="game_developer"><?php esc_html_e( 'Developer', 'steppa-discover' ); ?></label>
                            <input type="text" id="game_developer" name="game_developer" placeholder="e.g. Supercell">
                        </div>
                        <div class="steppa-field">
                            <label for="game_genre"><?php esc_html_e( 'Genre', 'steppa-discover' ); ?></label>
                            <?php
                            $genres_list = get_terms( [ 'taxonomy' => 'game_genre', 'hide_empty' => false ] );
                            if ( is_array( $genres_list ) && ! empty( $genres_list ) ) : ?>
                            <select id="game_genre" name="game_genre">
                                <option value=""><?php esc_html_e( '— Select Genre —', 'steppa-discover' ); ?></option>
                                <?php foreach ( $genres_list as $g ) : ?>
                                <option value="<?php echo esc_attr( $g->name ); ?>"><?php echo esc_html( $g->name ); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <?php else : ?>
                            <input type="text" id="game_genre" name="game_genre" placeholder="e.g. Action">
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Description -->
                    <div class="steppa-field">
                        <label for="game_description"><?php esc_html_e( 'Description', 'steppa-discover' ); ?></label>
                        <textarea id="game_description" name="game_description" placeholder="<?php esc_attr_e( 'Game description…', 'steppa-discover' ); ?>"></textarea>
                    </div>

                    <!-- Icon URL -->
                    <div class="steppa-field">
                        <label for="icon_url"><?php esc_html_e( 'Icon URL', 'steppa-discover' ); ?></label>
                        <input type="url" id="icon_url" name="icon_url" placeholder="https://…/icon.png">
                    </div>

                    <!-- Row: Rating + Reviews + Price -->
                    <div class="steppa-row-3">
                        <div class="steppa-field">
                            <label for="rating"><?php esc_html_e( 'Rating (0–5)', 'steppa-discover' ); ?></label>
                            <input type="number" id="rating" name="rating" min="0" max="5" step="0.1" placeholder="4.5">
                        </div>
                        <div class="steppa-field">
                            <label for="reviews"><?php esc_html_e( 'Review Count', 'steppa-discover' ); ?></label>
                            <input type="number" id="reviews" name="reviews" min="0" placeholder="1000000">
                        </div>
                        <div class="steppa-field">
                            <label for="price"><?php esc_html_e( 'Price', 'steppa-discover' ); ?></label>
                            <input type="text" id="price" name="price" placeholder="Free">
                        </div>
                    </div>

                    <!-- Row: Installs + Size + Version -->
                    <div class="steppa-row-3">
                        <div class="steppa-field">
                            <label for="installs"><?php esc_html_e( 'Installs (Display)', 'steppa-discover' ); ?></label>
                            <input type="text" id="installs" name="installs" placeholder="500M+">
                        </div>
                        <div class="steppa-field">
                            <label for="installs_raw"><?php esc_html_e( 'Installs (Raw Number)', 'steppa-discover' ); ?></label>
                            <input type="number" id="installs_raw" name="installs_raw" min="0" placeholder="500000000">
                        </div>
                        <div class="steppa-field">
                            <label for="size"><?php esc_html_e( 'File Size', 'steppa-discover' ); ?></label>
                            <input type="text" id="size" name="size" placeholder="142 MB">
                        </div>
                    </div>

                    <!-- Row: Version + Trending -->
                    <div class="steppa-row">
                        <div class="steppa-field">
                            <label for="version"><?php esc_html_e( 'Current Version', 'steppa-discover' ); ?></label>
                            <input type="text" id="version" name="version" placeholder="15.0.1">
                        </div>
                        <div class="steppa-field">
                            <label for="trending_score"><?php esc_html_e( 'Trending Score (0–100)', 'steppa-discover' ); ?></label>
                            <input type="number" id="trending_score" name="trending_score" min="0" max="100" placeholder="75">
                        </div>
                    </div>

                    <!-- Checkboxes -->
                    <div class="steppa-field" style="display:flex;gap:24px;">
                        <label style="display:flex;align-items:center;gap:6px;font-weight:normal;">
                            <input type="checkbox" name="is_offline" value="1">
                            <?php esc_html_e( 'Playable Offline', 'steppa-discover' ); ?>
                        </label>
                        <label style="display:flex;align-items:center;gap:6px;font-weight:normal;">
                            <input type="checkbox" name="is_featured" value="1">
                            <?php esc_html_e( 'Featured Game', 'steppa-discover' ); ?>
                        </label>
                    </div>

                    <button type="submit" class="btn-steppa">
                        <?php esc_html_e( '✚ Add Game', 'steppa-discover' ); ?>
                    </button>
                </form>
            </div>
        </div><!-- /panel-manual -->

        <!-- ══ Tab: Dashboard ════════════════════════════════════════ -->
        <div id="panel-dashboard" class="steppa-panel">
            <div class="steppa-card">
                <h2><?php esc_html_e( 'Game Catalogue Overview', 'steppa-discover' ); ?></h2>

                <!-- Summary stats -->
                <div class="steppa-stat-grid" style="margin-bottom:24px;">
                    <div class="steppa-stat-box">
                        <div class="count"><?php echo esc_html( $published ); ?></div>
                        <div class="label"><?php esc_html_e( 'Published Games', 'steppa-discover' ); ?></div>
                    </div>
                    <div class="steppa-stat-box">
                        <div class="count"><?php echo esc_html( $draft ); ?></div>
                        <div class="label"><?php esc_html_e( 'Drafts', 'steppa-discover' ); ?></div>
                    </div>
                    <div class="steppa-stat-box">
                        <div class="count"><?php echo esc_html( $genre_count ); ?></div>
                        <div class="label"><?php esc_html_e( 'Genres', 'steppa-discover' ); ?></div>
                    </div>
                    <div class="steppa-stat-box">
                        <div class="count"><?php echo esc_html( $dev_count ); ?></div>
                        <div class="label"><?php esc_html_e( 'Developers', 'steppa-discover' ); ?></div>
                    </div>
                </div>

                <!-- Games by genre -->
                <?php if ( is_array( $genre_terms ) && ! empty( $genre_terms ) ) : ?>
                <h3><?php esc_html_e( 'Games by Genre', 'steppa-discover' ); ?></h3>
                <table class="wp-list-table widefat fixed striped" style="max-width:600px;">
                    <thead>
                        <tr>
                            <th><?php esc_html_e( 'Genre', 'steppa-discover' ); ?></th>
                            <th><?php esc_html_e( 'Game Count', 'steppa-discover' ); ?></th>
                            <th><?php esc_html_e( 'Actions', 'steppa-discover' ); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ( $genre_terms as $term ) : ?>
                        <tr>
                            <td>
                                <?php echo esc_html( steppa_get_genre_icon( $term->slug ) ); ?>
                                <?php echo esc_html( $term->name ); ?>
                            </td>
                            <td><strong><?php echo esc_html( $term->count ); ?></strong></td>
                            <td>
                                <a href="<?php echo esc_url( get_term_link( $term ) ); ?>" target="_blank" class="button button-small">View</a>
                                <a href="<?php echo esc_url( get_edit_term_link( $term->term_id, 'game_genre' ) ); ?>" class="button button-small">Edit</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <?php else : ?>
                <p class="steppa-notice-info"><?php esc_html_e( 'No genres yet. Import or add games to populate genres.', 'steppa-discover' ); ?></p>
                <?php endif; ?>
            </div>
        </div><!-- /panel-dashboard -->

        <!-- ══ Tab: Tools ════════════════════════════════════════════ -->
        <div id="panel-tools" class="steppa-panel">
            <div class="steppa-card">
                <h2><?php esc_html_e( 'Re-seed Demo Games', 'steppa-discover' ); ?></h2>
                <p class="steppa-notice-info">
                    <strong><?php esc_html_e( 'Warning:', 'steppa-discover' ); ?></strong>
                    <?php esc_html_e( 'This will DELETE all existing android_game posts and re-run the seeder with 150 demo games. This cannot be undone.', 'steppa-discover' ); ?>
                </p>
                <form method="post" action="" onsubmit="return confirm('<?php esc_attr_e( 'Are you sure? All existing games will be deleted and re-seeded.', 'steppa-discover' ); ?>')">
                    <?php wp_nonce_field( 'steppa_reseed', 'steppa_reseed_nonce' ); ?>
                    <button type="submit" class="btn-steppa btn-steppa-danger">
                        <?php esc_html_e( '🔄 Delete All & Re-Seed', 'steppa-discover' ); ?>
                    </button>
                </form>
            </div>

            <div class="steppa-card">
                <h2><?php esc_html_e( 'Flush Rewrite Rules', 'steppa-discover' ); ?></h2>
                <p><?php esc_html_e( 'If game/genre URLs return 404 errors, flush rewrite rules.', 'steppa-discover' ); ?></p>
                <a href="<?php echo esc_url( admin_url( 'options-permalink.php' ) ); ?>" class="btn-steppa btn-steppa-secondary">
                    <?php esc_html_e( '⚡ Go to Permalink Settings', 'steppa-discover' ); ?>
                </a>
            </div>

            <div class="steppa-card">
                <h2><?php esc_html_e( 'Debug Info', 'steppa-discover' ); ?></h2>
                <table class="wp-list-table widefat" style="max-width:500px;">
                    <tr><th><?php esc_html_e( 'Theme Version', 'steppa-discover' ); ?></th><td><?php echo esc_html( STEPPA_VERSION ); ?></td></tr>
                    <tr><th><?php esc_html_e( 'WordPress Version', 'steppa-discover' ); ?></th><td><?php echo esc_html( get_bloginfo( 'version' ) ); ?></td></tr>
                    <tr><th><?php esc_html_e( 'PHP Version', 'steppa-discover' ); ?></th><td><?php echo esc_html( PHP_VERSION ); ?></td></tr>
                    <tr><th><?php esc_html_e( 'Seeded Option', 'steppa-discover' ); ?></th><td><?php echo esc_html( get_option( 'steppa_seeded_v1', 'no' ) ); ?></td></tr>
                    <tr><th><?php esc_html_e( 'Theme Dir', 'steppa-discover' ); ?></th><td><?php echo esc_html( STEPPA_THEME_DIR ); ?></td></tr>
                </table>
            </div>
        </div><!-- /panel-tools -->

    </div><!-- #steppa-import-wrap -->
    <?php
}
