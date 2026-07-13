<?php
/**
 * The Quire Dashboard — rebuilt from the Figma widget library
 * ("Dashboard — Widget Library" page, H4 default arrangement) with the
 * H2c customize mode: the whole card drags (no grip), × to remove, a
 * picker drawer to add widgets back, auto-saved per user.
 *
 * Rendered in place of wp-admin/index.php (see quire.php). Everything on
 * this screen is REAL: live counts, the actual moderation queue with
 * working actions, real scheduled + published posts, a Quick Draft that
 * saves, the real site-health result, the real WordPress news feed.
 */

defined( 'ABSPATH' ) || exit;

// quire_day_phrase() lives in quire.php — shared with the posts screen
// and the ajax row renderers.

// ---- real data ------------------------------------------------------
$counts        = wp_count_posts();
$published     = (int) $counts->publish;
$drafts        = (int) $counts->draft;
$page_counts   = wp_count_posts( 'page' );
$pages         = (int) $page_counts->publish;
$comment_count = wp_count_comments();
$in_queue      = (int) $comment_count->moderated;
$comments_all  = (int) $comment_count->total_comments;
$media_counts  = (array) wp_count_attachments();
$media_total   = array_sum( array_map( 'intval', $media_counts ) );

$pending     = get_comments( [ 'status' => 'hold', 'number' => 3 ] );
$scheduled   = get_posts( [ 'post_status' => 'future', 'numberposts' => 2, 'orderby' => 'date', 'order' => 'ASC' ] );
$recent      = wp_get_recent_posts( [ 'numberposts' => 3, 'post_status' => 'publish' ], OBJECT );
$draft_posts = get_posts( [ 'post_status' => 'draft', 'numberposts' => 2, 'orderby' => 'modified', 'order' => 'DESC' ] );

$core_update = null;
$update_data = get_site_transient( 'update_core' );
if ( $update_data && ! empty( $update_data->updates ) && 'upgrade' === $update_data->updates[0]->response ) {
	$core_update = $update_data->updates[0]->current;
}

$health      = get_transient( 'health-check-site-status-result' );
$health_data = $health ? json_decode( $health, true ) : null;
$health_bad  = $health_data ? (int) $health_data['critical'] + (int) $health_data['recommended'] : null;

$draft_saved = isset( $_GET['quire-draft'] ) && 'saved' === $_GET['quire-draft'];

// ---- Welcome: five real setup steps ---------------------------------
$sample_post = get_page_by_path( 'hello-world', OBJECT, 'post' );
$posts_any   = $published + $drafts + count( $scheduled );
if ( $sample_post && 'publish' === $sample_post->post_status ) {
	$posts_any--; // the sample doesn't count as "your first post"
}

// "Style your site" — done once the Site Editor has saved user styles
// for the current theme (the wp_global_styles post exists with content).
$styled = false;
$gs     = get_posts( [
	'post_type'   => 'wp_global_styles',
	'post_status' => 'publish',
	'numberposts' => 1,
	'tax_query'   => [ [ 'taxonomy' => 'wp_theme', 'field' => 'name', 'terms' => get_stylesheet() ] ],
] );
if ( $gs && strlen( $gs[0]->post_content ) > 60 ) {
	$styled = true;
}

$steps = [
	[
		'label' => __( 'Choose your theme', 'quire' ),
		'desc'  => __( 'The design your visitors see.', 'quire' ),
		'done'  => get_option( 'stylesheet' ) !== WP_DEFAULT_THEME,
		'url'   => admin_url( 'themes.php' ),
	],
	[
		'label' => __( 'Set your site title and tagline', 'quire' ),
		'desc'  => __( 'How your site introduces itself.', 'quire' ),
		'done'  => '' !== get_option( 'blogdescription' ) || ! in_array( get_option( 'blogname' ), [ 'My WordPress Website', 'WordPress' ], true ),
		'url'   => admin_url( 'options-general.php' ),
	],
	[
		'label' => __( 'Write your first post', 'quire' ),
		'desc'  => __( 'A draft counts — just start.', 'quire' ),
		'done'  => $posts_any > 0,
		'url'   => admin_url( 'post-new.php' ),
	],
	[
		'label' => __( 'Set a site icon', 'quire' ),
		'desc'  => __( 'Shown in browser tabs and bookmarks.', 'quire' ),
		'done'  => (int) get_option( 'site_icon' ) > 0,
		'url'   => admin_url( 'options-general.php' ),
	],
	[
		'label' => __( 'Style your site', 'quire' ),
		'desc'  => __( 'Colours and fonts for the whole site, in the Site Editor.', 'quire' ),
		'done'  => $styled,
		'url'   => admin_url( 'site-editor.php' ),
	],
];
$steps_done        = count( array_filter( array_column( $steps, 'done' ) ) );
$welcome_dismissed = (bool) get_user_meta( get_current_user_id(), 'quire_welcome_dismissed', true );
$dismiss_url       = wp_nonce_url( admin_url( 'admin-post.php?action=quire_dismiss_welcome' ), 'quire_dismiss_welcome' );

// ---- News & events: the real wordpress.org news feed ----------------
$news_items = null; // null = unreachable
if ( ! function_exists( 'fetch_feed' ) ) {
	include_once ABSPATH . WPINC . '/feed.php';
}
add_filter( 'wp_feed_options', static function ( $feed ) {
	$feed->set_timeout( 5 );
} );
$feed = fetch_feed( 'https://wordpress.org/news/feed/' );
if ( ! is_wp_error( $feed ) ) {
	$news_items = [];
	foreach ( $feed->get_items( 0, 3 ) as $item ) {
		$news_items[] = [ 'title' => $item->get_title(), 'url' => $item->get_permalink() ];
	}
}

// ---- widget registry --------------------------------------------------
// Each widget: title used in the picker drawer, one-line picker
// description (from the H3 mock), in_picker = false means remove is
// final (Welcome). Every widget gets the same width; height hugs content.
// 'source' groups the drawer — plugin sections (WooCommerce, …) join
// this registry when the plugin-widget bridge (R1) lands.
$quire_widgets = [
	'welcome' => [
		'title'     => __( 'Welcome', 'quire' ),
		'desc'      => '',
		'in_picker' => false,
		'source'    => 'Quire',
		'render'    => function () use ( $steps, $steps_done, $dismiss_url ) { ?>
			<?php if ( $steps_done < count( $steps ) ) : ?>
			<div class="card__head qwelcome__head">
				<div class="card__title"><?php esc_html_e( 'Welcome — set up your site', 'quire' ); ?></div>
				<a class="qwelcome__dismiss" href="<?php echo esc_url( $dismiss_url ); ?>"><?php esc_html_e( 'Dismiss', 'quire' ); ?></a>
			</div>
			<div class="card__body qwelcome__body">
				<div class="qwelcome__count"><?php printf( esc_html__( '%1$s of %2$s steps done', 'quire' ), (int) $steps_done, count( $steps ) ); ?></div>
				<div class="progress qwelcome__progress"><div class="progress__fill" style="width:<?php echo esc_attr( round( $steps_done / count( $steps ) * 100 ) ); ?>%"></div></div>
				<div class="checklist qwelcome__list">
					<?php foreach ( $steps as $step ) : ?>
						<?php if ( $step['done'] ) : ?>
						<div class="checklist__item done">
							<span class="checklist__mark"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg></span>
							<span class="checklist__label"><?php echo esc_html( $step['label'] ); ?></span>
						</div>
						<?php else : ?>
						<a class="checklist__item qwelcome__todo" href="<?php echo esc_url( $step['url'] ); ?>">
							<span class="checklist__mark"></span>
							<span class="checklist__label">
								<span class="qwelcome__steplabel"><?php echo esc_html( $step['label'] ); ?></span>
								<span class="qwelcome__stepdesc"><?php echo esc_html( $step['desc'] ); ?></span>
							</span>
							<span class="qchev" aria-hidden="true"></span>
						</a>
						<?php endif; ?>
					<?php endforeach; ?>
				</div>
			</div>
			<?php else : ?>
			<div class="qwelcome__alldone">
				<span class="qwelcome__bigmark"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg></span>
				<div class="qwelcome__donetitle"><?php esc_html_e( 'You’re all set', 'quire' ); ?></div>
				<div class="qwelcome__donebody"><?php esc_html_e( 'Every setup step is complete — nicely done.', 'quire' ); ?></div>
				<a class="btn btn--primary" href="<?php echo esc_url( $dismiss_url ); ?>"><?php esc_html_e( 'Dismiss', 'quire' ); ?></a>
			</div>
			<?php endif; ?>
		<?php },
	],
	'overview' => [
		'title'     => __( 'Overview', 'quire' ),
		'desc'      => __( 'Published posts, pages, media at a glance.', 'quire' ),
		'in_picker' => true,
		'source'    => 'Quire',
		'render'    => function () use ( $published, $drafts, $pages, $comments_all, $in_queue, $media_total, $sample_post ) { ?>
			<div class="card__head">
				<div class="card__title"><?php esc_html_e( 'Overview', 'quire' ); ?></div>
			</div>
			<div class="card__body qov">
				<a class="qov__cell" href="<?php echo esc_url( admin_url( 'edit.php' ) ); ?>">
					<span class="qov__value"><?php echo esc_html( number_format_i18n( $published ) ); ?></span>
					<span class="qov__label"><?php echo esc_html( _n( 'post', 'posts', $published, 'quire' ) ); ?></span>
					<?php if ( $drafts > 0 ) : ?>
						<span class="qov__sub"><?php printf( esc_html( _n( '%s draft waiting', '%s drafts waiting', $drafts, 'quire' ) ), esc_html( number_format_i18n( $drafts ) ) ); ?></span>
					<?php elseif ( 1 === $published && $sample_post ) : ?>
						<span class="qov__sub"><?php esc_html_e( 'the sample post', 'quire' ); ?></span>
					<?php endif; ?>
				</a>
				<a class="qov__cell" href="<?php echo esc_url( admin_url( 'edit.php?post_type=page' ) ); ?>">
					<span class="qov__value"><?php echo esc_html( number_format_i18n( $pages ) ); ?></span>
					<span class="qov__label"><?php echo esc_html( _n( 'page', 'pages', $pages, 'quire' ) ); ?></span>
					<?php if ( 1 === $pages && get_page_by_path( 'sample-page' ) ) : ?>
						<span class="qov__sub"><?php esc_html_e( 'the sample page', 'quire' ); ?></span>
					<?php endif; ?>
				</a>
				<a class="qov__cell" href="<?php echo esc_url( admin_url( 'edit-comments.php' ) ); ?>">
					<span class="qov__value"><?php echo esc_html( number_format_i18n( $comments_all ) ); ?></span>
					<span class="qov__label"><?php echo esc_html( _n( 'comment', 'comments', $comments_all, 'quire' ) ); ?></span>
					<?php if ( $in_queue > 0 ) : ?>
						<span class="qov__sub"><?php printf( esc_html( _n( '%s in the queue', '%s in the queue', $in_queue, 'quire' ) ), esc_html( number_format_i18n( $in_queue ) ) ); ?></span>
					<?php endif; ?>
				</a>
				<a class="qov__cell" href="<?php echo esc_url( admin_url( 'upload.php' ) ); ?>">
					<span class="qov__value"><?php echo esc_html( number_format_i18n( $media_total ) ); ?></span>
					<span class="qov__label"><?php esc_html_e( 'media', 'quire' ); ?></span>
				</a>
			</div>
			<div class="qov__foot"><a href="<?php echo esc_url( admin_url( 'about.php' ) ); ?>" title="<?php esc_attr_e( 'What’s new in this version', 'quire' ); ?>"><?php printf( esc_html__( 'WordPress %s', 'quire' ), esc_html( get_bloginfo( 'version' ) ) ); ?></a> · <?php printf( esc_html__( '%s theme', 'quire' ), esc_html( wp_get_theme()->get( 'Name' ) ) ); ?></div>
		<?php },
	],
	'needs-eye' => [
		'title'     => __( 'Needs your eye', 'quire' ),
		'desc'      => __( 'Comments waiting for a decision.', 'quire' ),
		'in_picker' => true,
		'source'    => 'Quire',
		'render'    => function () use ( $pending ) { ?>
			<div class="card__head">
				<div class="card__title"><?php esc_html_e( 'Needs your eye', 'quire' ); ?></div>
				<div class="card__desc"><?php esc_html_e( 'Comments waiting for a decision.', 'quire' ); ?></div>
			</div>
			<div class="card__body">
				<?php if ( $pending ) : foreach ( $pending as $c ) :
					$approve = wp_nonce_url( admin_url( "comment.php?action=approvecomment&c={$c->comment_ID}" ), "approve-comment_{$c->comment_ID}" );
					$spam    = wp_nonce_url( admin_url( "comment.php?action=spamcomment&c={$c->comment_ID}" ), "delete-comment_{$c->comment_ID}" );
					$trash   = wp_nonce_url( admin_url( "comment.php?action=trashcomment&c={$c->comment_ID}" ), "delete-comment_{$c->comment_ID}" );
				?>
				<div class="act">
					<div class="act__body">
						<div class="act__line"><a href="<?php echo esc_url( admin_url( 'comment.php?action=editcomment&c=' . $c->comment_ID ) ); ?>"><?php echo esc_html( $c->comment_author ); ?></a>
							<?php esc_html_e( 'on', 'quire' ); ?> <a href="<?php echo esc_url( get_edit_post_link( $c->comment_post_ID ) ); ?>">&ldquo;<?php echo esc_html( get_the_title( $c->comment_post_ID ) ); ?>&rdquo;</a></div>
						<div class="act__quote">&ldquo;<?php echo esc_html( wp_trim_words( $c->comment_content, 14 ) ); ?>&rdquo;</div>
						<div class="act__meta"><?php echo esc_html( human_time_diff( strtotime( $c->comment_date_gmt ) ) . ' ' . __( 'ago', 'quire' ) ); ?></div>
					</div>
					<div class="dt-actions is-open">
						<a href="<?php echo esc_url( $approve ); ?>"><?php esc_html_e( 'Approve', 'quire' ); ?></a>
						<a href="<?php echo esc_url( $spam ); ?>"><?php esc_html_e( 'Spam', 'quire' ); ?></a>
						<a href="<?php echo esc_url( $trash ); ?>"><?php esc_html_e( 'Trash', 'quire' ); ?></a>
					</div>
				</div>
				<?php endforeach; else : ?>
				<div class="act">
					<div class="act__body">
						<div class="act__line qallclear"><span class="qdot qdot--ok" aria-hidden="true"></span><?php esc_html_e( 'Nothing waiting — all clear. New comments land here first.', 'quire' ); ?></div>
					</div>
				</div>
				<?php endif; ?>
			</div>
			<div class="card__foot card__foot--start"><a class="qfootlink" href="<?php echo esc_url( admin_url( 'edit-comments.php' ) ); ?>"><?php esc_html_e( 'All comments', 'quire' ); ?></a></div>
		<?php },
	],
	'publishing' => [
		'title'     => __( 'Publishing', 'quire' ),
		'desc'      => __( 'Your latest and upcoming posts.', 'quire' ),
		'in_picker' => true,
		'source'    => 'Quire',
		'render'    => function () use ( $scheduled, $recent ) { ?>
			<div class="card__head">
				<div class="card__title"><?php esc_html_e( 'Publishing', 'quire' ); ?></div>
			</div>
			<div class="card__body">
				<?php if ( $scheduled || $recent ) : ?>
					<?php foreach ( $scheduled as $p ) : ?>
					<div class="act">
						<div class="act__body">
							<div class="act__line"><a href="<?php echo esc_url( get_edit_post_link( $p->ID ) ); ?>"><?php echo esc_html( get_the_title( $p ) ); ?></a>
								<span class="badge badge--neutral"><?php esc_html_e( 'Scheduled', 'quire' ); ?></span></div>
							<div class="act__meta"><?php echo esc_html( quire_day_phrase( get_post_timestamp( $p ), true ) ); ?></div>
						</div>
						<div class="dt-actions is-open">
							<a href="<?php echo esc_url( get_edit_post_link( $p->ID ) ); ?>"><?php esc_html_e( 'Edit', 'quire' ); ?></a>
							<a href="<?php echo esc_url( get_preview_post_link( $p ) ); ?>"><?php esc_html_e( 'Preview', 'quire' ); ?></a>
						</div>
					</div>
					<?php endforeach; ?>
					<?php foreach ( $recent as $p ) : ?>
					<div class="act">
						<div class="act__body">
							<div class="act__line"><a href="<?php echo esc_url( get_edit_post_link( $p->ID ) ); ?>"><?php echo esc_html( get_the_title( $p ) ); ?></a></div>
							<div class="act__meta"><?php printf( esc_html__( 'Published %s', 'quire' ), esc_html( quire_day_phrase( get_post_timestamp( $p ) ) ) ); ?></div>
						</div>
						<div class="dt-actions is-open">
							<a href="<?php echo esc_url( get_edit_post_link( $p->ID ) ); ?>"><?php esc_html_e( 'Edit', 'quire' ); ?></a>
							<a href="<?php echo esc_url( get_permalink( $p ) ); ?>"><?php esc_html_e( 'View', 'quire' ); ?></a>
						</div>
					</div>
					<?php endforeach; ?>
				<?php else : ?>
				<div class="act">
					<div class="act__body">
						<div class="act__line"><?php esc_html_e( 'Nothing published yet.', 'quire' ); ?></div>
						<div class="act__meta"><?php esc_html_e( 'Your first post will appear here the moment it’s live.', 'quire' ); ?></div>
					</div>
				</div>
				<?php endif; ?>
			</div>
			<div class="card__foot card__foot--start"><a class="qfootlink" href="<?php echo esc_url( admin_url( 'edit.php' ) ); ?>"><?php esc_html_e( 'All posts', 'quire' ); ?></a></div>
		<?php },
	],
	'quick-draft' => [
		'title'     => __( 'Quick draft', 'quire' ),
		'desc'      => __( 'Save an idea before it goes.', 'quire' ),
		'in_picker' => true,
		'source'    => 'Quire',
		'render'    => function () use ( $draft_posts ) { ?>
			<div class="card__head">
				<div class="card__title"><?php esc_html_e( 'Quick draft', 'quire' ); ?></div>
			</div>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<div class="card__body qdraft is-collapsed">
					<input type="hidden" name="action" value="quire_quick_draft">
					<?php wp_nonce_field( 'quire_quick_draft' ); ?>
					<input class="field" type="text" name="quire_draft_title" placeholder="<?php esc_attr_e( 'Catch an idea before it goes…', 'quire' ); ?>" required>
					<div class="qdraft__more">
						<textarea class="field" name="quire_draft_content" placeholder="<?php esc_attr_e( 'What’s on your mind?', 'quire' ); ?>"></textarea>
						<div class="qdraft__actions">
							<button type="button" class="btn btn--tertiary btn--sm qdraft__discard"><?php esc_html_e( 'Discard', 'quire' ); ?></button>
							<button type="submit" class="btn btn--primary btn--sm"><?php esc_html_e( 'Save draft', 'quire' ); ?></button>
						</div>
					</div>
				</div>
			</form>
			<?php if ( $draft_posts ) : ?>
			<div class="card__body qdraft__list">
				<?php foreach ( $draft_posts as $p ) : ?>
				<div class="act">
					<div class="act__body">
						<div class="act__line"><a href="<?php echo esc_url( get_edit_post_link( $p->ID ) ); ?>"><?php echo esc_html( get_the_title( $p ) ?: __( '(no title)', 'quire' ) ); ?></a></div>
						<div class="act__meta"><?php printf( esc_html__( 'Draft — %s', 'quire' ), esc_html( quire_day_phrase( strtotime( $p->post_modified ) ) ) ); ?></div>
					</div>
					<div class="dt-actions is-open">
						<a href="<?php echo esc_url( get_edit_post_link( $p->ID ) ); ?>"><?php esc_html_e( 'Edit', 'quire' ); ?></a>
					</div>
				</div>
				<?php endforeach; ?>
			</div>
			<?php endif; ?>
		<?php },
	],
	'site-health' => [
		'title'     => __( 'Site health', 'quire' ),
		'desc'      => __( 'Whether your site needs attention.', 'quire' ),
		'in_picker' => true,
		'source'    => 'Quire',
		'render'    => function () use ( $health_bad ) { ?>
			<div class="card__head">
				<div class="card__title"><?php esc_html_e( 'Site health', 'quire' ); ?></div>
			</div>
			<div class="card__body qhealth">
				<?php if ( null === $health_bad ) : ?>
					<div class="qhealth__line"><?php esc_html_e( 'Not checked yet. The first check takes about a minute.', 'quire' ); ?></div>
					<a class="qfootlink" href="<?php echo esc_url( admin_url( 'site-health.php' ) ); ?>"><?php esc_html_e( 'Run the first check', 'quire' ); ?></a>
				<?php elseif ( 0 === $health_bad ) : ?>
					<div class="qhealth__line"><span class="qdot qdot--ok" aria-hidden="true"></span><?php esc_html_e( 'All checks passing', 'quire' ); ?></div>
				<?php else : ?>
					<div class="qhealth__line"><span class="qdot qdot--warn" aria-hidden="true"></span><?php printf( esc_html( _n( '%s item to look at', '%s items to look at', $health_bad, 'quire' ) ), esc_html( number_format_i18n( $health_bad ) ) ); ?></div>
					<a class="qfootlink" href="<?php echo esc_url( admin_url( 'site-health.php' ) ); ?>"><?php esc_html_e( 'See what they are', 'quire' ); ?></a>
				<?php endif; ?>
			</div>
		<?php },
	],
	'news' => [
		'title'     => __( 'WordPress news & events', 'quire' ),
		'desc'      => __( 'Meetups and project news.', 'quire' ),
		'in_picker' => true,
		'source'    => 'Quire',
		'render'    => function () use ( $news_items ) { ?>
			<div class="card__head">
				<div class="card__title"><?php esc_html_e( 'News & events', 'quire' ); ?></div>
			</div>
			<div class="card__body qnews">
				<?php if ( null === $news_items || ! $news_items ) : ?>
					<div class="qnews__err"><?php esc_html_e( 'News can’t be loaded right now. It will refresh on its own.', 'quire' ); ?></div>
				<?php else : ?>
					<?php foreach ( $news_items as $i => $item ) : ?>
						<a class="qnews__item<?php echo 0 === $i ? ' qnews__item--lead' : ''; ?>" href="<?php echo esc_url( $item['url'] ); ?>" target="_blank" rel="noreferrer noopener"><?php echo esc_html( $item['title'] ); ?></a>
					<?php endforeach; ?>
					<a class="qfootlink" href="https://wordpress.org/news/" target="_blank" rel="noreferrer noopener"><?php esc_html_e( 'See all', 'quire' ); ?></a>
				<?php endif; ?>
			</div>
		<?php },
	],
];

// ---- per-user layout --------------------------------------------------
$default_layout = [
	'main'   => [ 'welcome', 'overview', 'needs-eye', 'publishing' ],
	'side'   => [ 'quick-draft', 'site-health', 'news' ],
	'hidden' => [],
];
$layout = get_user_meta( get_current_user_id(), 'quire_dashboard_layout', true );
if ( ! is_array( $layout ) ) {
	$layout = $default_layout;
}
$known = array_keys( $quire_widgets );
foreach ( [ 'main', 'side', 'hidden' ] as $col ) {
	$layout[ $col ] = array_values( array_intersect( $layout[ $col ] ?? [], $known ) );
}
// A widget added in an update (or missing from a stale layout) returns to
// its default column — Welcome always to the top.
$placed = array_merge( $layout['main'], $layout['side'], $layout['hidden'] );
foreach ( $known as $id ) {
	if ( in_array( $id, $placed, true ) ) {
		continue;
	}
	if ( 'welcome' === $id ) {
		array_unshift( $layout['main'], 'welcome' );
	} else {
		$col            = in_array( $id, $default_layout['side'], true ) ? 'side' : 'main';
		$layout[ $col ][] = $id;
	}
}

$render_widget = function ( string $id ) use ( $quire_widgets ) {
	$w = $quire_widgets[ $id ];
	// One width for every widget (decided 2026-07-12); customize mode adds
	// only the × — the whole card is the drag surface (H2c), no grip.
	printf( '<section class="card qwidget" data-widget="%s">', esc_attr( $id ) );
	echo '<button type="button" class="qremove" aria-label="' . esc_attr__( 'Remove widget', 'quire' ) . '">&times;</button>';
	( $w['render'] )();
	echo '</section>';
};

require_once ABSPATH . 'wp-admin/admin-header.php';
?>
<div class="quire-screen"
     data-ajax="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>"
     data-nonce="<?php echo esc_attr( wp_create_nonce( 'quire_dashboard_layout' ) ); ?>"
     data-dismiss="<?php echo esc_url( $dismiss_url ); ?>">

  <header class="qtopbar">
    <div>
      <a class="qcrumb" href="<?php echo esc_url( home_url() ); ?>"><?php echo esc_html( get_bloginfo( 'name' ) ); ?></a>
      <h1 class="qtitle"><?php esc_html_e( 'Dashboard', 'quire' ); ?></h1>
    </div>
    <div class="qactions">
      <button type="button" class="btn btn--secondary" id="qcustomize"
        data-label="<?php esc_attr_e( 'Customize', 'quire' ); ?>"
        data-label-done="<?php esc_attr_e( 'Done', 'quire' ); ?>"><?php esc_html_e( 'Customize', 'quire' ); ?></button>
      <a class="btn btn--primary" href="<?php echo esc_url( admin_url( 'post-new.php' ) ); ?>"><?php esc_html_e( 'New post', 'quire' ); ?></a>
    </div>
  </header>

  <?php if ( $draft_saved ) : ?>
  <div class="notice-quire notice notice--success">
    <div>
      <div class="notice__title"><?php esc_html_e( 'Draft saved', 'quire' ); ?></div>
      <div class="notice__body"><?php esc_html_e( 'Your idea is safe under Posts → Drafts.', 'quire' ); ?></div>
    </div>
  </div>
  <?php endif; ?>

  <?php if ( $core_update ) : ?>
  <div class="notice-quire notice notice--info">
    <div>
      <div class="notice__title"><?php printf( esc_html__( 'WordPress %s is ready to install', 'quire' ), esc_html( $core_update ) ); ?></div>
      <div class="notice__body"><a href="<?php echo esc_url( admin_url( 'update-core.php' ) ); ?>"><?php esc_html_e( 'Review and update', 'quire' ); ?></a></div>
    </div>
  </div>
  <?php endif; ?>

  <div class="qdesk">
    <?php foreach ( [ 'main', 'side' ] as $col ) : ?>
    <div class="qcol" data-col="<?php echo esc_attr( $col ); ?>">
      <?php
      foreach ( $layout[ $col ] as $id ) {
        if ( 'welcome' === $id && $welcome_dismissed ) {
          continue;
        }
        $render_widget( $id );
      }
      ?>
      <button type="button" class="qslot"><?php esc_html_e( '+ Add widget', 'quire' ); ?></button>
    </div>
    <?php endforeach; ?>
  </div>

  <div class="qhiddenstore" hidden>
    <?php
    foreach ( $layout['hidden'] as $id ) {
      $render_widget( $id );
    }
    ?>
  </div>

  <div class="qscrim"></div>

  <aside class="qdrawer">
    <div class="qdrawer__head">
      <div class="qdrawer__title"><?php esc_html_e( 'Add a widget', 'quire' ); ?></div>
      <button type="button" class="qdrawer__close" aria-label="<?php esc_attr_e( 'Close', 'quire' ); ?>">&times;</button>
    </div>
    <?php
    // One group per source. Today that's Quire alone; WooCommerce and
    // other-plugin sections arrive with the plugin-widget bridge (R1).
    $sources = [];
    foreach ( $quire_widgets as $id => $w ) {
      if ( $w['in_picker'] ) {
        $sources[ $w['source'] ][ $id ] = $w;
      }
    }
    foreach ( $sources as $source => $widgets ) :
    ?>
    <div class="qdrawer__group">
      <div class="qdrawer__grouplabel"><?php echo esc_html( $source ); ?></div>
      <?php foreach ( $widgets as $id => $w ) : ?>
      <div class="qdrawer__row" data-widget="<?php echo esc_attr( $id ); ?>">
        <div class="qdrawer__meta">
          <div class="qdrawer__name"><?php echo esc_html( $w['title'] ); ?></div>
          <div class="qdrawer__desc"><?php echo esc_html( $w['desc'] ); ?></div>
        </div>
        <button type="button" class="btn btn--secondary btn--sm qdrawer__add"><?php esc_html_e( '+ Add', 'quire' ); ?></button>
        <button type="button" class="qdrawer__removelink"><?php esc_html_e( 'Remove', 'quire' ); ?></button>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endforeach; ?>
  </aside>

</div>
<?php
require_once ABSPATH . 'wp-admin/admin-footer.php';
exit;
