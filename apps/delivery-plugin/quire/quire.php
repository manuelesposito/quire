<?php
/**
 * Plugin Name: Quire
 * Plugin URI:  https://github.com/manuelesposito/quire
 * Description: A cleaner, warmer WordPress admin — one calm design language across
 *   classic screens, the editor, and (coming) WooCommerce & Jetpack. Design tokens
 *   first; turn it off any time under Settings → General.
 * Version:     0.1.0
 * License:     GPL-2.0-or-later
 * Author:      Quire
 *
 * Delivery Lane architecture (see DELIVERY.md in the repo):
 *   assets/variables.css    the design tokens (generated — do not edit)
 *   assets/hooks.css        Lane 1: WP's own theming hooks mapped to tokens
 *   assets/core-classic.css Lane 2: the classic wp-admin bridge
 * Grown from experiments/playground-probe/quire-probe.php — the probe was
 * the prototype.
 */

defined( 'ABSPATH' ) || exit;

const QUIRE_OPTION = 'quire_enabled';

function quire_is_enabled(): bool {
	return (bool) get_option( QUIRE_OPTION, true );
}

add_action( 'admin_enqueue_scripts', function () {
	if ( ! quire_is_enabled() ) {
		return;
	}
	$base = plugin_dir_url( __FILE__ ) . 'assets';
	$ver  = '0.2.0';

	// Fonts are bundled (SIL OFL) — no admin page ever phones a font CDN.
	wp_enqueue_style( 'quire-fonts', "$base/fonts.css", [], $ver );
	wp_enqueue_style( 'quire-tokens', "$base/variables.css", [], $ver );
	wp_enqueue_style( 'quire-hooks', "$base/hooks.css", [ 'quire-tokens' ], $ver );
	// 'colors' (WP's admin colour scheme) loads late and repaints the menu —
	// declaring it as a dependency is load-bearing, not cosmetic (probe finding).
	wp_enqueue_style( 'quire-core-classic', "$base/core-classic.css", [ 'quire-hooks', 'colors' ], $ver );

	// Per-product bridges — only when the product is there to bridge.
	if ( class_exists( 'WooCommerce' ) ) {
		wp_enqueue_style( 'quire-woo', "$base/woo.css", [ 'quire-core-classic' ], $ver );
	}
}, 999 );

// ---- Lane 3: Quire-owned screens -----------------------------------
// Real screens: our design, real data, rendered in place of the core page.
// The admin chrome (menu, admin bar — already bridged) stays around them
// until the shell stage of the climb.
function quire_render_screen( string $screen ): void {
	// Core's Screen Options panel would only manage the hidden core widgets
	// here — the picker drawer replaced it. (Help tab stays for now, R8.)
	add_filter( 'screen_options_show_screen', '__return_false' );
	$base = plugin_dir_url( __FILE__ ) . 'assets';
	$ver  = '0.8.5';
	// components.css is scoped to Quire screens only — its class names
	// (.card, .btn) would collide with core styles if loaded globally.
	// It depends on core-classic so component rules always PRINT after the
	// Lane-2 bridge — specificity ties must resolve to the component.
	wp_enqueue_style( 'quire-components', "$base/components.css", [ 'quire-tokens', 'quire-core-classic' ], $ver );
	wp_enqueue_style( "quire-screen-$screen", "$base/screen-$screen.css", [ 'quire-components' ], $ver );
	if ( file_exists( __DIR__ . "/assets/screen-$screen.js" ) ) {
		wp_enqueue_script( "quire-screen-$screen", "$base/screen-$screen.js", [], $ver, true );
	}
	require __DIR__ . "/screens/$screen.php"; // renders + exits
}

add_action( 'load-index.php', function () {
	if ( ! quire_is_enabled() ) {
		return;
	}
	quire_render_screen( 'dashboard' );
} );

add_action( 'load-edit.php', function () {
	// Posts only for now — pages/CPTs keep core until their own camps.
	if ( ! quire_is_enabled() || 'post' !== ( $GLOBALS['typenow'] ?? '' ) ) {
		return;
	}
	quire_render_screen( 'posts' );
} );

add_action( 'load-options-general.php', function () {
	// Multisite trims this page's option set — our form would blank what it
	// omits (options.php updates the whole allow-list). Core keeps it there.
	if ( ! quire_is_enabled() || is_multisite() ) {
		return;
	}
	quire_render_screen( 'settings-general' );
} );

// ---- shared date phrasing (screens + ajax renderers) -------------------
// "Today, 09:00" / "Tomorrow, 09:00" / "12 July, 09:00" with time,
// "today" / "yesterday" / "5 July" without.
function quire_day_phrase( int $ts, bool $with_time = false ): string {
	$day       = wp_date( 'Y-m-d', $ts );
	$today     = wp_date( 'Y-m-d' );
	$tomorrow  = wp_date( 'Y-m-d', strtotime( '+1 day', time() ) );
	$yesterday = wp_date( 'Y-m-d', strtotime( '-1 day', time() ) );
	$time      = wp_date( get_option( 'time_format' ), $ts );

	if ( $day === $today ) {
		return $with_time ? sprintf( __( 'Today, %s', 'quire' ), $time ) : __( 'today', 'quire' );
	}
	if ( $day === $tomorrow && $with_time ) {
		return sprintf( __( 'Tomorrow, %s', 'quire' ), $time );
	}
	if ( $day === $yesterday && ! $with_time ) {
		return __( 'yesterday', 'quire' );
	}
	$date = wp_date( get_option( 'date_format' ), $ts );
	return $with_time ? "$date, $time" : $date;
}

// ---- Posts screen ajax: instant actions with Undo, drawer saves --------
// One nonce for the screen; capability is checked per post, per action.

function quire_posts_counts(): array {
	$c   = wp_count_posts();
	$all = (int) $c->publish + (int) $c->future + (int) $c->draft + (int) $c->pending + (int) $c->private;
	return [
		'all'     => $all,
		'publish' => (int) $c->publish,
		'future'  => (int) $c->future,
		'draft'   => (int) $c->draft,
		'pending' => (int) $c->pending,
		'private' => (int) $c->private,
		'trash'   => (int) $c->trash,
	];
}

// The row data the drawers edit and the JS re-renders after a save.
function quire_post_row_data( WP_Post $p ): array {
	$cats = get_the_category( $p->ID );
	$tags = get_the_tags( $p->ID ) ?: [];
	return [
		'id'         => $p->ID,
		'title'      => get_the_title( $p ) ?: __( '(no title)', 'quire' ),
		'slug'       => $p->post_name,
		'status'     => $p->post_status,
		'sticky'     => is_sticky( $p->ID ),
		'protected'  => '' !== $p->post_password,
		'author'     => (int) $p->post_author,
		'authorName' => get_the_author_meta( 'display_name', $p->post_author ),
		'date'       => get_post_timestamp( $p ) ? wp_date( 'Y-m-d\TH:i', get_post_timestamp( $p ) ) : '',
		'cats'       => array_map( fn( $c ) => (int) $c->term_id, $cats ),
		'catNames'   => implode( ', ', array_map( fn( $c ) => $c->name, $cats ) ),
		'tags'       => implode( ', ', array_map( fn( $t ) => $t->name, $tags ) ),
		'comments'   => 'open' === $p->comment_status,
		'pings'      => 'open' === $p->ping_status,
		'dateL1'     => 'future' === $p->post_status ? __( 'Scheduled', 'quire' ) : ( 'publish' === $p->post_status ? __( 'Published', 'quire' ) : __( 'Last modified', 'quire' ) ),
		'dateL2'     => quire_day_phrase( 'publish' === $p->post_status || 'future' === $p->post_status ? get_post_timestamp( $p ) : strtotime( $p->post_modified ), 'future' === $p->post_status ),
	];
}

// trash / untrash / delete — one or many ids, counts come back for the views.
add_action( 'wp_ajax_quire_post_action', function () {
	check_ajax_referer( 'quire_posts', 'nonce' );
	$op  = sanitize_key( $_POST['op'] ?? '' );
	$ids = array_map( 'intval', (array) ( $_POST['ids'] ?? [] ) );
	if ( ! in_array( $op, [ 'trash', 'untrash', 'delete' ], true ) || ! $ids ) {
		wp_send_json_error( null, 400 );
	}
	// Undo must be a true undo: core untrashes to draft by default (5.6+),
	// but the pre-trash status is stored — restore THAT.
	add_filter( 'wp_untrash_post_status', fn( $s, $pid, $prev ) => $prev ?: $s, 10, 3 );
	$done = [];
	foreach ( $ids as $id ) {
		if ( ! current_user_can( 'delete_post', $id ) ) {
			continue;
		}
		$ok = 'trash' === $op ? wp_trash_post( $id ) : ( 'untrash' === $op ? wp_untrash_post( $id ) : wp_delete_post( $id, true ) );
		if ( $ok ) {
			$done[] = $id;
		}
	}
	wp_send_json_success( [ 'done' => $done, 'counts' => quire_posts_counts() ] );
} );

// quick edit (one post) — full core capability, content excluded.
add_action( 'wp_ajax_quire_quick_save', function () {
	check_ajax_referer( 'quire_posts', 'nonce' );
	$id = (int) ( $_POST['id'] ?? 0 );
	if ( ! $id || ! current_user_can( 'edit_post', $id ) ) {
		wp_send_json_error( null, 403 );
	}
	$status     = sanitize_key( $_POST['status'] ?? 'draft' );
	$visibility = sanitize_key( $_POST['visibility'] ?? 'public' );
	$update     = [
		'ID'             => $id,
		'post_title'     => sanitize_text_field( wp_unslash( $_POST['title'] ?? '' ) ),
		'post_name'      => sanitize_title( wp_unslash( $_POST['slug'] ?? '' ) ),
		'post_status'    => 'private' === $visibility ? 'private' : $status,
		'post_author'    => (int) ( $_POST['author'] ?? 0 ),
		'post_password'  => 'password' === $visibility ? sanitize_text_field( wp_unslash( $_POST['password'] ?? '' ) ) : '',
		'comment_status' => empty( $_POST['comments'] ) ? 'closed' : 'open',
		'ping_status'    => empty( $_POST['pings'] ) ? 'closed' : 'open',
	];
	if ( ! empty( $_POST['date'] ) ) {
		// datetime-local "YYYY-MM-DDTHH:MM" → site-time post_date
		$update['post_date'] = str_replace( 'T', ' ', sanitize_text_field( wp_unslash( $_POST['date'] ) ) ) . ':00';
		$update['edit_date'] = true;
	}
	wp_update_post( $update );
	wp_set_post_categories( $id, array_map( 'intval', (array) ( $_POST['cats'] ?? [] ) ) );
	wp_set_post_tags( $id, sanitize_text_field( wp_unslash( $_POST['tags'] ?? '' ) ), false );
	empty( $_POST['sticky'] ) ? unstick_post( $id ) : stick_post( $id );
	wp_send_json_success( [ 'row' => quire_post_row_data( get_post( $id ) ), 'counts' => quire_posts_counts() ] );
} );

// bulk edit (N posts) — "No change" fields arrive empty and are not touched.
// Unlike core, categories and tags can be REMOVED.
add_action( 'wp_ajax_quire_bulk_edit', function () {
	check_ajax_referer( 'quire_posts', 'nonce' );
	$ids = array_map( 'intval', (array) ( $_POST['ids'] ?? [] ) );
	if ( ! $ids ) {
		wp_send_json_error( null, 400 );
	}
	$add_cats    = array_map( 'intval', (array) ( $_POST['add_cats'] ?? [] ) );
	$remove_cats = array_map( 'intval', (array) ( $_POST['remove_cats'] ?? [] ) );
	$add_tags    = sanitize_text_field( wp_unslash( $_POST['add_tags'] ?? '' ) );
	$remove_tags = sanitize_text_field( wp_unslash( $_POST['remove_tags'] ?? '' ) );
	$status      = sanitize_key( $_POST['status'] ?? '' );
	$author      = (int) ( $_POST['author'] ?? 0 );
	$comments    = sanitize_key( $_POST['comments'] ?? '' );
	$sticky      = sanitize_key( $_POST['sticky'] ?? '' );

	$rows = [];
	foreach ( $ids as $id ) {
		if ( ! current_user_can( 'edit_post', $id ) ) {
			continue;
		}
		$update = [ 'ID' => $id ];
		if ( $status ) { $update['post_status'] = $status; }
		if ( $author ) { $update['post_author'] = $author; }
		if ( $comments ) { $update['comment_status'] = 'open' === $comments ? 'open' : 'closed'; }
		if ( count( $update ) > 1 ) { wp_update_post( $update ); }
		if ( $add_cats ) { wp_set_post_categories( $id, $add_cats, true ); }
		if ( $remove_cats ) {
			$keep = array_diff( wp_get_post_categories( $id ), $remove_cats );
			wp_set_post_categories( $id, $keep ?: [ (int) get_option( 'default_category' ) ] );
		}
		if ( $add_tags ) { wp_set_post_tags( $id, $add_tags, true ); }
		if ( $remove_tags ) {
			$drop = array_map( 'trim', explode( ',', strtolower( $remove_tags ) ) );
			$keep = array_filter( wp_get_post_tags( $id ), fn( $t ) => ! in_array( strtolower( $t->name ), $drop, true ) );
			wp_set_post_tags( $id, implode( ',', array_map( fn( $t ) => $t->name, $keep ) ), false );
		}
		if ( 'stick' === $sticky ) { stick_post( $id ); }
		if ( 'unstick' === $sticky ) { unstick_post( $id ); }
		$rows[] = quire_post_row_data( get_post( $id ) );
	}
	wp_send_json_success( [ 'rows' => $rows, 'counts' => quire_posts_counts() ] );
} );

// Quick draft — the one write on the Dashboard. Real draft, real nonce.
add_action( 'admin_post_quire_quick_draft', function () {
	if ( ! current_user_can( 'edit_posts' ) ) {
		wp_die( __( 'Sorry, you are not allowed to create drafts.', 'quire' ) );
	}
	check_admin_referer( 'quire_quick_draft' );
	$title = sanitize_text_field( wp_unslash( $_POST['quire_draft_title'] ?? '' ) );
	$body  = wp_kses_post( wp_unslash( $_POST['quire_draft_content'] ?? '' ) );
	if ( '' !== $title || '' !== $body ) {
		wp_insert_post( [
			'post_title'   => $title,
			'post_content' => $body,
			'post_status'  => 'draft',
		] );
	}
	wp_safe_redirect( admin_url( 'index.php?quire-draft=saved' ) );
	exit;
} );

// Dashboard arrangement — auto-saved per user on every customize-mode
// change (drag, remove, add). Widget ids are validated against the fixed
// set; anything else is silently dropped.
add_action( 'wp_ajax_quire_dashboard_layout', function () {
	check_ajax_referer( 'quire_dashboard_layout', 'nonce' );
	$known  = [ 'welcome', 'overview', 'needs-eye', 'publishing', 'quick-draft', 'site-health', 'news' ];
	$layout = json_decode( wp_unslash( $_POST['layout'] ?? '' ), true );
	if ( ! is_array( $layout ) ) {
		wp_send_json_error( null, 400 );
	}
	$clean = [];
	foreach ( [ 'main', 'side', 'hidden' ] as $col ) {
		$ids           = is_array( $layout[ $col ] ?? null ) ? $layout[ $col ] : [];
		$clean[ $col ] = array_values( array_intersect( array_map( 'sanitize_key', $ids ), $known ) );
	}
	update_user_meta( get_current_user_id(), 'quire_dashboard_layout', $clean );
	wp_send_json_success();
} );

// Welcome widget dismissal — per user, permanent until meta is deleted.
add_action( 'admin_post_quire_dismiss_welcome', function () {
	check_admin_referer( 'quire_dismiss_welcome' );
	update_user_meta( get_current_user_id(), 'quire_welcome_dismissed', 1 );
	wp_safe_redirect( admin_url( 'index.php' ) );
	exit;
} );

// The footer's "Version X" becomes the same quiet door to What's New as
// the Overview widget's version line. When an update is pending, core
// already prints its own "Get Version X" link — leave that one alone.
add_filter( 'update_footer', function ( $text ) {
	if ( ! quire_is_enabled() || false !== strpos( $text, '<a' ) ) {
		return $text;
	}
	return '<a href="' . esc_url( admin_url( 'about.php' ) ) . '" title="' . esc_attr__( 'What’s new in this version', 'quire' ) . '">' . $text . '</a>';
}, 11 );

// The front door: wp-login.php runs its own enqueue hook.
add_action( 'login_enqueue_scripts', function () {
	if ( ! quire_is_enabled() ) {
		return;
	}
	$base = plugin_dir_url( __FILE__ ) . 'assets';
	$ver  = '0.2.0';
	// Fonts are bundled (SIL OFL) — no admin page ever phones a font CDN.
	wp_enqueue_style( 'quire-fonts', "$base/fonts.css", [], $ver );
	wp_enqueue_style( 'quire-tokens', "$base/variables.css", [], $ver );
	wp_enqueue_style( 'quire-login', "$base/login.css", [ 'quire-tokens', 'login' ], $ver );
} );

// The login header shows the site's own name (serif via login.css), not the WP logo.
add_filter( 'login_headertext', function ( $text ) {
	return quire_is_enabled() ? get_bloginfo( 'name' ) : $text;
} );
add_filter( 'login_headerurl', function ( $url ) {
	return quire_is_enabled() ? home_url() : $url;
} );

// The off switch — a single checkbox on Settings → General.
add_action( 'admin_init', function () {
	register_setting( 'general', QUIRE_OPTION, [
		'type'              => 'boolean',
		'default'           => true,
		'sanitize_callback' => 'rest_sanitize_boolean',
	] );
	add_settings_field(
		QUIRE_OPTION,
		__( 'Quire admin style', 'quire' ),
		function () {
			printf(
				'<label><input type="checkbox" name="%s" value="1" %s> %s</label>',
				esc_attr( QUIRE_OPTION ),
				checked( quire_is_enabled(), true, false ),
				esc_html__( 'Give the admin the Quire look (uncheck to return to the default style)', 'quire' )
			);
		},
		'general'
	);
} );
