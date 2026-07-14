<?php
/**
 * Quire Shell — D6, the one-column shell (Figma "Pattern · Shell D6 —
 * pixel-perfect" is the spec; every value below is a token from
 * variables.css or derived from tokens by stated arithmetic).
 *
 * The architecture (all decided 2026-07-13/14, the slow round):
 *   THE SITE LEVEL — the dashboard is the SITE's lobby, not WordPress's.
 *      index.php + update-core.php classify as the 'site' product; its
 *      menu is Home + Updates; the update dot lives here.
 *   ONE COLUMN (size-sidebar 300) — masthead (the site's name, Lora,
 *      = the way home), the current level's menu, the ONE divider,
 *      product rows below it at the site level, the nameplate inside
 *      a product. No rail. No context line in the band, ever.
 *   EVERYTHING TRAVELS — product rows go to their front door; the
 *      nameplate goes BACK to the main menu (hover slides the back
 *      chevron in over the mark — decided 2026-07-14); the masthead
 *      goes Home. The column is STATELESS: always a mirror of the page;
 *      the browser's Back button does the remembering. Changes are
 *      INSTANT (plain page loads).
 *   COLLAPSE — the column narrows to size-rail 80; same surface, same
 *      border, same divider; only the words leave (tooltips speak).
 *      At most one house on screen, ever.
 *   THE BAND — h size-band 60 (= size-masthead: one optical shelf),
 *      NO hairline (spacing separates), title = the page-title role.
 *   Icons: vendored Lucide (ISC), one family; product marks 16 in rows,
 *      20 in the nameplate. The search (⌘K) button ships with R8.
 */

defined( 'ABSPATH' ) || exit;

const QUIRE_SHELL_META = 'quire_shell_collapsed';

function quire_shell_active(): bool {
	return quire_is_enabled() && ! is_network_admin();
}

// The band (and the second-level column that hangs from it) step aside
// where a screen brings a full-surface editor of its own.
function quire_shell_band_active(): bool {
	if ( ! quire_shell_active() ) {
		return false;
	}
	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
	if ( $screen && ( $screen->is_block_editor() || 'site-editor' === $screen->id ) ) {
		return false;
	}
	return true;
}

// ---- classification ---------------------------------------------------

// Manifests are keyed on SLUGS, not labels — translations break labels.
// Some products register full relative URLs (Woo's Payments is
// "admin.php?page=wc-settings&tab=…") — classify on the page param.
// THE SITE LEVEL: the dashboard aggregates every product's widgets and
// updates span every product — they belong to the site, above the products.
function quire_shell_classify_slug( string $slug ): string {
	if ( preg_match( '/[?&]page=([^&]+)/', $slug, $m ) ) {
		$slug = $m[1];
	}
	if ( 'index.php' === $slug || 'update-core.php' === $slug ) {
		return 'site';
	}
	static $core = [
		'edit.php', 'upload.php', 'edit.php?post_type=page',
		'edit-comments.php', 'themes.php', 'plugins.php', 'users.php',
		'profile.php', 'tools.php', 'options-general.php',
	];
	if ( in_array( $slug, $core, true ) ) {
		return 'wp';
	}
	if ( 'woocommerce' === $slug || 'edit.php?post_type=product' === $slug
		|| 'action-scheduler' === $slug
		|| str_starts_with( $slug, 'wc-' ) || str_starts_with( $slug, 'woocommerce-' ) ) {
		return 'woo';
	}
	if ( str_starts_with( $slug, 'jetpack' ) || 'my-jetpack' === $slug ) {
		return 'jp';
	}
	return 'tray';
}

function quire_shell_product_names(): array {
	return [
		'site' => get_bloginfo( 'name' ),
		'wp'   => __( 'WordPress', 'quire' ),
		'woo'  => __( 'WooCommerce', 'quire' ),
		'jp'   => __( 'Jetpack', 'quire' ),
		'tray' => __( 'Plugins', 'quire' ),
	];
}

// A product's eponymous top-level menu dissolves — its children ARE the
// root (S3). The site level hoists the Dashboard menu the same way.
function quire_shell_primary_slugs(): array {
	return [
		'site' => [ 'index.php' ],
		'woo'  => [ 'woocommerce' ],
		'jp'   => [ 'jetpack', 'my-jetpack' ],
	];
}

// Settings-like menus hold co-equal SECTIONS — the page is named by its
// section even at the front door ("General", never a page called "Settings").
function quire_shell_section_menus(): array {
	return [ 'options-general.php' ];
}

// S10: first-level icons, keyed on slugs like everything else. Lucide
// files use stroke="currentColor", so state colors come from CSS.
function quire_shell_icon_name( string $slug ): string {
	if ( false !== strpos( $slug, 'tab=checkout' ) ) {
		return 'credit-card'; // Woo's Payments registers a wc-settings deep link.
	}
	if ( preg_match( '/[?&]page=([^&]+)/', $slug, $m ) ) {
		$slug = $m[1];
	}
	static $map = [
		// the site level
		'index.php'       => 'house',
		'update-core.php' => 'rotate-cw',
		// WordPress
		'edit.php'                => 'pen-line',
		'upload.php'              => 'image',
		'edit.php?post_type=page' => 'file',
		'edit-comments.php'       => 'message-square',
		'themes.php'              => 'panels-top-left',
		'plugins.php'             => 'box',
		'users.php'               => 'users',
		'profile.php'             => 'circle-user',
		'tools.php'               => 'wrench',
		'options-general.php'     => 'settings',
		// WooCommerce
		'wc-admin'                          => 'house',
		'wc-orders'                         => 'shopping-cart',
		'edit.php?post_type=shop_order'     => 'shopping-cart',
		'wc-admin&path=/customers'          => 'users',
		'coupons-moved'                     => 'ticket',
		'wc-reports'                        => 'chart-line',
		'wc-settings'                       => 'settings',
		'wc-status'                         => 'activity',
		'wc-admin&path=/extensions'         => 'blocks',
		'edit.php?post_type=product'        => 'package',
		'wc-admin&path=/analytics/overview' => 'chart-column',
		'woocommerce-marketing'             => 'megaphone',
		// Jetpack
		'my-jetpack'         => 'house',
		'jetpack'            => 'zap',
		'jetpack#/dashboard' => 'zap',
		'jetpack#/settings'  => 'settings',
		'stats'              => 'chart-line',
		'akismet-key-config' => 'shield',
		'jetpack-search'     => 'search',
	];
	return $map[ $slug ] ?? 'puzzle';
}

function quire_shell_icon_svg( string $slug ): string {
	static $cache = [];
	$name = quire_shell_icon_name( $slug );
	if ( ! isset( $cache[ $name ] ) ) {
		$file           = __DIR__ . '/assets/icons/' . $name . '.svg';
		$cache[ $name ] = file_exists( $file ) ? (string) file_get_contents( $file ) : '';
	}
	return $cache[ $name ];
}

// The product marks (rows: 16 box · nameplate: 20 box — masks in CSS).
function quire_shell_mark( string $key ): string {
	static $marks = null;
	if ( null === $marks ) {
		$marks = [
			'wp'   => '<span class="qshell__mark qshell__mark--wp"></span>',
			'woo'  => '<span class="qshell__mark qshell__mark--woo"></span>',
			'jp'   => '<span class="qshell__mark qshell__mark--jp"></span>',
			'tray' => '<span class="qshell__icon">' . quire_shell_icon_svg( '__tray__' ) . '</span>',
		];
		// the tray wears the grid glyph, same stroke family
		$marks['tray'] = '<span class="qshell__icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"><rect x="4" y="4" width="7" height="7" rx="1.5"/><rect x="13" y="4" width="7" height="7" rx="1.5"/><rect x="4" y="13" width="7" height="7" rx="1.5"/><rect x="13" y="13" width="7" height="7" rx="1.5"/></svg></span>';
	}
	return $marks[ $key ] ?? '';
}

// R2: activation order for the guests; site first, WordPress second, tray last.
function quire_shell_activation_rank(): array {
	$prefix = [ 'woo' => 'woocommerce/', 'jp' => 'jetpack/' ];
	$rank   = [];
	foreach ( (array) get_option( 'active_plugins', [] ) as $pos => $file ) {
		foreach ( $prefix as $key => $p ) {
			if ( ! isset( $rank[ $key ] ) && str_starts_with( $file, $p ) ) {
				$rank[ $key ] = $pos;
			}
		}
	}
	return $rank;
}

// Menu titles arrive as HTML with count spans and screen-reader text.
function quire_shell_label( string $raw ): string {
	$label = trim( wp_strip_all_tags( preg_replace( '/<span[^>]*>.*?<\/span>/s', '', $raw ) ) );
	return '' !== $label ? $label : trim( wp_strip_all_tags( $raw ) );
}

function quire_shell_badge( string $raw ): int {
	return preg_match( '/count-(\d+)/', $raw, $m ) ? (int) $m[1] : 0;
}

// Core's own URL grammar: .php slugs are direct, plugin pages hang off
// their parent when the parent is a real file, else off admin.php.
function quire_shell_item_url( string $slug, string $parent = '' ): string {
	if ( preg_match( '#^https?://#', $slug ) ) {
		return $slug;
	}
	if ( false !== strpos( $slug, '.php' ) ) {
		return admin_url( $slug );
	}
	if ( $parent && false !== strpos( $parent, '.php' ) && 'admin.php' !== $parent ) {
		return admin_url( add_query_arg( 'page', $slug, $parent ) );
	}
	return admin_url( 'admin.php?page=' . $slug );
}

/**
 * Read $menu/$submenu into the product model. The current-item highlight
 * reuses core's own resolution; $self is derived HERE with core's exact
 * regexes because menu-header.php only sets it after the body tag, and
 * this model is first computed in the body-class filter.
 */
function quire_shell_products(): array {
	global $menu, $submenu, $parent_file, $submenu_file, $plugin_page;

	$self = preg_replace( '|^.*/wp-admin/network/|i', '', $_SERVER['PHP_SELF'] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
	$self = preg_replace( '|^.*/wp-admin/|i', '', $self );
	$sub_current = $submenu_file ?: $self;

	// wc-admin routes many screens through one plugin page, told apart by
	// ?path= — match path-qualified slugs exactly; the bare $plugin_page
	// match is only honest when the request carries no path.
	$request_slug = '';
	$has_path     = isset( $_GET['path'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( $plugin_page ) {
		$request_slug = $plugin_page . ( $has_path ? '&path=' . sanitize_text_field( wp_unslash( $_GET['path'] ) ) : '' ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	}

	$names    = quire_shell_product_names();
	$products = [];

	foreach ( (array) $menu as $m ) {
		$slug = $m[2] ?? '';
		if ( '' === $slug || false !== strpos( $m[4] ?? '', 'wp-menu-separator' ) ) {
			continue;
		}
		$key = quire_shell_classify_slug( $slug );

		$children = [];
		foreach ( (array) ( $submenu[ $slug ] ?? [] ) as $s ) {
			$s_slug  = $s[2] ?? '';
			$s_label = quire_shell_label( $s[0] ?? '' );
			if ( '' === $s_slug || '' === $s_label ) {
				continue;
			}
			// a grafted item names its true origin, quietly
			$s_key = quire_shell_classify_slug( $s_slug );
			$via   = ( $s_key !== $key && 'tray' !== $s_key && 'site' !== $s_key && isset( $names[ $s_key ] ) )
				? $names[ $s_key ] : '';
			$children[] = [
				'slug'    => $s_slug,
				'label'   => $s_label,
				'url'     => quire_shell_item_url( $s_slug, $slug ),
				'badge'   => quire_shell_badge( $s[0] ?? '' ),
				'via'     => $via,
				'current' => $s_slug === $sub_current
					|| ( $request_slug && $s_slug === $request_slug )
					|| ( $plugin_page && ! $has_path && $s_slug === $plugin_page ),
			];
		}

		$label = quire_shell_label( $m[0] ?? '' );
		if ( '' === $label ) {
			continue;
		}

		if ( ! isset( $products[ $key ] ) ) {
			$products[ $key ] = [ 'key' => $key, 'name' => $names[ $key ], 'items' => [] ];
		}
		$products[ $key ]['items'][] = [
			'slug'     => $slug,
			'label'    => $label,
			'url'      => quire_shell_item_url( $slug ),
			'badge'    => quire_shell_badge( $m[0] ?? '' ),
			'current'  => $slug === $parent_file || ( $request_slug && $slug === $request_slug ),
			'children' => $children,
		];
	}

	// One "you are here": a child match is the precise signal.
	$has_child_current = false;
	foreach ( $products as $p ) {
		foreach ( $p['items'] as $i ) {
			if ( array_filter( $i['children'], fn( $c ) => $c['current'] ) ) {
				$has_child_current = true;
				break 2;
			}
		}
	}
	if ( $has_child_current ) {
		foreach ( $products as &$p ) {
			foreach ( $p['items'] as &$i ) {
				if ( $i['current'] && ! array_filter( $i['children'], fn( $c ) => $c['current'] ) ) {
					$i['current'] = false;
				}
			}
			unset( $i );
		}
		unset( $p );
	}

	// Order: the site above everything, WordPress first among products,
	// guests in activation order, the tray last.
	$rank  = quire_shell_activation_rank();
	$known = array_keys( $products );
	usort( $known, function ( $a, $b ) use ( $rank ) {
		$w = fn( $k ) => 'site' === $k ? -2 : ( 'wp' === $k ? -1 : ( 'tray' === $k ? PHP_INT_MAX : ( $rank[ $k ] ?? PHP_INT_MAX - 1 ) ) );
		return $w( $a ) <=> $w( $b );
	} );

	$ordered = [];
	foreach ( $known as $key ) {
		$p            = $products[ $key ];
		$p['entries'] = quire_shell_entries( $key, $p['items'] );
		$p['current'] = (bool) array_filter( $p['items'], fn( $i ) => $i['current'] || array_filter( $i['children'], fn( $c ) => $c['current'] ) );
		$p['badge']   = array_sum( array_column( $p['items'], 'badge' ) ) > 0
			|| (bool) array_filter( $p['items'], fn( $i ) => array_sum( array_column( $i['children'], 'badge' ) ) > 0 );
		// the front door: where the product's row (or nameplate) travels
		$p['door'] = $p['entries'] ? ( $p['entries'][0]['children'][0]['url'] ?? $p['entries'][0]['url'] ?? admin_url() ) : admin_url();
		// the hoisted menu's own label survives for the band title
		$p['front_label'] = $p['items'][0]['label'] ?? $p['name'];
		unset( $p['items'] );
		$ordered[] = $p;
	}
	return $ordered;
}

/**
 * The display model:
 *   link    — a leaf row (icon + label)
 *   parent  — a row that travels to its front door; children feed the
 *             second-level column beside the content
 *   section — tray only: an unknown plugin's name over its own items
 */
function quire_shell_entries( string $key, array $items ): array {
	if ( 'tray' === $key ) {
		return array_map( function ( $i ) {
			$links = $i['children'] ?: [ [ 'slug' => $i['slug'], 'label' => $i['label'], 'url' => $i['url'], 'badge' => $i['badge'], 'via' => '', 'current' => $i['current'] ] ];
			return [ 'type' => 'section', 'label' => $i['label'], 'children' => $links ];
		}, $items );
	}

	$entries = [];
	$primary = quire_shell_primary_slugs()[ $key ] ?? [];

	foreach ( $items as $i ) {
		if ( in_array( $i['slug'], $primary, true ) && $i['children'] ) {
			foreach ( $i['children'] as $c ) {
				$entries[] = [ 'type' => 'link', 'icon' => quire_shell_icon_svg( $c['slug'] ) ] + $c;
			}
			continue;
		}
		if ( $i['children'] ) {
			$entries[] = [
				'type'     => 'parent',
				'slug'     => $i['slug'],
				'icon'     => quire_shell_icon_svg( $i['slug'] ),
				'label'    => $i['label'],
				'url'      => $i['url'],
				'badge'    => $i['badge'] || array_sum( array_column( $i['children'], 'badge' ) ) > 0 ? 1 : 0,
				'current'  => $i['current'] || (bool) array_filter( $i['children'], fn( $c ) => $c['current'] ),
				'children' => $i['children'],
			];
			continue;
		}
		$entries[] = [
			'type'    => 'link',
			'slug'    => $i['slug'],
			'icon'    => quire_shell_icon_svg( $i['slug'] ),
			'label'   => $i['label'],
			'url'     => $i['url'],
			'badge'   => $i['badge'],
			'via'     => '',
			'current' => $i['current'],
		];
	}
	return $entries;
}

/**
 * One computed model per request. active = the product whose page this is
 * (the site level when nothing deeper claims it). subnav = the current
 * menu's children, rendered beside the content. band = the page's title.
 */
function quire_shell_model(): array {
	static $model = null;
	if ( null !== $model ) {
		return $model;
	}
	$products = quire_shell_products();
	$active   = null;
	foreach ( $products as $p ) {
		if ( $p['current'] ) {
			$active = $p;
			break;
		}
	}
	$active = $active ?? $products[0];

	$subnav = null;
	if ( quire_shell_band_active() ) {
		foreach ( $active['entries'] as $e ) {
			if ( 'parent' === $e['type'] && $e['current'] ) {
				$subnav = [ 'label' => $e['label'], 'children' => $e['children'] ];
				break;
			}
		}
	}

	// The band's title (no context line — the column carries the context).
	// Front door → the area's name; deeper → the child's name; Settings-like
	// menus name by SECTION even at the front door. The site's front door
	// is the Dashboard menu's own name.
	$title = '';
	foreach ( $active['entries'] as $e ) {
		if ( 'section' === $e['type'] ) {
			foreach ( $e['children'] as $c ) {
				if ( $c['current'] ) {
					$title = $c['label'];
				}
			}
			continue;
		}
		if ( empty( $e['current'] ) ) {
			continue;
		}
		if ( 'parent' === $e['type'] ) {
			$cur = null;
			foreach ( $e['children'] as $c ) {
				if ( $c['current'] ) {
					$cur = $c;
					break;
				}
			}
			$sections = in_array( $e['slug'], quire_shell_section_menus(), true );
			if ( $cur && ( $sections || $cur['slug'] !== $e['slug'] ) ) {
				$title = $cur['label'];
			} else {
				$title = $e['label'];
			}
		} else {
			// hoisted front doors carry the menu's own name (Home → Dashboard)
			$primary = quire_shell_primary_slugs()[ $active['key'] ][0] ?? null;
			$title   = ( $primary && $e['slug'] === $primary ) ? $active['front_label'] : $e['label'];
		}
		break;
	}
	if ( '' === $title ) {
		$title = get_admin_page_title();
	}

	$model = [
		'products' => $products,
		'active'   => $active,
		'subnav'   => $subnav,
		'title'    => $title,
	];
	return $model;
}

// ---- rendering ----------------------------------------------------------

function quire_shell_dot(): string {
	return '<span class="qshell__dot"></span>';
}

// The words survive collapse in the tooltip (translucent ink, right side).
function quire_shell_tip( string $label ): string {
	return '<span class="qshell__tip">' . esc_html( $label ) . '</span>';
}

function quire_shell_link_html( array $e ): string {
	return '<a class="qshell__row has-tip' . ( $e['current'] ? ' is-current' : '' ) . '" href="' . esc_url( $e['url'] ) . '">'
		. ( ! empty( $e['icon'] ) ? '<span class="qshell__icon">' . $e['icon'] . '</span>' : '' )
		. '<span class="qshell__label">' . esc_html( $e['label'] ) . '</span>'
		. ( ! empty( $e['via'] ) ? '<span class="qshell__via">' . esc_html( sprintf( __( 'via %s', 'quire' ), $e['via'] ) ) . '</span>' : '' )
		. ( ! empty( $e['badge'] ) ? quire_shell_dot() : '' )
		. quire_shell_tip( $e['label'] )
		. '</a>';
}

// A product's row at the site level: mark + name + › — travels to the door.
function quire_shell_product_row_html( array $p ): string {
	return '<a class="qshell__row qshell__row--product has-tip" href="' . esc_url( $p['door'] ) . '">'
		. quire_shell_mark( $p['key'] )
		. '<span class="qshell__label">' . esc_html( $p['name'] ) . '</span>'
		. ( $p['badge'] ? quire_shell_dot() : '<span class="qshell__chev"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M9 6l6 6-6 6"/></svg></span>' )
		. quire_shell_tip( $p['name'] )
		. '</a>';
}

function quire_shell_menu_html( array $entries ): string {
	$html = '';
	foreach ( $entries as $e ) {
		if ( 'section' === $e['type'] ) {
			$html .= '<div class="qshell__section">' . esc_html( $e['label'] ) . '</div>';
			foreach ( $e['children'] as $c ) {
				$c['icon'] = ''; // tray rows carry no icons — all-puzzle is noise
				$html     .= quire_shell_link_html( $c );
			}
			continue;
		}
		$html .= quire_shell_link_html( $e );
	}
	return $html;
}

add_action( 'in_admin_header', function () {
	if ( ! quire_shell_active() ) {
		return;
	}
	$model    = quire_shell_model();
	$products = $model['products'];
	$active   = $model['active'];
	$subnav   = $model['subnav'];

	// THE BAND — title + actions, no context line, no hairline. Normal flow
	// inside #wpcontent: it spans the second-level column AND the content,
	// and scrolls away with the page.
	if ( quire_shell_band_active() ) :
	?>
	<div class="qshell-band" id="qshell-band">
		<h1 class="qshell-band__title"><?php echo esc_html( $model['title'] ); ?></h1>
		<div class="qshell-band__actions" id="qshell-band-actions"><?php
			echo apply_filters( 'quire_shell_band_actions', '' ); // phpcs:ignore WordPress.Security.EscapeOutput
		?></div>
	</div>
	<?php
	endif;

	// S9: the current menu's pages, plain text, beside the content —
	// real page content (grid column of #wpcontent), sticky live.
	if ( $subnav ) :
	?>
	<nav class="qshell__sub" aria-label="<?php echo esc_attr( $subnav['label'] ); ?>">
		<?php
		foreach ( $subnav['children'] as $c ) {
			echo '<a class="qshell__sub-link' . ( $c['current'] ? ' is-current' : '' ) . '" href="' . esc_url( $c['url'] ) . '">'
				. '<span class="qshell__label">' . esc_html( $c['label'] ) . ( $c['badge'] ? quire_shell_dot() : '' ) . '</span>'
				. ( $c['via'] ? '<span class="qshell__via">' . esc_html( sprintf( __( 'via %s', 'quire' ), $c['via'] ) ) . '</span>' : '' )
				. '</a>';
		}
		?>
	</nav>
	<?php
	endif;

	$user      = wp_get_current_user();
	$initial   = mb_strtoupper( mb_substr( $user->display_name ?: $user->user_login, 0, 1 ) );
	$collapsed = (bool) get_user_meta( $user->ID, QUIRE_SHELL_META, true );
	$is_site   = 'site' === $active['key'];
	?>
	<div id="quire-shell" class="qshell<?php echo $collapsed ? ' is-collapsed' : ''; ?>"
		data-ajax="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>"
		data-nonce="<?php echo esc_attr( wp_create_nonce( 'quire_shell' ) ); ?>"
		data-label-collapse="<?php esc_attr_e( 'Collapse sidebar', 'quire' ); ?>"
		data-label-expand="<?php esc_attr_e( 'Expand sidebar', 'quire' ); ?>">

		<div class="qshell__masthead">
			<a class="qshell__wordmark" href="<?php echo esc_url( admin_url() ); ?>"><?php echo esc_html( get_bloginfo( 'name' ) ); ?></a>
			<?php /* the search (⌘K) icon button ships with R8 — no dead doors */ ?>
			<button type="button" class="qshell__iconbtn has-tip" id="qshell-toggle"
				aria-label="<?php echo esc_attr( $collapsed ? __( 'Expand sidebar', 'quire' ) : __( 'Collapse sidebar', 'quire' ) ); ?>">
				<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><rect x="3" y="4" width="18" height="16" rx="2"/><line x1="9.5" y1="4" x2="9.5" y2="20"/></svg>
				<span class="qshell__tip qshell__tip--toggle"><?php echo esc_html( $collapsed ? __( 'Expand sidebar', 'quire' ) : __( 'Collapse sidebar', 'quire' ) ); ?></span>
			</button>
		</div>

		<nav class="qshell__menu" aria-label="<?php echo esc_attr( $active['name'] ); ?>">
			<?php if ( ! $is_site ) : ?>
				<?php /* the nameplate is the way BACK — click closes the product and
				         returns to the main menu; hovering slides the chevron in
				         over the mark to say so before you commit. */ ?>
				<a class="qshell__nameplate has-tip" href="<?php echo esc_url( admin_url() ); ?>"
					aria-label="<?php esc_attr_e( 'Back to the main menu', 'quire' ); ?>">
					<span class="qshell__np-glyph">
						<?php echo quire_shell_mark( $active['key'] ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
						<span class="qshell__np-back"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg></span>
					</span>
					<span class="qshell__label"><?php echo esc_html( $active['name'] ); ?></span>
					<?php echo quire_shell_tip( __( 'Back to the main menu', 'quire' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
				</a>
			<?php endif; ?>

			<?php echo quire_shell_menu_html( $active['entries'] ); // phpcs:ignore WordPress.Security.EscapeOutput ?>

			<?php if ( $is_site ) : ?>
				<div class="qshell__divider"></div>
				<?php
				foreach ( $products as $p ) {
					if ( 'site' === $p['key'] ) {
						continue;
					}
					echo quire_shell_product_row_html( $p ); // phpcs:ignore WordPress.Security.EscapeOutput
				}
				if ( current_user_can( 'install_plugins' ) ) {
					echo '<a class="qshell__row qshell__row--quiet has-tip" href="' . esc_url( admin_url( 'plugin-install.php' ) ) . '">'
						. '<span class="qshell__icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg></span>'
						. '<span class="qshell__label">' . esc_html__( 'Add plugins', 'quire' ) . '</span>'
						. quire_shell_tip( __( 'Add plugins', 'quire' ) )
						. '</a>';
				}
				?>
			<?php endif; ?>
		</nav>

		<div class="qshell__account">
			<button type="button" class="qshell__acct" id="qshell-acct" aria-haspopup="true" aria-expanded="false">
				<span class="qshell__avatar"><?php echo esc_html( $initial ); ?></span>
			</button>
		</div>

		<div class="qshell__pop" id="qshell-pop" hidden>
			<div class="qshell__pop-id">
				<span class="qshell__pop-name"><?php echo esc_html( $user->display_name ); ?></span>
				<span class="qshell__pop-mail"><?php echo esc_html( $user->user_email ); ?></span>
			</div>
			<div class="qshell__pop-sep"></div>
			<?php /* Visit site moved to the band on Home — one thing, one place */ ?>
			<a href="<?php echo esc_url( get_edit_profile_url() ); ?>"><?php esc_html_e( 'Edit profile', 'quire' ); ?></a>
			<a href="<?php echo esc_url( wp_logout_url() ); ?>"><?php esc_html_e( 'Log out', 'quire' ); ?></a>
		</div>
	</div>
	<?php
}, 1 );

// Body classes drive the #wpcontent margins and the subnav grid.
add_filter( 'admin_body_class', function ( $classes ) {
	if ( quire_shell_active() ) {
		$classes .= ' quire-shell';
		if ( get_user_meta( get_current_user_id(), QUIRE_SHELL_META, true ) ) {
			$classes .= ' quire-shell-collapsed';
		}
		if ( quire_shell_model()['subnav'] ) {
			$classes .= ' quire-shell-subnav';
		}
		if ( quire_shell_band_active() ) {
			$classes .= ' quire-shell-band';
		}
	}
	return $classes;
} );

// Collapse persists per user, like core's menu fold.
add_action( 'wp_ajax_quire_shell_state', function () {
	check_ajax_referer( 'quire_shell', 'nonce' );
	update_user_meta( get_current_user_id(), QUIRE_SHELL_META, empty( $_POST['collapsed'] ) ? 0 : 1 );
	wp_send_json_success();
} );
