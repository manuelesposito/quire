<?php
/**
 * The Quire Dashboard — the first real screen (Lane 3).
 *
 * Rendered in place of wp-admin/index.php (see quire.php). Everything on
 * this screen is REAL: live counts, the actual moderation queue with
 * working actions, real posts, a Quick Draft that saves. The WP admin
 * chrome (menu, admin bar) stays around it for now — the shell is a
 * later stage of the climb.
 */

defined( 'ABSPATH' ) || exit;

// ---- real data ------------------------------------------------------
$counts        = wp_count_posts();
$published     = (int) $counts->publish;
$drafts        = (int) $counts->draft;
$page_counts   = wp_count_posts( 'page' );
$pages         = (int) $page_counts->publish;
$comment_count = wp_count_comments();
$in_queue      = (int) $comment_count->moderated;
$media_counts  = (array) wp_count_attachments();
$media_total   = array_sum( array_map( 'intval', $media_counts ) );

$pending = get_comments( [ 'status' => 'hold', 'number' => 3 ] );
$recent  = wp_get_recent_posts( [ 'numberposts' => 5, 'post_status' => 'publish' ], OBJECT );

$core_update = null;
$update_data = get_site_transient( 'update_core' );
if ( $update_data && ! empty( $update_data->updates ) && 'upgrade' === $update_data->updates[0]->response ) {
	$core_update = $update_data->updates[0]->current;
}

$health       = get_transient( 'health-check-site-status-result' );
$health_data  = $health ? json_decode( $health, true ) : null;
$health_bad   = $health_data ? (int) $health_data['critical'] + (int) $health_data['recommended'] : null;

$draft_saved = isset( $_GET['quire-draft'] ) && 'saved' === $_GET['quire-draft'];

require_once ABSPATH . 'wp-admin/admin-header.php';
?>
<div class="quire-screen">

  <header class="qtopbar">
    <div>
      <div class="qcrumb"><?php echo esc_html( get_bloginfo( 'name' ) ); ?></div>
      <h1 class="qtitle"><?php esc_html_e( 'Dashboard', 'quire' ); ?></h1>
    </div>
    <div class="qactions">
      <a class="btn btn--secondary" href="<?php echo esc_url( home_url() ); ?>"><?php esc_html_e( 'View site', 'quire' ); ?></a>
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

  <div class="qstats">
    <a class="stat" href="<?php echo esc_url( admin_url( 'edit.php' ) ); ?>">
      <div class="stat__label"><?php esc_html_e( 'Published posts', 'quire' ); ?></div>
      <div class="stat__value"><?php echo esc_html( number_format_i18n( $published ) ); ?></div>
      <div class="stat__delta"><?php echo esc_html( sprintf( _n( '%s draft waiting', '%s drafts waiting', $drafts, 'quire' ), number_format_i18n( $drafts ) ) ); ?></div>
    </a>
    <a class="stat" href="<?php echo esc_url( admin_url( 'edit.php?post_type=page' ) ); ?>">
      <div class="stat__label"><?php esc_html_e( 'Pages', 'quire' ); ?></div>
      <div class="stat__value"><?php echo esc_html( number_format_i18n( $pages ) ); ?></div>
      <div class="stat__delta">&nbsp;</div>
    </a>
    <a class="stat" href="<?php echo esc_url( admin_url( 'edit-comments.php?comment_status=moderated' ) ); ?>">
      <div class="stat__label"><?php esc_html_e( 'Comments in queue', 'quire' ); ?></div>
      <div class="stat__value"><?php echo esc_html( number_format_i18n( $in_queue ) ); ?></div>
      <div class="stat__delta"><?php echo $in_queue ? esc_html__( 'Waiting for a decision', 'quire' ) : esc_html__( 'All clear', 'quire' ); ?></div>
    </a>
    <a class="stat" href="<?php echo esc_url( admin_url( 'upload.php' ) ); ?>">
      <div class="stat__label"><?php esc_html_e( 'Media items', 'quire' ); ?></div>
      <div class="stat__value"><?php echo esc_html( number_format_i18n( $media_total ) ); ?></div>
      <div class="stat__delta">&nbsp;</div>
    </a>
  </div>

  <div class="qdesk">
    <div class="qmain">

      <section class="card">
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
                <?php esc_html_e( 'on', 'quire' ); ?> <a href="<?php echo esc_url( get_edit_post_link( $c->comment_post_ID ) ); ?>"><?php echo esc_html( get_the_title( $c->comment_post_ID ) ); ?></a></div>
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
          <div class="act"><div class="act__body">
            <div class="act__line"><?php esc_html_e( 'Nothing waiting — all clear.', 'quire' ); ?></div>
            <div class="act__meta"><?php esc_html_e( 'New comments land here first.', 'quire' ); ?></div>
          </div></div>
          <?php endif; ?>
        </div>
        <div class="card__foot"><a class="btn btn--tertiary btn--sm" href="<?php echo esc_url( admin_url( 'edit-comments.php' ) ); ?>"><?php esc_html_e( 'All comments', 'quire' ); ?></a></div>
      </section>

      <section class="card">
        <div class="card__head">
          <div class="card__title"><?php esc_html_e( 'Recently published', 'quire' ); ?></div>
        </div>
        <div class="card__body">
          <?php if ( $recent ) : foreach ( $recent as $p ) : ?>
          <div class="act">
            <div class="act__body">
              <div class="act__line"><a href="<?php echo esc_url( get_edit_post_link( $p->ID ) ); ?>"><?php echo esc_html( get_the_title( $p ) ); ?></a></div>
              <div class="act__meta"><?php echo esc_html( wp_date( get_option( 'date_format' ), get_post_timestamp( $p ) ) ); ?></div>
            </div>
            <div class="dt-actions is-open">
              <a href="<?php echo esc_url( get_edit_post_link( $p->ID ) ); ?>"><?php esc_html_e( 'Edit', 'quire' ); ?></a>
              <a href="<?php echo esc_url( get_permalink( $p ) ); ?>"><?php esc_html_e( 'View', 'quire' ); ?></a>
            </div>
          </div>
          <?php endforeach; else : ?>
          <div class="act"><div class="act__body">
            <div class="act__line"><?php esc_html_e( 'Nothing published yet.', 'quire' ); ?></div>
            <div class="act__meta"><?php esc_html_e( 'Your first post will appear here.', 'quire' ); ?></div>
          </div></div>
          <?php endif; ?>
        </div>
        <div class="card__foot"><a class="btn btn--tertiary btn--sm" href="<?php echo esc_url( admin_url( 'edit.php' ) ); ?>"><?php esc_html_e( 'All posts', 'quire' ); ?></a></div>
      </section>

    </div>
    <div class="qside">

      <section class="card">
        <div class="card__head">
          <div class="card__title"><?php esc_html_e( 'Quick draft', 'quire' ); ?></div>
          <div class="card__desc"><?php esc_html_e( 'Catch an idea before it goes — it lands in Posts as a draft.', 'quire' ); ?></div>
        </div>
        <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
          <div class="card__body qdraft">
            <input type="hidden" name="action" value="quire_quick_draft">
            <?php wp_nonce_field( 'quire_quick_draft' ); ?>
            <input class="field" type="text" name="quire_draft_title" placeholder="<?php esc_attr_e( 'Title', 'quire' ); ?>" required>
            <textarea class="field" name="quire_draft_content" placeholder="<?php esc_attr_e( 'What’s on your mind?', 'quire' ); ?>"></textarea>
          </div>
          <div class="card__foot"><button type="submit" class="btn btn--primary btn--sm"><?php esc_html_e( 'Save draft', 'quire' ); ?></button></div>
        </form>
      </section>

      <section class="card">
        <div class="card__head">
          <div class="card__title"><?php esc_html_e( 'Site health', 'quire' ); ?></div>
        </div>
        <div class="card__body qhealth">
          <div class="act">
            <div class="act__body">
              <?php if ( null === $health_bad ) : ?>
                <div class="act__line"><?php esc_html_e( 'Not checked yet', 'quire' ); ?></div>
                <div class="act__meta"><a href="<?php echo esc_url( admin_url( 'site-health.php' ) ); ?>"><?php esc_html_e( 'Run the first check', 'quire' ); ?></a></div>
              <?php elseif ( 0 === $health_bad ) : ?>
                <div class="act__line"><?php esc_html_e( 'All checks passing', 'quire' ); ?></div>
              <?php else : ?>
                <div class="act__line"><a href="<?php echo esc_url( admin_url( 'site-health.php' ) ); ?>"><?php echo esc_html( sprintf( _n( '%s item to look at', '%s items to look at', $health_bad, 'quire' ), number_format_i18n( $health_bad ) ) ); ?></a></div>
              <?php endif; ?>
            </div>
            <?php if ( 0 === $health_bad ) : ?>
              <span class="badge badge--success"><span class="dot"></span><?php esc_html_e( 'Good', 'quire' ); ?></span>
            <?php elseif ( null !== $health_bad ) : ?>
              <span class="badge badge--warning"><span class="dot"></span><?php esc_html_e( 'Should improve', 'quire' ); ?></span>
            <?php endif; ?>
          </div>
        </div>
      </section>

    </div>
  </div>

</div>
<?php
require_once ABSPATH . 'wp-admin/admin-footer.php';
exit;
