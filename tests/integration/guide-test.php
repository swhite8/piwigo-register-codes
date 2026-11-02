<?php
$bootstrap = getenv('REGISTER_CODES_TEST_BOOTSTRAP');
if (!$bootstrap || !is_file($bootstrap)) { throw new RuntimeException('Set REGISTER_CODES_TEST_BOOTSTRAP to a CLI Piwigo bootstrap'); }
require $bootstrap;
include_once dirname(__DIR__, 2).'/maintain.class.php';
$original_prefix=$prefixeTable;$prefixeTable='rclifecycle_'.bin2hex(random_bytes(6)).'_';
$maintenance=new piwigo_register_codes_maintain('piwigo-register-codes');$errors=[];
try {
  $maintenance->install('1.5.1',$errors);
  pwg_query('INSERT INTO '.$prefixeTable."register_codes (code,uses,used) VALUES ('preserve-test',1,0)");
  foreach(['activate','activate'] as $action)$maintenance->$action('1.5.1',$errors);
  $maintenance->update('1.5','1.5.1',$errors);$maintenance->deactivate();
  $r=pwg_query('SELECT COUNT(*) FROM '.$prefixeTable.'register_codes');list($n)=pwg_db_fetch_row($r);
  if((int)$n!==1 || $errors)throw new Exception('Lifecycle lost records');
  echo "PASS: real database install, repeated activation, update and deactivation preserve records in disposable tables\n";
} finally {$maintenance->uninstall();$prefixeTable=$original_prefix;}
// Exercise actual Piwigo loaders with a non-root public URL prefix.
$page['root_path']='/photos/';$conf['template_combine_files']=false;
$template->func_combine_script(['id'=>'register_codes.datepicker','require'=>'jquery','load'=>'header','path'=>'plugins/piwigo-register-codes/js/foundation-datepicker.js']);
$template->func_combine_css(['path'=>'plugins/piwigo-register-codes/css/admin.css']);
$template->output=Template::COMBINED_SCRIPTS_TAG.Template::COMBINED_CSS_TAG;
ob_start();$template->flush();$assets=ob_get_clean();
if(!str_contains($assets,'src="/photos/') || !str_contains($assets,'href="/photos/') || str_contains($assets,'src="/plugins/') || str_contains($assets,'href="/plugins/'))throw new Exception('Subdirectory assets broken');
echo "PASS: actual Piwigo script/CSS loaders render /photos/ URLs correctly\n";
