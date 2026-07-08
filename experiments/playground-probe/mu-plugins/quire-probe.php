<?php
/**
 * Plugin Name: Quire Probe
 * Description: Injects Quire design tokens into wp-admin in measurable layers.
 *   ?quire=0 baseline (off) · ?quire=1 tokens + WP hooks only · ?quire=2 + bridge.
 *   The chosen layer persists via cookie so navigation keeps the state.
 */

function quire_probe_level(): int {
    if ( isset( $_GET['quire'] ) ) {
        $level = max( 0, min( 2, (int) $_GET['quire'] ) );
        setcookie( 'quire_probe', (string) $level, 0, '/' );
        return $level;
    }
    if ( isset( $_COOKIE['quire_probe'] ) ) {
        return max( 0, min( 2, (int) $_COOKIE['quire_probe'] ) );
    }
    return 0;
}

add_action( 'admin_enqueue_scripts', function () {
    $level = quire_probe_level();
    if ( $level < 1 ) {
        return;
    }
    $base = content_url( 'mu-plugins/quire-probe' );

    wp_enqueue_style( 'quire-tokens', $base . '/quire-tokens.css', [], '1' );
    wp_enqueue_style( 'quire-layer1', $base . '/layer1-hooks.css', [ 'quire-tokens' ], '1' );

    if ( $level >= 2 ) {
        wp_enqueue_style(
            'quire-fonts',
            'https://fonts.googleapis.com/css2?family=Libre+Baskerville:ital,wght@0,400;0,700;1,400&family=Inter:wght@400;450;500;600&family=IBM+Plex+Mono:wght@400;500&display=swap',
            [],
            '1'
        );
        wp_enqueue_style( 'quire-layer2', $base . '/layer2-bridge.css', [ 'quire-layer1', 'colors' ], '1' );
    }
}, 999 );
