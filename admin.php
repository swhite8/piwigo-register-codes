<?php
// Chech whether we are indeed included by Piwigo.
if (!defined('PHPWG_ROOT_PATH')) die('Hacking attempt!');

//Check If Administrator
check_status(ACCESS_ADMINISTRATOR);

// Every administrative mutation requires a POST-bound session token.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (!isset($_POST['pwg_token']) || !is_string($_POST['pwg_token'])
      || !hash_equals(get_pwg_token(), $_POST['pwg_token'])) {
    access_denied();
    exit;
  }
  check_pwg_token();

  foreach (array('register_code', 'register_comment', 'register_expiry',
                 'batch_comment', 'batch_expiry', 'code') as $field) {
    if (isset($_POST[$field]) && !is_string($_POST[$field])) {
      bad_request(l10n('Invalid registration code field'));
      exit;
    }
  }
  foreach (array('uses', 'batch_uses', 'batch_count', 'id') as $field) {
    if (isset($_POST[$field])) {
      $minimum = in_array($field, array('batch_count', 'id')) ? 1 : 0;
      $maximum = $field === 'batch_count' ? 100 : 2147483647;
      if (!is_string($_POST[$field]) || !preg_match('/^[0-9]+$/D', $_POST[$field])
          || filter_var($_POST[$field], FILTER_VALIDATE_INT,
               array('options' => array('min_range' => $minimum, 'max_range' => $maximum))) === false) {
        bad_request(l10n('Invalid registration code count or identifier'));
        exit;
      }
    }
  }
  foreach (array('register_expiry', 'batch_expiry') as $field) {
    if (!empty($_POST[$field])) {
      $date = DateTime::createFromFormat('!Y-m-d H:i:s', $_POST[$field]);
      if (!$date || $date->format('Y-m-d H:i:s') !== $_POST[$field]) {
        bad_request(l10n('Invalid registration code expiry; use YYYY-MM-DD HH:MM:SS'));
        exit;
      }
    }
  }
  if (isset($_POST['register_code'])) {
    foreach (array('register_comment', 'uses', 'register_expiry') as $field) {
      if (!isset($_POST[$field])) { bad_request(l10n('Missing registration code field')); exit; }
    }
    if (strlen(stripslashes($_POST['register_code'])) > 255
        || strlen(stripslashes($_POST['register_comment'])) > 255) {
      bad_request(l10n('Registration code and comment must be at most 255 bytes'));
      exit;
    }
  }
  if (isset($_POST['batch_comment']) && strlen(stripslashes($_POST['batch_comment'])) > 255) {
    bad_request(l10n('Registration comment must be at most 255 bytes'));
    exit;
  }
  // Decode Piwigo's input escaping once, then escape for this DB connection.
  foreach (array('register_code', 'register_comment', 'batch_comment', 'code') as $field) {
    if (isset($_POST[$field])) {
      $_POST[$field] = pwg_db_real_escape_string(stripslashes($_POST[$field]));
    }
  }
}
$template->assign('REGISTER_CODES_DIRECTION', in_array(substr($user['language'] ?? '', 0, 2), array('ar', 'ur')) ? 'rtl' : 'ltr');
$template->assign('REGISTER_CODES_TOKEN', get_pwg_token());
$template->assign('REGISTER_CODES_ASSET_PATH', 'plugins/'.basename(__DIR__).'/');

if (isset($_POST["register_code"])) {

  $register_code = $_POST["register_code"];
  $register_comment = $_POST["register_comment"];
  $uses = $_POST["uses"];
  $register_expiry = $_POST["register_expiry"];
  $_SESSION['reg_codes_uses_default'] = $uses;
  $check_query = 'select * from ' . $prefixeTable . "register_codes where code='$register_code'";
  $code_count = pwg_db_num_rows(pwg_query($check_query));

  if ($code_count >= 1) {
    echo '<p class="errors">'.htmlspecialchars(l10n('Duplicate Code Entry Not Allowed.'), ENT_QUOTES, 'UTF-8').'</p>';
  }else{
    if($register_code == "") {
        echo '<p class="errors">'.htmlspecialchars(l10n('Registration Code Required.'), ENT_QUOTES, 'UTF-8').'</p>';
    }else{
      if($register_expiry == "") {
        $query = 'insert into ' . $prefixeTable . "register_codes (code,comment,uses,expiry) values ('$register_code','$register_comment','$uses',NULL)";
      }else{
        $query = 'insert into ' . $prefixeTable . "register_codes (code,comment,uses,expiry) values ('$register_code','$register_comment','$uses','$register_expiry')";
      }
      pwg_query($query);
      $self_url = get_admin_plugin_menu_link(dirname(__FILE__)).'/admin.php';
      redirect($self_url);
    }
  }
}

if (isset($_POST["id"],$_POST["code"])) {
  $query = 'delete from ' . $prefixeTable . 'register_codes where id="' . $_POST["id"] . '" and code="' . $_POST["code"] . '"';
  pwg_query($query);
  $self_url = get_admin_plugin_menu_link(dirname(__FILE__)).'/admin.php';
  redirect($self_url);
}

function register_codes_get_codes() {
  global $template, $prefixeTable;
  
  $query = 'SELECT * FROM ' . $prefixeTable . 'register_codes ORDER BY id DESC';
  $result = pwg_query($query);
  
  $codes = array();
  $expired_codes = array();
  
  while ($row = pwg_db_fetch_assoc($result)) {
    // Check if code is expired by usage count or date
    if (($row['used'] >= $row['uses'] && $row['uses'] != 0) || 
        ($row['expiry'] !== NULL && strtotime($row['expiry']) < time())) {
      $expired_codes[] = $row;
    } else {
      $codes[] = $row;
    }
  }
  
  if(isset($_SESSION['reg_codes_uses_default'])){
    $template->assign('reg_codes_uses_default', $_SESSION['reg_codes_uses_default']);
  }else{
    $template->assign('reg_codes_uses_default', 0);
  }
  
  $template->assign('register_codes', $codes);
  $template->assign('expired_codes', $expired_codes);
  
  // Pass any generated batch codes to the template
  if (isset($_SESSION['generated_batch_codes'])) {
    $template->assign('generated_batch_codes', $_SESSION['generated_batch_codes']);
    $template->assign('batch_success_count', $_SESSION['batch_success_count']);
    
    // Clear the session values after they've been displayed once
    if (!isset($_POST["batch_count"])) {
      unset($_SESSION['generated_batch_codes']);
      unset($_SESSION['batch_success_count']);
    }
  }
}
// Batch code generator function
if (isset($_POST["batch_count"])) {
  $batch_count = intval($_POST["batch_count"]);
  $batch_comment = isset($_POST["batch_comment"]) ? $_POST["batch_comment"] : '';
  $batch_uses = isset($_POST["batch_uses"]) ? $_POST["batch_uses"] : 1;
  $batch_expiry = isset($_POST["batch_expiry"]) ? $_POST["batch_expiry"] : '';
  $_SESSION['reg_codes_uses_default'] = $batch_uses;
  
  $generated_codes = array();
  $success_count = 0;
  
  // Generate requested number of codes
  for ($i = 0; $i < $batch_count; $i++) {
    // Generate a unique code
    $unique = false;
    $code = '';
    
    while (!$unique) {
      // Generate random code (combination of two random strings for better uniqueness)
      $code = bin2hex(random_bytes(16));
      
      // Check if code already exists
      $check_query = 'SELECT COUNT(*) FROM ' . $prefixeTable . "register_codes WHERE code='$code'";
      $result = pwg_query($check_query);
      list($count) = pwg_db_fetch_row($result);
      
      if ($count == 0) {
        $unique = true;
      }
    }
    
    // Insert the code
    if ($batch_expiry == "") {
      $query = 'INSERT INTO ' . $prefixeTable . "register_codes (code, comment, uses, expiry) VALUES ('$code', '$batch_comment', '$batch_uses', NULL)";
    } else {
      $query = 'INSERT INTO ' . $prefixeTable . "register_codes (code, comment, uses, expiry) VALUES ('$code', '$batch_comment', '$batch_uses', '$batch_expiry')";
    }
    
    if (pwg_query($query)) {
      $success_count++;
      $generated_codes[] = $code;
    }
  }
  
  // Store generated codes in session for display
  $_SESSION['generated_batch_codes'] = $generated_codes;
  $_SESSION['batch_success_count'] = $success_count;
  
  $self_url = get_admin_plugin_menu_link(dirname(__FILE__)).'/admin.php';
  redirect($self_url);
}

// Fetch the template.
global $template, $prefixeTable;
include_once(PHPWG_ROOT_PATH . 'admin/include/tabsheet.class.php');
register_codes_get_codes();
$template->assign('registration_history',register_codes_get_registration_history());

//Default Expiry Settings
$template->assign('register_expiry', date("Y-m-d H:i:00", strtotime("+1 week", strtotime("now"))));
$template->assign('batch_expiry', date("Y-m-d H:i:00", strtotime("+1 week", strtotime("now"))));

// Add our template to the global template
$template->set_filenames(
 array(
    'plugin_admin_content' => dirname(__FILE__) . '/admin.tpl',
 )
);

// Assign the template contents to ADMIN_CONTENT
$template->assign_var_from_handle('ADMIN_CONTENT', 'plugin_admin_content');


// Get users who used the codes
function register_codes_get_registration_history()
{
  global $prefixeTable;
  
  $query = 'SELECT * FROM ' . $prefixeTable . 'register_codes_users ORDER BY created_at DESC';
  $result = pwg_query($query);
  $history = array();
  while ($row = pwg_db_fetch_assoc($result))
  {
    $history[] = $row;
  }

  return $history;
}

?>
