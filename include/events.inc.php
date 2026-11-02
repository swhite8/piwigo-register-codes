<?php
defined('PHPWG_ROOT_PATH') or die('Hacking attempt!');

function register_codes_admin_menu($menu) {
 array_push(
   $menu,
   array(
     'NAME'  => l10n('Register Codes Plugin'),
     'URL'   => get_admin_plugin_menu_link(dirname(__DIR__)).'/admin.php'
   )
 );
 return $menu;
}

function register_codes_init() {

  global $template;
  load_language('plugin.lang', REGISTER_CODES_PATH);
  load_language('lang', PHPWG_ROOT_PATH.PWG_LOCAL_DIR, array('no_fallback'=>true, 'local'=>true) );

}

function register_codes_register_init() {
    include(REGISTER_CODES_PATH . '/register_codes_register.php');
}

/**
 * Logs the user's name after registration.
 *
 * @param array $user_data Array containing user information.
 */
function register_codes_log_user_registration($user_data)
{
    global $prefixeTable;
    $reservation = $GLOBALS['register_codes_reservation'] ?? null;
    if (!$reservation || stripslashes($user_data['username']) !== $reservation['username']) { return; }
    // Usage was already reserved atomically before Piwigo created the account.
    unset($GLOBALS['register_codes_reservation']);
    $username = pwg_db_real_escape_string($reservation['username']);
    $code = pwg_db_real_escape_string($reservation['code']);
    pwg_query('INSERT INTO '.$prefixeTable."register_codes_users (code,user_name) VALUES ('$code','$username')");
}

/** Release a rejected/aborted registration's reservation, never a created user's. */
function register_codes_release_reservation()
{
    global $prefixeTable;
    $reservation = $GLOBALS['register_codes_reservation'] ?? null;
    if (!$reservation) { return; }
    // Core may have inserted the account before a mail or plugin exception.
    // In that case keep the usage charged, even if the success hook never ran.
    if (get_userid($reservation['username']) !== false) {
      unset($GLOBALS['register_codes_reservation']);
      return;
    }
    pwg_query('UPDATE '.$prefixeTable.'register_codes SET used = used - 1'
      .' WHERE id='.(int)$reservation['id'].' AND used > 0');
    unset($GLOBALS['register_codes_reservation']);
}
