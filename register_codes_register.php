<?php
if (!defined('PHPWG_ROOT_PATH'))
  die('Hacking attempt!');

global $template, $prefixeTable;

// Prefer the active theme (then its inherited template directories).
$register_codes_template = realpath(REGISTER_CODES_PATH.'register_codes_register.tpl');
foreach ($template->get_template_dir() as $directory) {
  $candidate = rtrim($directory, '/').'/plugins/piwigo-register-codes/register_codes_register.tpl';
  if (is_file($candidate)) {
    $register_codes_template = $candidate;
    break;
  }
}
// Piwigo's set_filename also honors a set_extent override for this handle.
$template->set_filename('register_codes_register', $register_codes_template);
$template->assign('REGISTERCODES', array('parsed_content' => $template->parse('register_codes_register', true)), true);

add_event_handler('loc_end_page_header', 'register_codes_add_registration_field');
// Reserve only after core validation and other normal plugin validators.
add_event_handler('register_user_check', 'register_codes_check_code', PHP_INT_MAX);

function register_codes_add_registration_field()
{
  global $template;
  $template->set_prefilter('register', 'register_codes_prefilter_registration');
}

function register_codes_prefilter_registration($content)
{

  $field = '{$REGISTERCODES.parsed_content}';
  // Themes may place the field themselves; repeated filtering must be harmless.
  if (strpos($content, $field) !== false) { return $content; }
  if (preg_match('~</fieldset\s*>~i', $content, $match, PREG_OFFSET_CAPTURE)) {
    $position = $match[0][1] + strlen($match[0][0]);
  } elseif (preg_match('~<(?:input|button)\b[^>]*\btype\s*=\s*["\']submit["\'][^>]*>~i', $content, $match, PREG_OFFSET_CAPTURE)) {
    $position = $match[0][1];
  } else {
    // Do not append outside an unknown form or interfere with page rendering.
    return $content;
  }
  return substr($content, 0, $position).$field.substr($content, $position);

}

function register_codes_check_code($errors, $user_data = array())
{
  global $prefixeTable;
  unset($_SESSION['user_register_code']); // Discard legacy session state.
  register_codes_release_reservation();
  if (!empty($errors)) { return $errors; }

  if (!isset($_POST['register_code']) || !is_string($_POST['register_code'])
      || strlen(stripslashes($_POST['register_code'])) > 255
      || empty($user_data['username'])) {
    $errors[] = l10n('Invalid Registration Code');
    return $errors;
  }

  $raw_code = stripslashes($_POST['register_code']);
  unset($_POST['register_code']);
  $code = pwg_db_real_escape_string($raw_code);
  // Select one deterministic row even in old databases containing duplicate codes.
  $result = pwg_query('SELECT id FROM '.$prefixeTable."register_codes WHERE code='$code' ORDER BY id LIMIT 1");
  if (!pwg_db_num_rows($result)) {
    $errors[] = l10n('Invalid Registration Code');
    return $errors;
  }
  list($id) = pwg_db_fetch_row($result);
  $id = (int) $id;
  // Preserve the plugin's existing gallery-timezone expiry semantics.
  $now = pwg_db_real_escape_string(date('Y-m-d H:i:s'));

  // The condition and increment execute as ONE database operation. Works with
  // existing MyISAM tables as well as InnoDB; no schema migration is required.
  pwg_query('UPDATE '.$prefixeTable.'register_codes SET used = used + 1'
    .' WHERE id='.$id.' AND (uses = 0 OR used < uses)'
    ." AND (expiry IS NULL OR expiry >= '$now')");
  if (pwg_db_changes() !== 1) {
    $errors[] = l10n('Invalid Registration Code');
    return $errors;
  }
  $GLOBALS['register_codes_reservation'] = array(
    'id' => $id, 'code' => $raw_code,
    'username' => stripslashes($user_data['username']),
  );
  register_shutdown_function('register_codes_release_reservation');
  return $errors;
}
