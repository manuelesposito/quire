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

	wp_enqueue_style(
		'quire-fonts',
		'https://fonts.googleapis.com/css2?family=Libre+Baskerville:ital,wght@0,400;0,700;1,400&family=Inter:wght@400;450;500;600&family=IBM+Plex+Mono:wght@400;500&display=swap',
		[],
		$ver
	);
	wp_enqueue_style( 'quire-tokens', "$base/variables.css", [], $ver );
	wp_enqueue_style( 'quire-hooks', "$base/hooks.css", [ 'quire-tokens' ], $ver );
	// 'colors' (WP's admin colour scheme) loads late and repaints the menu —
	// declaring it as a dependency is load-bearing, not cosmetic (probe finding).
	wp_enqueue_style( 'quire-core-classic', "$base/core-classic.css", [ 'quire-hooks', 'colors' ], $ver );
}, 999 );

// The front door: wp-login.php runs its own enqueue hook.
add_action( 'login_enqueue_scripts', function () {
	if ( ! quire_is_enabled() ) {
		return;
	}
	$base = plugin_dir_url( __FILE__ ) . 'assets';
	$ver  = '0.1.0';
	wp_enqueue_style(
		'quire-fonts',
		'https://fonts.googleapis.com/css2?family=Libre+Baskerville:ital,wght@0,400;0,700;1,400&family=Inter:wght@400;450;500;600&display=swap',
		[],
		$ver
	);
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
