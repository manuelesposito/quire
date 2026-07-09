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
	$ver  = '0.1.0';

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
	$base = plugin_dir_url( __FILE__ ) . 'assets';
	$ver  = '0.2.1';
	// components.css is scoped to Quire screens only — its class names
	// (.card, .btn) would collide with core styles if loaded globally.
	wp_enqueue_style( 'quire-components', "$base/components.css", [ 'quire-tokens' ], $ver );
	wp_enqueue_style( "quire-screen-$screen", "$base/screen-$screen.css", [ 'quire-components' ], $ver );
	require __DIR__ . "/screens/$screen.php"; // renders + exits
}

add_action( 'load-index.php', function () {
	if ( ! quire_is_enabled() ) {
		return;
	}
	quire_render_screen( 'dashboard' );
} );

add_action( 'load-options-general.php', function () {
	// Multisite trims this page's option set — our form would blank what it
	// omits (options.php updates the whole allow-list). Core keeps it there.
	if ( ! quire_is_enabled() || is_multisite() ) {
		return;
	}
	quire_render_screen( 'settings-general' );
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

// The front door: wp-login.php runs its own enqueue hook.
add_action( 'login_enqueue_scripts', function () {
	if ( ! quire_is_enabled() ) {
		return;
	}
	$base = plugin_dir_url( __FILE__ ) . 'assets';
	$ver  = '0.1.0';
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
