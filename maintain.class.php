<?php
defined('PHPWG_ROOT_PATH') or die('Hacking attempt!');

class piwigo_register_codes_maintain extends PluginMaintain {

  function install($plugin_version, &$errors=array()) {
    $this->create_tables();
  }

  function activate($plugin_version, &$errors=array()) {
    // Also support installations upgrading from versions that created on activate.
    $this->create_tables();
  }

  function update($old_version, $new_version, &$errors=array()) {
    // No schema migration is needed for 1.5.1. Keep existing data untouched.
    $this->create_tables();
  }

  private function create_tables() {
    global $prefixeTable;

    // create table
    $query = 'CREATE TABLE IF NOT EXISTS ' . $prefixeTable . 'register_codes (
id INT AUTO_INCREMENT PRIMARY KEY,
code VARCHAR(255) NOT NULL,
comment VARCHAR(255) NULL,
uses int not null default 0,
used int not null default 0,
expiry TIMESTAMP NULL,
created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;';
    pwg_query($query);

    $query2 = 'CREATE TABLE IF NOT EXISTS ' . $prefixeTable . 'register_codes_users (
code VARCHAR(255) NOT NULL,
user_name VARCHAR(255) NOT NULL,
created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;';
    pwg_query($query2);
  }

  function deactivate() {
    // Keep codes and history when temporarily disabling the plugin.
  }

  function uninstall() {
    global $prefixeTable;
    pwg_query('DROP TABLE IF EXISTS ' . $prefixeTable . 'register_codes;');
    pwg_query('DROP TABLE IF EXISTS ' . $prefixeTable . 'register_codes_users;');
  }


}

?>
