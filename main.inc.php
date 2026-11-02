<?php
/*
Version: 1.5.1
Plugin Name: Register Codes
Plugin URI: https://piwigo.org/ext/index.php?eid=997
Author: swhite-photos
Description: Plugin that requires users to have a registration code to register.
Attributions: foundation-datepicker jss/cs plugin available from https://github.com/najlepsiwebdesigner/foundation-datepicker and also had help by reading the piwigo captcha plugin(s)
*/

// Chech whether we are indeed included by Piwigo.
if (!defined('PHPWG_ROOT_PATH')) die('Hacking attempt!');

// Define the path to our plugin.
define('REGISTER_CODES_PATH', PHPWG_PLUGINS_PATH.basename(dirname(__FILE__)).'/');

// Piwigo includes these callbacks only when an event first needs them.
$register_codes_events = REGISTER_CODES_PATH.'include/events.inc.php';
add_event_handler('get_admin_plugin_menu_links', 'register_codes_admin_menu', 50, $register_codes_events);
add_event_handler('loc_begin_register', 'register_codes_register_init', 50, $register_codes_events);
add_event_handler('init', 'register_codes_init', 50, $register_codes_events);
// Finalize before ordinary post-registration handlers can fail.
add_event_handler('register_user', 'register_codes_log_user_registration', 0, $register_codes_events);
unset($register_codes_events);
