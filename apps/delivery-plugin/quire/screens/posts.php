<?php
/**
 * The Quire Posts list — Camp 3, built from "Pattern · Posts (DECIDED
 * 2026-07-13)" in Figma. One surface: views with live counts + filters +
 * search inside the table card; selection transforms the toolbar into the
 * bulk bar; quick edit and bulk edit are drawers; trash is instant with
 * an Undo toast. Everything here is REAL data and real actions.
 *
 * Rendered in place of wp-admin/edit.php for post_type=post (see quire.php).
 */

defined( 'ABSPATH' ) || exit;

// ---- read the request ------------------------------------------------
$view    = sanitize_key( $_GET['post_status'] ?? 'all' );
$search  = sanitize_text_field( wp_unslash( $_GET['s'] ?? '' ) );
$month   = (int) ( $_GET['m'] ?? 0 );
$cat     = (int) ( $_GET['cat'] ?? 0 );
$tag     = sanitize_title( wp_unslash( $_GET['tag'] ?? '' ) );
$orderby = sanitize_key( $_GET['orderby'] ?? '' );
$order   = strtoupper( sanitize_key( $_GET['order'] ?? 'DESC' ) ) === 'ASC' ? 'ASC' : 'DESC';
$paged   = max( 1, (int) ( $_GET['paged'] ?? 1 ) );

$counts = quire_posts_counts();
$views  = [
	'all'     => [ __( 'All', 'quire' ), $counts['all'] ],
	'publish' => [ __( 'Published', 'quire' ), $counts['publish'] ],
	'future'  => [ __( 'Scheduled', 'quire' ), $counts['future'] ],
	'draft'   => [ __( 'Drafts', 'quire' ), $counts['draft'] ],
	'pending' => [ __( 'Pending', 'quire' ), $counts['pending'] ],
	'private' => [ __( 'Private', 'quire' ), $counts['private'] ],
	'trash'   => [ __( 'Trash', 'quire' ), $counts['trash'] ],
];
if ( ! isset( $views[ $view ] ) || ( 'all' !== $view && 0 === $views[ $view ][1] ) ) {
	$view = 'all';
}

$args = [
	'post_type'      => 'post',
	'post_status'    => 'all' === $view ? [ 'publish', 'future', 'draft', 'pending', 'private' ] : $view,
	'posts_per_page' => 20,
	'paged'          => $paged,
	'orderby'        => in_array( $orderby, [ 'title', 'date', 'comment_count' ], true ) ? $orderby : 'date',
	'order'          => $order,
	'ignore_sticky_posts' => true,
];
if ( $search ) { $args['s'] = $search; }
if ( $month )  { $args['m'] = $month; }
if ( $cat )    { $args['cat'] = $cat; }
if ( $tag )    { $args['tag'] = $tag; }

$query = new WP_Query( $args );

// months that actually have posts (core's months_dropdown, minimally)
global $wpdb;
$months = $wpdb->get_results(
	"SELECT DISTINCT YEAR(post_date) AS y, MONTH(post_date) AS m FROM {$wpdb->posts}
	 WHERE post_type = 'post' AND post_status NOT IN ('auto-draft','trash')
	 ORDER BY post_date DESC LIMIT 24"
);

$categories = get_categories( [ 'hide_empty' => false ] );
$authors    = get_users( [ 'capability' => 'edit_posts', 'fields' => [ 'ID', 'display_name' ] ] );

// keeps current filters when building view/sort/page links
function quire_posts_url( array $overrides = [] ): string {
	$keep = [];
	foreach ( [ 'post_status', 's', 'm', 'cat', 'tag', 'orderby', 'order', 'paged' ] as $k ) {
		if ( isset( $_GET[ $k ] ) && '' !== $_GET[ $k ] ) {
			$keep[ $k ] = sanitize_text_field( wp_unslash( $_GET[ $k ] ) );
		}
	}
	$q = array_filter( array_merge( $keep, $overrides ), fn( $v ) => '' !== $v && null !== $v );
	return esc_url( add_query_arg( $q, admin_url( 'edit.php' ) ) );
}

function quire_sort_link( string $key, string $label, string $orderby, string $order ): string {
	$is   = $orderby === $key || ( '' === $orderby && 'date' === $key );
	$next = $is && 'ASC' === $order ? 'DESC' : 'ASC';
	$mark = $is ? ( 'ASC' === $order ? ' ↑' : ' ↓' ) : '';
	$safe = str_starts_with( $label, '<svg' ) ? $label : esc_html( $label );
	return '<a href="' . quire_posts_url( [ 'orderby' => $key, 'order' => $next, 'paged' => 1 ] ) . '"' . ( $is ? ' class="is-sorted"' : '' ) . '>' . $safe . $mark . '</a>';
}

// monochrome comment bubble — an emoji would break the one-symbol-family rule
const QUIRE_BUBBLE_SVG = '<svg width="14" height="14" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.4" style="vertical-align:-2px"><path d="M14 10.5c0 .8-.7 1.5-1.5 1.5H6l-3.5 3v-3h-.5C1.2 12 .5 11.3.5 10.5v-7C.5 2.7 1.2 2 2 2h10.5c.8 0 1.5.7 1.5 1.5v7z" transform="translate(.5 -.5)"/></svg>';

$badge_for = function ( WP_Post $p ): array {
	$b = [];
	if ( 'future' === $p->post_status )  { $b[] = __( 'Scheduled', 'quire' ); }
	if ( 'draft' === $p->post_status )   { $b[] = __( 'Draft', 'quire' ); }
	if ( 'pending' === $p->post_status ) { $b[] = __( 'Pending', 'quire' ); }
	if ( 'private' === $p->post_status ) { $b[] = __( 'Private', 'quire' ); }
	if ( '' !== $p->post_password )      { $b[] = __( 'Protected', 'quire' ); }
	if ( is_sticky( $p->ID ) )           { $b[] = __( 'Sticky', 'quire' ); }
	return $b;
};

require_once ABSPATH . 'wp-admin/admin-header.php';
?>
<div class="quire-screen"
     data-ajax="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>"
     data-nonce="<?php echo esc_attr( wp_create_nonce( 'quire_posts' ) ); ?>">

  <header class="qtopbar">
    <div>
      <a class="qcrumb" href="<?php echo esc_url( home_url() ); ?>"><?php echo esc_html( get_bloginfo( 'name' ) ); ?></a>
      <h1 class="qtitle"><?php esc_html_e( 'Posts', 'quire' ); ?></h1>
    </div>
    <div class="qactions">
      <a class="btn btn--primary" href="<?php echo esc_url( admin_url( 'post-new.php' ) ); ?>"><?php esc_html_e( 'New post', 'quire' ); ?></a>
    </div>
  </header>

  <div class="card qtable">

    <div class="qtb" data-mode="rest">
      <div class="qtb__rest">
        <nav class="qviews">
          <?php foreach ( $views as $key => [ $label, $n ] ) : ?>
            <?php // self-pruning: zero-count views render hidden so live
                  // count updates (a first trash, a restore) can reveal them ?>
            <a href="<?php echo quire_posts_url( [ 'post_status' => 'all' === $key ? null : $key, 'paged' => 1 ] ); ?>"
               class="<?php echo $view === $key ? 'is-current' : ''; ?>"
               <?php if ( 'all' !== $key && 0 === $n && $view !== $key ) : ?>style="display:none"<?php endif; ?>
               data-view="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $label ); ?> <span class="qviews__n"><?php echo esc_html( number_format_i18n( $n ) ); ?></span></a>
          <?php endforeach; ?>
        </nav>
        <form class="qtb__filters" method="get" action="<?php echo esc_url( admin_url( 'edit.php' ) ); ?>">
          <?php if ( 'all' !== $view ) : ?><input type="hidden" name="post_status" value="<?php echo esc_attr( $view ); ?>"><?php endif; ?>
          <span class="select-wrap select-wrap--quiet"><select class="dt-select" name="m" onchange="this.form.submit()">
            <option value="0"><?php esc_html_e( 'All dates', 'quire' ); ?></option>
            <?php foreach ( $months as $mo ) : $val = (int) ( $mo->y . str_pad( $mo->m, 2, '0', STR_PAD_LEFT ) ); ?>
              <option value="<?php echo esc_attr( $val ); ?>" <?php selected( $month, $val ); ?>><?php echo esc_html( wp_date( 'F Y', mktime( 0, 0, 0, (int) $mo->m, 1, (int) $mo->y ) ) ); ?></option>
            <?php endforeach; ?>
          </select></span>
          <span class="select-wrap select-wrap--quiet"><select class="dt-select" name="cat" onchange="this.form.submit()">
            <option value="0"><?php esc_html_e( 'All categories', 'quire' ); ?></option>
            <?php foreach ( $categories as $c ) : ?>
              <option value="<?php echo esc_attr( $c->term_id ); ?>" <?php selected( $cat, $c->term_id ); ?>><?php echo esc_html( $c->name ); ?></option>
            <?php endforeach; ?>
          </select></span>
          <input class="dt-search" type="search" name="s" value="<?php echo esc_attr( $search ); ?>" placeholder="<?php esc_attr_e( 'Search posts…', 'quire' ); ?>">
          <?php if ( 'trash' === $view && $counts['trash'] > 0 ) : ?>
            <button type="button" class="btn btn--secondary btn--sm" id="qempty-trash"><?php esc_html_e( 'Empty Trash', 'quire' ); ?></button>
          <?php endif; ?>
        </form>
      </div>
      <div class="qtb__selected" hidden>
        <span class="qtb__count">0</span>
        <span class="qtb__sep"></span>
        <?php if ( 'trash' === $view ) : ?>
          <button type="button" class="qtb__act" data-bulk="untrash"><?php esc_html_e( 'Restore', 'quire' ); ?></button>
          <button type="button" class="qtb__act qtb__act--danger" data-bulk="delete"><?php esc_html_e( 'Delete permanently', 'quire' ); ?></button>
        <?php else : ?>
          <button type="button" class="qtb__act" data-bulk="edit"><?php esc_html_e( 'Edit', 'quire' ); ?></button>
          <button type="button" class="qtb__act qtb__act--danger" data-bulk="trash"><?php esc_html_e( 'Move to Trash', 'quire' ); ?></button>
        <?php endif; ?>
        <span class="qtb__gap"></span>
        <button type="button" class="qtb__act qtb__clear"><?php esc_html_e( 'Clear selection', 'quire' ); ?></button>
      </div>
    </div>

    <?php if ( $query->have_posts() ) : ?>
    <div class="qthead">
      <span class="qcol--cb"><input type="checkbox" id="qselect-all" aria-label="<?php esc_attr_e( 'Select all', 'quire' ); ?>"></span>
      <span class="qcol--title"><?php echo quire_sort_link( 'title', __( 'Title', 'quire' ), $orderby, $order ); ?></span>
      <span class="qcol--author"><?php esc_html_e( 'Author', 'quire' ); ?></span>
      <span class="qcol--cats"><?php esc_html_e( 'Categories', 'quire' ); ?></span>
      <span class="qcol--tags"><?php esc_html_e( 'Tags', 'quire' ); ?></span>
      <span class="qcol--comments" title="<?php esc_attr_e( 'Comments', 'quire' ); ?>"><?php echo quire_sort_link( 'comment_count', QUIRE_BUBBLE_SVG, $orderby, $order ); ?></span>
      <span class="qcol--date"><?php echo quire_sort_link( 'date', __( 'Date', 'quire' ), $orderby, $order ); ?></span>
    </div>

    <?php while ( $query->have_posts() ) : $query->the_post(); $p = get_post();
      $row  = quire_post_row_data( $p );
      $cats = get_the_category( $p->ID );
      $tags = get_the_tags( $p->ID ) ?: [];
      $edit = get_edit_post_link( $p->ID );
    ?>
    <div class="qrow" data-post="<?php echo esc_attr( wp_json_encode( $row ) ); ?>">
      <span class="qcol--cb"><input type="checkbox" class="qrow__cb" value="<?php echo esc_attr( $p->ID ); ?>"></span>
      <span class="qcol--title">
        <span class="qrow__line">
          <a class="qrow__title" href="<?php echo esc_url( $edit ); ?>"><?php echo esc_html( $row['title'] ); ?></a>
          <?php foreach ( $badge_for( $p ) as $b ) : ?><span class="badge badge--neutral"><?php echo esc_html( $b ); ?></span><?php endforeach; ?>
        </span>
        <span class="qrow__acts">
          <?php if ( 'trash' === $view ) : ?>
            <button type="button" class="qlink" data-act="untrash"><?php esc_html_e( 'Restore', 'quire' ); ?></button>
            <button type="button" class="qlink qlink--danger" data-act="delete"><?php esc_html_e( 'Delete permanently', 'quire' ); ?></button>
          <?php else : ?>
            <a class="qlink" href="<?php echo esc_url( $edit ); ?>"><?php esc_html_e( 'Edit', 'quire' ); ?></a>
            <button type="button" class="qlink" data-act="quick"><?php esc_html_e( 'Quick Edit', 'quire' ); ?></button>
            <button type="button" class="qlink qlink--danger" data-act="trash"><?php esc_html_e( 'Trash', 'quire' ); ?></button>
            <?php if ( 'publish' === $p->post_status ) : ?>
              <a class="qlink" href="<?php echo esc_url( get_permalink( $p ) ); ?>"><?php esc_html_e( 'View', 'quire' ); ?></a>
            <?php else : ?>
              <a class="qlink" href="<?php echo esc_url( get_preview_post_link( $p ) ); ?>"><?php esc_html_e( 'Preview', 'quire' ); ?></a>
            <?php endif; ?>
          <?php endif; ?>
        </span>
      </span>
      <span class="qcol--author qcell"><?php echo esc_html( $row['authorName'] ); ?></span>
      <span class="qcol--cats qcell qcell--cats">
        <?php if ( $cats ) : $out = [];
          foreach ( $cats as $c ) { $out[] = '<a href="' . quire_posts_url( [ 'cat' => $c->term_id, 'paged' => 1 ] ) . '">' . esc_html( $c->name ) . '</a>'; }
          echo implode( ', ', $out );
        else : echo '—'; endif; ?>
      </span>
      <span class="qcol--tags qcell qcell--tags">
        <?php if ( $tags ) : $out = [];
          foreach ( $tags as $t ) { $out[] = '<a href="' . quire_posts_url( [ 'tag' => $t->slug, 'paged' => 1 ] ) . '">' . esc_html( $t->name ) . '</a>'; }
          echo implode( ', ', $out );
        else : echo '—'; endif; ?>
      </span>
      <span class="qcol--comments qcell">
        <?php $n = (int) $p->comment_count;
        echo $n ? '<a href="' . esc_url( admin_url( 'edit-comments.php?p=' . $p->ID ) ) . '">' . esc_html( number_format_i18n( $n ) ) . '</a>' : '—'; ?>
      </span>
      <span class="qcol--date">
        <span class="qdate__l1"><?php echo esc_html( $row['dateL1'] ); ?></span>
        <span class="qdate__l2"><?php echo esc_html( $row['dateL2'] ); ?></span>
      </span>
    </div>
    <?php endwhile; wp_reset_postdata(); ?>

    <div class="qtfoot">
      <span><?php printf( esc_html( _n( '%s post', '%s posts', $query->found_posts, 'quire' ) ), esc_html( number_format_i18n( $query->found_posts ) ) ); ?></span>
      <?php if ( $query->max_num_pages > 1 ) : ?>
      <span class="qpager">
        <?php if ( $paged > 1 ) : ?><a href="<?php echo quire_posts_url( [ 'paged' => $paged - 1 ] ); ?>">‹</a><?php else : ?><span class="is-off">‹</span><?php endif; ?>
        <?php printf( esc_html__( '%1$s of %2$s', 'quire' ), (int) $paged, (int) $query->max_num_pages ); ?>
        <?php if ( $paged < $query->max_num_pages ) : ?><a href="<?php echo quire_posts_url( [ 'paged' => $paged + 1 ] ); ?>">›</a><?php else : ?><span class="is-off">›</span><?php endif; ?>
      </span>
      <?php endif; ?>
    </div>

    <?php else : ?>
    <div class="qempty-list">
      <?php echo $search
        ? esc_html( sprintf( __( 'Nothing found for “%s”.', 'quire' ), $search ) )
        : esc_html__( 'No posts here.', 'quire' ); ?>
    </div>
    <?php endif; ?>

  </div>

  <div class="qscrim"></div>

  <aside class="qdrawer" id="qquick" data-authors="<?php echo esc_attr( wp_json_encode( array_map( fn( $a ) => [ 'id' => (int) $a->ID, 'name' => $a->display_name ], $authors ) ) ); ?>">
    <div class="qdrawer__head">
      <div>
        <div class="qdrawer__title"><?php esc_html_e( 'Quick edit', 'quire' ); ?></div>
        <div class="qdrawer__sub"></div>
      </div>
      <button type="button" class="qdrawer__close" aria-label="<?php esc_attr_e( 'Close', 'quire' ); ?>">&times;</button>
    </div>
    <label class="qfield"><span><?php esc_html_e( 'Title', 'quire' ); ?></span><input class="field" name="title"></label>
    <label class="qfield"><span><?php esc_html_e( 'Slug', 'quire' ); ?></span><input class="field" name="slug"></label>
    <div class="qfield-pair">
      <label class="qfield"><span><?php esc_html_e( 'Status', 'quire' ); ?></span>
        <select class="field" name="status">
          <option value="publish"><?php esc_html_e( 'Published', 'quire' ); ?></option>
          <option value="future"><?php esc_html_e( 'Scheduled', 'quire' ); ?></option>
          <option value="draft"><?php esc_html_e( 'Draft', 'quire' ); ?></option>
          <option value="pending"><?php esc_html_e( 'Pending review', 'quire' ); ?></option>
        </select></label>
      <label class="qfield"><span><?php esc_html_e( 'Date', 'quire' ); ?></span><input class="field" type="datetime-local" name="date"></label>
    </div>
    <label class="qfield"><span><?php esc_html_e( 'Categories', 'quire' ); ?></span>
      <span class="qcatlist">
        <?php foreach ( $categories as $c ) : ?>
          <label class="qcheck"><input type="checkbox" name="cats[]" value="<?php echo esc_attr( $c->term_id ); ?>"> <?php echo esc_html( $c->name ); ?></label>
        <?php endforeach; ?>
      </span></label>
    <label class="qfield"><span><?php esc_html_e( 'Tags', 'quire' ); ?></span><input class="field" name="tags" placeholder="<?php esc_attr_e( 'comma, separated', 'quire' ); ?>"></label>
    <div class="qfield-pair">
      <label class="qfield"><span><?php esc_html_e( 'Author', 'quire' ); ?></span>
        <select class="field" name="author">
          <?php foreach ( $authors as $a ) : ?><option value="<?php echo esc_attr( $a->ID ); ?>"><?php echo esc_html( $a->display_name ); ?></option><?php endforeach; ?>
        </select></label>
      <label class="qfield"><span><?php esc_html_e( 'Visibility', 'quire' ); ?></span>
        <select class="field" name="visibility">
          <option value="public"><?php esc_html_e( 'Public', 'quire' ); ?></option>
          <option value="password"><?php esc_html_e( 'Password protected', 'quire' ); ?></option>
          <option value="private"><?php esc_html_e( 'Private', 'quire' ); ?></option>
        </select></label>
    </div>
    <label class="qfield" data-password hidden><span><?php esc_html_e( 'Password', 'quire' ); ?></span><input class="field" name="password"></label>
    <div class="qchecks">
      <label class="qcheck"><input type="checkbox" name="comments"> <?php esc_html_e( 'Allow comments', 'quire' ); ?></label>
      <label class="qcheck"><input type="checkbox" name="pings"> <?php esc_html_e( 'Allow pingbacks', 'quire' ); ?></label>
      <label class="qcheck"><input type="checkbox" name="sticky"> <?php esc_html_e( 'Stick to the top of the blog', 'quire' ); ?></label>
    </div>
    <div class="qdrawer__note"><?php esc_html_e( 'Content is edited in the editor — everything else lives here.', 'quire' ); ?></div>
    <div class="qdrawer__foot">
      <button type="button" class="btn btn--tertiary btn--sm qdrawer__cancel"><?php esc_html_e( 'Cancel', 'quire' ); ?></button>
      <button type="button" class="btn btn--primary btn--sm" id="qquick-save"><?php esc_html_e( 'Update', 'quire' ); ?></button>
    </div>
  </aside>

  <aside class="qdrawer" id="qbulk">
    <div class="qdrawer__head">
      <div>
        <div class="qdrawer__title"></div>
        <div class="qdrawer__sub"></div>
      </div>
      <button type="button" class="qdrawer__close" aria-label="<?php esc_attr_e( 'Close', 'quire' ); ?>">&times;</button>
    </div>
    <label class="qfield"><span><?php esc_html_e( 'Add categories', 'quire' ); ?></span>
      <span class="qcatlist">
        <?php foreach ( $categories as $c ) : ?>
          <label class="qcheck"><input type="checkbox" name="add_cats[]" value="<?php echo esc_attr( $c->term_id ); ?>"> <?php echo esc_html( $c->name ); ?></label>
        <?php endforeach; ?>
      </span></label>
    <label class="qfield"><span><?php esc_html_e( 'Remove categories', 'quire' ); ?></span>
      <span class="qcatlist">
        <?php foreach ( $categories as $c ) : ?>
          <label class="qcheck"><input type="checkbox" name="remove_cats[]" value="<?php echo esc_attr( $c->term_id ); ?>"> <?php echo esc_html( $c->name ); ?></label>
        <?php endforeach; ?>
      </span></label>
    <label class="qfield"><span><?php esc_html_e( 'Add tags', 'quire' ); ?></span><input class="field" name="add_tags" placeholder="<?php esc_attr_e( 'comma, separated', 'quire' ); ?>"></label>
    <label class="qfield"><span><?php esc_html_e( 'Remove tags', 'quire' ); ?></span><input class="field" name="remove_tags" placeholder="<?php esc_attr_e( 'comma, separated', 'quire' ); ?>"></label>
    <div class="qfield-pair">
      <label class="qfield"><span><?php esc_html_e( 'Status', 'quire' ); ?></span>
        <select class="field" name="status">
          <option value=""><?php esc_html_e( 'No change', 'quire' ); ?></option>
          <option value="publish"><?php esc_html_e( 'Published', 'quire' ); ?></option>
          <option value="draft"><?php esc_html_e( 'Draft', 'quire' ); ?></option>
          <option value="pending"><?php esc_html_e( 'Pending review', 'quire' ); ?></option>
          <option value="private"><?php esc_html_e( 'Private', 'quire' ); ?></option>
        </select></label>
      <label class="qfield"><span><?php esc_html_e( 'Author', 'quire' ); ?></span>
        <select class="field" name="author">
          <option value=""><?php esc_html_e( 'No change', 'quire' ); ?></option>
          <?php foreach ( $authors as $a ) : ?><option value="<?php echo esc_attr( $a->ID ); ?>"><?php echo esc_html( $a->display_name ); ?></option><?php endforeach; ?>
        </select></label>
    </div>
    <div class="qfield-pair">
      <label class="qfield"><span><?php esc_html_e( 'Comments', 'quire' ); ?></span>
        <select class="field" name="comments">
          <option value=""><?php esc_html_e( 'No change', 'quire' ); ?></option>
          <option value="open"><?php esc_html_e( 'Allow', 'quire' ); ?></option>
          <option value="closed"><?php esc_html_e( 'Do not allow', 'quire' ); ?></option>
        </select></label>
      <label class="qfield"><span><?php esc_html_e( 'Sticky', 'quire' ); ?></span>
        <select class="field" name="sticky">
          <option value=""><?php esc_html_e( 'No change', 'quire' ); ?></option>
          <option value="stick"><?php esc_html_e( 'Stick', 'quire' ); ?></option>
          <option value="unstick"><?php esc_html_e( 'Unstick', 'quire' ); ?></option>
        </select></label>
    </div>
    <div class="qdrawer__note"><?php esc_html_e( 'Fields left on “No change” are not touched.', 'quire' ); ?></div>
    <div class="qdrawer__foot">
      <button type="button" class="btn btn--tertiary btn--sm qdrawer__cancel"><?php esc_html_e( 'Cancel', 'quire' ); ?></button>
      <button type="button" class="btn btn--primary btn--sm" id="qbulk-save"></button>
    </div>
  </aside>

  <div class="qtoast" hidden>
    <span class="qtoast__msg"></span>
    <button type="button" class="qtoast__undo"><?php esc_html_e( 'Undo', 'quire' ); ?></button>
  </div>

</div>
<?php
require_once ABSPATH . 'wp-admin/admin-footer.php';
exit;
