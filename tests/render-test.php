<?php
$root = rtrim(getenv('PIWIGO_ROOT') ?: '', '/');
if (!$root || !is_file($root.'/include/smarty/libs/Smarty.class.php')) {
  fwrite(STDERR, "Set PIWIGO_ROOT to a Piwigo installation to run the Smarty test.\n");
  exit(2);
}
require $root.'/include/smarty/libs/Smarty.class.php';
$s=new Smarty\Smarty;
$compiled = sys_get_temp_dir().'/register-codes-smarty-'.bin2hex(random_bytes(6));
mkdir($compiled, 0700);
register_shutdown_function(function() use ($compiled) {
  foreach (glob($compiled.'/*') as $file) { unlink($file); }
  rmdir($compiled);
});
$s->setCompileDir($compiled);
$translations = array();
$locale = getenv('REGISTER_CODES_TEST_LOCALE');
if ($locale) {
  if (!preg_match('/^[a-z]{2}_[A-Z]{2}$/D', $locale)) throw new Exception('Invalid test locale');
  $lang = array();
  include dirname(__DIR__).'/language/'.$locale.'/plugin.lang.php';
  $translations = $lang;
}
$s->registerPlugin('modifier','translate',fn($x)=>$translations[$x] ?? $x);
$s->registerPlugin('block','html_style',function($p,$c){return $c;});
$assets = array();
$s->registerPlugin('function','combine_css',function($p) use (&$assets){$assets[]=$p['path'];return '';});
$s->registerPlugin('function','combine_script',function($p) use (&$assets){$assets[]=$p['path'];if (($p['require'] ?? '') !== 'jquery') throw new Exception('Missing jQuery dependency');return '';});
$x='</textarea><script>alert(1)</script>"\'&';
$row=['id'=>1,'code'=>$x,'comment'=>$x,'uses'=>0,'used'=>0,'expiry'=>null,'created_at'=>$x];
$s->assign(['REGISTER_CODES_DIRECTION'=>in_array($locale, array('ar_SA','ur_PK')) ? 'rtl' : 'ltr','REGISTER_CODES_ASSET_PATH'=>'plugins/piwigo-register-codes/','register_codes'=>[$row],'expired_codes'=>[$row],'registration_history'=>[['code'=>$x,'user_name'=>$x,'created_at'=>$x]],'REGISTER_CODES_TOKEN'=>'test-token','reg_codes_uses_default'=>1,'register_expiry'=>'2027-01-01 00:00:00','batch_expiry'=>'2027-01-01 00:00:00']);
$html=$s->fetch(dirname(__DIR__).'/admin.tpl');
if(str_contains($html,$x))throw new Exception('Unescaped payload');
if(substr_count($html,'name="pwg_token"')!==4)throw new Exception('Missing form token');

if ($assets !== ['plugins/piwigo-register-codes/css/foundation-datepicker.css','plugins/piwigo-register-codes/js/foundation-datepicker.js','plugins/piwigo-register-codes/css/admin.css']) throw new Exception('Unexpected asset paths');
if ($output = getenv('REGISTER_CODES_TEST_OUTPUT')) file_put_contents($output, $html);
echo "PASS: actual Smarty template renders, escapes stored payloads, and includes tokens on all mutation forms\n";
