<?php
$bootstrap = getenv('REGISTER_CODES_TEST_BOOTSTRAP');
if (!$bootstrap || !is_file($bootstrap)) { throw new RuntimeException('Set REGISTER_CODES_TEST_BOOTSTRAP to a CLI Piwigo bootstrap'); }
require $bootstrap;
foreach (array('en_UK','zh_CN','hi_IN','es_ES','ar_SA','fr_FR','bn_IN','pt_BR','id_ID','ur_PK') as $locale) {
  $expected=array();$lang_before=$lang;$lang=array();
  include PHPWG_ROOT_PATH.'plugins/piwigo-register-codes/language/'.$locale.'/plugin.lang.php';
  $expected=$lang;$lang=$lang_before;$user['language']=$locale;
  load_language('plugin.lang',REGISTER_CODES_PATH,array('language'=>$locale));
  foreach($expected as $key=>$value)if(l10n($key)!==$value)throw new Exception('Locale load mismatch: '.$locale.' '.$key);
  echo 'PASS: actual Piwigo language loader '.$locale."\n";
}
