<?php
/**
 * Quire Settings → General — the first real form screen (Lane 3).
 *
 * The form posts to core's own options.php via settings_fields('general'),
 * so saving IS core saving: same nonce, same sanitizing, same redirect back.
 *
 * CRITICAL: options.php updates EVERY option in the 'general' allow-list on
 * submit and blanks any it doesn't receive — so every core field is present
 * here, visible or hidden (site_icon is preserved hidden until we own a
 * media picker). Fields other plugins registered on this page render in
 * their own card at the end, so nothing becomes unreachable.
 */

defined( 'ABSPATH' ) || exit;

if ( ! current_user_can( 'manage_options' ) ) {
	wp_die( __( 'Sorry, you are not allowed to manage options for this site.' ) );
}

// Core handles this cancel-link inside options-general.php, which we replace —
// honour it here with the same nonce and behaviour.
if ( isset( $_GET['dismiss'] ) && 'new_admin_email' === $_GET['dismiss'] ) {
	check_admin_referer( 'dismiss-' . get_current_blog_id() . '-new_admin_email' );
	delete_option( 'new_admin_email' );
	wp_safe_redirect( admin_url( 'options-general.php' ) );
	exit;
}

// ---- real data ------------------------------------------------------
global $wp_locale;

$saved = isset( $_GET['settings-updated'] );

$siteurl_locked = defined( 'WP_SITEURL' );
$home_locked    = defined( 'WP_HOME' );

$new_admin_email = get_option( 'new_admin_email' );
if ( $new_admin_email && get_option( 'admin_email' ) === $new_admin_email ) {
	$new_admin_email = false;
}

$site_icon_id  = (int) get_option( 'site_icon' );
$site_icon_url = $site_icon_id ? wp_get_attachment_image_url( $site_icon_id, [ 64, 64 ] ) : '';

$languages = get_available_languages();

$tzstring = get_option( 'timezone_string' );
$gmt      = get_option( 'gmt_offset' );
if ( empty( $tzstring ) ) { // map raw offset back to the UTC±X choice, like core
	$tzstring = ( 0 == $gmt ) ? 'UTC+0' : ( $gmt < 0 ? "UTC{$gmt}" : "UTC+{$gmt}" );
}

$date_formats   = array_unique( apply_filters( 'date_formats', [ __( 'F j, Y' ), 'Y-m-d', 'm/d/Y', 'd/m/Y' ] ) );
$time_formats   = array_unique( apply_filters( 'time_formats', [ __( 'g:i a' ), 'g:i A', 'H:i' ] ) );
$date_format    = get_option( 'date_format' );
$time_format    = get_option( 'time_format' );
$date_is_custom = ! in_array( $date_format, $date_formats, true );
$time_is_custom = ! in_array( $time_format, $time_formats, true );

// Our own toggle renders natively below — keep it out of the plugin-fields card.
global $wp_settings_fields, $wp_settings_sections;
unset( $wp_settings_fields['general']['default'][ QUIRE_OPTION ] );
$foreign_fields   = ! empty( $wp_settings_fields['general']['default'] );
$foreign_sections = ! empty( $wp_settings_sections['general'] );

// The band (H1) carries crumb + title; this screen contributes its save
// cluster to the band's actions slot. SETTINGS-SPEC.md D2/D3 still hold:
// no action chrome at rest — the bar appears on first edit, and it is
// FIXED (H1b), so it never scrolls out of sight.
add_filter( 'quire_shell_band_actions', function ( $html ) {
	ob_start();
	?>
	<div class="qsavebar" id="qsavebar" hidden>
		<span class="qsavebar__msg" id="qsavebar-msg"><?php esc_html_e( 'Unsaved changes', 'quire' ); ?></span>
		<button type="button" class="qsavebar__discard" id="qsavebar-discard"><?php esc_html_e( 'Discard', 'quire' ); ?></button>
		<button type="submit" class="qsavebar__save" form="quire-settings-form"><?php esc_html_e( 'Save', 'quire' ); ?></button>
	</div>
	<span class="screen-reader-text" role="status" aria-live="polite" id="qsavebar-live"></span>
	<?php
	return $html . ob_get_clean();
} );

require_once ABSPATH . 'wp-admin/admin-header.php';
?>
<div class="quire-screen">

  <div class="qsettings">
    <?php // The sections list lives in the shell's second-level column beside the content (S9) — the form centers alone (W3). ?>
    <div class="qmain">

      <?php if ( $saved ) : ?>
      <div class="notice-quire notice notice--success">
        <div>
          <div class="notice__title"><?php esc_html_e( 'Settings saved', 'quire' ); ?></div>
          <div class="notice__body"><?php esc_html_e( 'Your changes are live across the site.', 'quire' ); ?></div>
        </div>
      </div>
      <?php endif; ?>

      <form id="quire-settings-form" method="post" action="<?php echo esc_url( admin_url( 'options.php' ) ); ?>" novalidate="novalidate">
        <?php settings_fields( 'general' ); ?>
        <?php // preserved, not yet editable here — omitting it would erase the icon (see header note) ?>
        <input type="hidden" name="site_icon" value="<?php echo esc_attr( $site_icon_id ?: '' ); ?>">
        <div class="qcols">
        <div class="qcol">

      <section class="card">
        <div class="card__head">
          <div class="card__title"><?php esc_html_e( 'Site identity', 'quire' ); ?></div>
          <div class="card__desc"><?php esc_html_e( 'How your site introduces itself — in browser tabs, search results, and feeds.', 'quire' ); ?></div>
        </div>
        <div class="card__body">
          <div class="srow">
            <label class="srow__label" for="blogname"><?php esc_html_e( 'Site title', 'quire' ); ?></label>
            <div class="srow__control"><input class="field" type="text" id="blogname" name="blogname" value="<?php form_option( 'blogname' ); ?>"></div>
          </div>
          <div class="srow">
            <label class="srow__label" for="blogdescription"><?php esc_html_e( 'Tagline', 'quire' ); ?></label>
            <div class="srow__control">
              <input class="field" type="text" id="blogdescription" name="blogdescription" value="<?php form_option( 'blogdescription' ); ?>">
              <div class="srow__help"><?php esc_html_e( 'In a few words, explain what this site is about. Shown where themes and search engines choose to display it.', 'quire' ); ?></div>
            </div>
          </div>
          <div class="srow">
            <div class="srow__label"><?php esc_html_e( 'Site icon', 'quire' ); ?></div>
            <div class="srow__control">
              <div class="icon-row">
                <?php if ( $site_icon_url ) : ?>
                  <img class="site-icon" src="<?php echo esc_url( $site_icon_url ); ?>" alt="">
                <?php else : ?>
                  <div class="site-icon site-icon--letter"><?php echo esc_html( mb_strtoupper( mb_substr( get_bloginfo( 'name' ), 0, 1 ) ) ); ?></div>
                <?php endif; ?>
                <a class="btn btn--secondary btn--sm" href="<?php echo esc_url( admin_url( 'customize.php?autofocus[section]=title_tagline&url=' . rawurlencode( home_url() ) ) ); ?>"><?php echo $site_icon_url ? esc_html__( 'Replace', 'quire' ) : esc_html__( 'Choose an icon', 'quire' ); ?></a>
              </div>
              <div class="srow__help"><?php esc_html_e( 'Shown in browser tabs and bookmarks. Square, at least 512 × 512 pixels.', 'quire' ); ?></div>
            </div>
          </div>
        </div>
      </section>

      <section class="card">
        <div class="card__head">
          <div class="card__title"><?php esc_html_e( 'Addresses', 'quire' ); ?></div>
          <div class="card__desc"><?php esc_html_e( 'Where WordPress lives and where visitors find the site.', 'quire' ); ?></div>
        </div>
        <div class="card__body">
          <div class="srow">
            <label class="srow__label" for="siteurl"><?php esc_html_e( 'WordPress address', 'quire' ); ?></label>
            <div class="srow__control">
              <input class="field field--url" type="url" id="siteurl" name="siteurl" value="<?php form_option( 'siteurl' ); ?>" <?php disabled( $siteurl_locked ); ?>>
              <?php if ( $siteurl_locked ) : ?><div class="srow__help"><?php esc_html_e( 'Fixed in wp-config.php (WP_SITEURL) — edit it there.', 'quire' ); ?></div><?php endif; ?>
            </div>
          </div>
          <div class="srow">
            <label class="srow__label" for="home"><?php esc_html_e( 'Site address', 'quire' ); ?></label>
            <div class="srow__control">
              <input class="field field--url" type="url" id="home" name="home" value="<?php form_option( 'home' ); ?>" <?php disabled( $home_locked ); ?>>
              <div class="srow__help"><?php echo $home_locked ? esc_html__( 'Fixed in wp-config.php (WP_HOME) — edit it there.', 'quire' ) : esc_html__( 'Enter the same address unless WordPress is installed in its own directory.', 'quire' ); ?></div>
            </div>
          </div>
          <div class="srow">
            <label class="srow__label" for="new_admin_email"><?php esc_html_e( 'Administration email', 'quire' ); ?></label>
            <div class="srow__control">
              <input class="field" type="email" id="new_admin_email" name="new_admin_email" value="<?php echo esc_attr( get_option( 'admin_email' ) ); ?>">
              <div class="srow__help"><?php esc_html_e( 'Used for admin purposes. A change is confirmed by email before it takes effect.', 'quire' ); ?></div>
              <?php if ( $new_admin_email ) : ?>
              <div class="srow__help srow__pending">
                <?php printf( esc_html__( 'A change to %s is waiting for confirmation.', 'quire' ), '<strong>' . esc_html( $new_admin_email ) . '</strong>' ); ?>
                <a href="<?php echo esc_url( wp_nonce_url( admin_url( 'options-general.php?dismiss=new_admin_email' ), 'dismiss-' . get_current_blog_id() . '-new_admin_email' ) ); ?>"><?php esc_html_e( 'Cancel it', 'quire' ); ?></a>
              </div>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </section>

        </div><?php // /qcol — what the site IS (identity, addresses) ?>
        <div class="qcol"><?php // how it BEHAVES (membership, locale, quire, plugins) ?>

      <section class="card">
        <div class="card__head">
          <div class="card__title"><?php esc_html_e( 'Membership', 'quire' ); ?></div>
          <div class="card__desc"><?php esc_html_e( 'Who can join, and what they can do when they arrive.', 'quire' ); ?></div>
        </div>
        <div class="card__body">
          <div class="srow srow--switch">
            <div>
              <div class="srow__rlabel"><label for="users_can_register"><?php esc_html_e( 'Anyone can register', 'quire' ); ?></label></div>
              <div class="srow__help"><?php esc_html_e( 'Visitors may create an account on the login screen.', 'quire' ); ?></div>
            </div>
            <label class="toggle"><input type="checkbox" id="users_can_register" name="users_can_register" value="1" <?php checked( '1', get_option( 'users_can_register' ) ); ?>><span class="track"></span></label>
          </div>
          <div class="srow">
            <label class="srow__label" for="default_role"><?php esc_html_e( 'Default role', 'quire' ); ?></label>
            <div class="srow__control">
              <span class="select-wrap"><select class="field" id="default_role" name="default_role"><?php wp_dropdown_roles( get_option( 'default_role' ) ); ?></select></span>
              <div class="srow__help"><?php esc_html_e( 'The role every new account starts with.', 'quire' ); ?></div>
            </div>
          </div>
        </div>
      </section>

      <section class="card">
        <div class="card__head">
          <div class="card__title"><?php esc_html_e( 'Language & time', 'quire' ); ?></div>
          <div class="card__desc"><?php esc_html_e( 'The language of the admin, and how dates and times read across the site.', 'quire' ); ?></div>
        </div>
        <div class="card__body">
          <div class="srow">
            <label class="srow__label" for="WPLANG"><?php esc_html_e( 'Site language', 'quire' ); ?></label>
            <div class="srow__control">
              <?php if ( $languages ) : ?>
              <span class="select-wrap"><?php
                wp_dropdown_languages( [
                  'name'                        => 'WPLANG',
                  'id'                          => 'WPLANG',
                  'selected'                    => get_option( 'WPLANG' ),
                  'languages'                   => $languages,
                  'show_available_translations' => false,
                ] );
              ?></span>
              <?php else : ?>
              <div class="srow__static"><?php esc_html_e( 'English (United States)', 'quire' ); ?></div>
              <div class="srow__help"><?php esc_html_e( 'No other languages are installed yet.', 'quire' ); ?></div>
              <?php endif; ?>
            </div>
          </div>
          <div class="srow">
            <label class="srow__label" for="timezone_string"><?php esc_html_e( 'Timezone', 'quire' ); ?></label>
            <div class="srow__control">
              <span class="select-wrap"><select class="field" id="timezone_string" name="timezone_string"><?php echo wp_timezone_choice( $tzstring, get_user_locale() ); ?></select></span>
              <div class="srow__help"><?php
                printf(
                  /* translators: 1: UTC time, 2: local time */
                  esc_html__( 'Universal time is %1$s · local time is %2$s', 'quire' ),
                  '<span class="fmt">' . esc_html( gmdate( 'Y-m-d H:i' ) ) . ' UTC</span>',
                  '<span class="fmt">' . esc_html( wp_date( 'H:i' ) ) . '</span>'
                );
              ?></div>
            </div>
          </div>
          <div class="srow">
            <div class="srow__label"><?php esc_html_e( 'Date format', 'quire' ); ?></div>
            <div class="srow__control">
              <div class="opts">
                <?php foreach ( $date_formats as $format ) : ?>
                <label class="opt"><input type="radio" name="date_format" value="<?php echo esc_attr( $format ); ?>" <?php checked( ! $date_is_custom && $format === $date_format ); ?>><span class="box ro"><span class="dot"></span></span> <?php echo esc_html( wp_date( $format ) ); ?> <span class="fmt"><?php echo esc_html( $format ); ?></span></label>
                <?php endforeach; ?>
                <label class="opt"><input type="radio" name="date_format" value="\c\u\s\t\o\m" <?php checked( $date_is_custom ); ?>><span class="box ro"><span class="dot"></span></span> <?php esc_html_e( 'Custom', 'quire' ); ?>
                  <input class="field field--fmt" type="text" name="date_format_custom" value="<?php echo esc_attr( $date_format ); ?>" aria-label="<?php esc_attr_e( 'Custom date format', 'quire' ); ?>">
                </label>
              </div>
            </div>
          </div>
          <div class="srow">
            <div class="srow__label"><?php esc_html_e( 'Time format', 'quire' ); ?></div>
            <div class="srow__control">
              <div class="opts">
                <?php foreach ( $time_formats as $format ) : ?>
                <label class="opt"><input type="radio" name="time_format" value="<?php echo esc_attr( $format ); ?>" <?php checked( ! $time_is_custom && $format === $time_format ); ?>><span class="box ro"><span class="dot"></span></span> <?php echo esc_html( wp_date( $format ) ); ?> <span class="fmt"><?php echo esc_html( $format ); ?></span></label>
                <?php endforeach; ?>
                <label class="opt"><input type="radio" name="time_format" value="\c\u\s\t\o\m" <?php checked( $time_is_custom ); ?>><span class="box ro"><span class="dot"></span></span> <?php esc_html_e( 'Custom', 'quire' ); ?>
                  <input class="field field--fmt" type="text" name="time_format_custom" value="<?php echo esc_attr( $time_format ); ?>" aria-label="<?php esc_attr_e( 'Custom time format', 'quire' ); ?>">
                </label>
              </div>
            </div>
          </div>
          <div class="srow">
            <label class="srow__label" for="start_of_week"><?php esc_html_e( 'Week starts on', 'quire' ); ?></label>
            <div class="srow__control">
              <span class="select-wrap"><select class="field" id="start_of_week" name="start_of_week">
                <?php for ( $day = 0; $day <= 6; $day++ ) : ?>
                <option value="<?php echo esc_attr( $day ); ?>" <?php selected( (int) get_option( 'start_of_week' ), $day ); ?>><?php echo esc_html( $wp_locale->get_weekday( $day ) ); ?></option>
                <?php endfor; ?>
              </select></span>
            </div>
          </div>
        </div>
      </section>

      <section class="card">
        <div class="card__head">
          <div class="card__title"><?php esc_html_e( 'Quire', 'quire' ); ?></div>
          <div class="card__desc"><?php esc_html_e( 'This admin design. It steps aside the moment you switch it off.', 'quire' ); ?></div>
        </div>
        <div class="card__body">
          <div class="srow srow--switch">
            <div>
              <div class="srow__rlabel"><label for="quire_enabled"><?php esc_html_e( 'Quire admin style', 'quire' ); ?></label></div>
              <div class="srow__help"><?php esc_html_e( 'Turn off to return to the default WordPress admin.', 'quire' ); ?></div>
            </div>
            <label class="toggle"><input type="checkbox" id="quire_enabled" name="<?php echo esc_attr( QUIRE_OPTION ); ?>" value="1" <?php checked( quire_is_enabled() ); ?>><span class="track"></span></label>
          </div>
        </div>
      </section>

      <?php if ( $foreign_fields || $foreign_sections ) : ?>
      <section class="card">
        <div class="card__head">
          <div class="card__title"><?php esc_html_e( 'From your plugins', 'quire' ); ?></div>
          <div class="card__desc"><?php esc_html_e( 'Settings other plugins add to this screen.', 'quire' ); ?></div>
        </div>
        <div class="card__body qplug">
          <?php if ( $foreign_fields ) : ?>
          <table class="form-table" role="presentation"><?php do_settings_fields( 'general', 'default' ); ?></table>
          <?php endif; ?>
          <?php do_settings_sections( 'general' ); ?>
        </div>
      </section>
      <?php endif; ?>

        </div><?php // /qcol ?>
        </div><?php // /qcols ?>
      </form>

    </div><?php // /qmain ?>
  </div><?php // /qsettings ?>

</div>
<script>
// typing a custom format selects its Custom radio — no other behaviour
document.querySelectorAll('.quire-screen .field--fmt').forEach(function (input) {
  input.addEventListener('input', function () {
    input.closest('label.opt').querySelector('input[type=radio]').checked = true;
  });
  input.addEventListener('focus', function () {
    input.closest('label.opt').querySelector('input[type=radio]').checked = true;
  });
});

// Contextual save bar — SETTINGS-SPEC.md D2 (a11y) + D3 (failure) decisions:
// appears on first edit, announces politely, never steals focus. Cmd/Ctrl+S
// saves when dirty. Discard restores the rendered values. Navigating away
// with edits warns. (Transport failures currently surface through core's
// options.php response — the qsavebar--error state is wired for the fetch
// upgrade; see spec.)
(function () {
  var form    = document.getElementById('quire-settings-form');
  var bar     = document.getElementById('qsavebar');
  var live    = document.getElementById('qsavebar-live');
  var discard = document.getElementById('qsavebar-discard');
  if (!form || !bar) { return; }
  var dirty = false;

  function markDirty() {
    if (dirty) { return; }
    dirty = true;
    bar.hidden = false;
    live.textContent = <?php echo wp_json_encode( __( 'You have unsaved changes. Save and Discard are available in the page header.', 'quire' ) ); ?>;
  }
  function markClean(announcement) {
    dirty = false;
    bar.hidden = true;
    live.textContent = announcement || '';
  }

  form.addEventListener('input',  markDirty);
  form.addEventListener('change', markDirty);

  discard.addEventListener('click', function () {
    form.reset();
    markClean(<?php echo wp_json_encode( __( 'Changes discarded.', 'quire' ) ); ?>);
  });

  form.addEventListener('submit', function () { dirty = false; });

  document.addEventListener('keydown', function (e) {
    if ((e.metaKey || e.ctrlKey) && 's' === e.key.toLowerCase()) {
      e.preventDefault();
      if (dirty) {
        form.requestSubmit ? form.requestSubmit() : form.submit();
      }
    }
  });

  window.addEventListener('beforeunload', function (e) {
    if (dirty) { e.preventDefault(); e.returnValue = ''; }
  });
})();
</script>
<?php
require_once ABSPATH . 'wp-admin/admin-footer.php';
exit;
