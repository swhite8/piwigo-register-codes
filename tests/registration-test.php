<?php
error_reporting(E_ALL);set_error_handler(function($n,$s){throw new Exception($s);});
define('PHPWG_ROOT_PATH', __DIR__.'/fixtures/');define('PHPWG_PLUGINS_PATH', dirname(__DIR__, 2).'/');
class T {function set_filename(...$x){}function assign(...$x){}function parse(...$x){return '';}function get_template_dir(){return [];}}
$template=new T;$prefixeTable='test_';$_SESSION=[];$queries=[];$rows=[[12]];$changes=1;$account=false;
function add_event_handler(...$x){}function l10n($s){return $s;}function pwg_db_real_escape_string($s){return addslashes($s);}
function pwg_query($q){global $queries,$rows;$queries[]=$q;return $rows;}
function pwg_db_num_rows($r){return count($r);}function pwg_db_fetch_row($r){return $r[0];}
function pwg_db_changes(){global $changes;return $changes;}
function get_userid($name){global $account;return $account;}
include dirname(__DIR__).'/main.inc.php';include dirname(__DIR__).'/include/events.inc.php';include dirname(__DIR__).'/register_codes_register.php';
function ok($condition){if(!$condition)throw new Exception('Assertion failed');}
function attempt($errors=[]){$_POST=['register_code'=>addslashes("a'b\\c")];return register_codes_check_code($errors,['username'=>addslashes("O'Reilly")]);}
ok(attempt()===[]);ok($GLOBALS['register_codes_reservation']['code']==="a'b\\c");
ok(str_contains($queries[1],'AND (uses = 0 OR used < uses)'));ok(str_contains($queries[1],"expiry >= '"));
register_codes_release_reservation();ok(!isset($GLOBALS['register_codes_reservation']));ok(str_contains(end($queries),'used = used - 1'));
$queries=[];ok(attempt(['other validation error'])===['other validation error']);ok($queries===[]);
$changes=0;ok(count(attempt())===1);ok(!isset($GLOBALS['register_codes_reservation']));
$changes=1;$rows=[];ok(count(attempt())===1);$rows=[[12]];
$_SESSION['user_register_code']='stale';$_POST=['register_code'=>[]];ok(count(register_codes_check_code([],['username'=>'test']))===1);ok(!isset($_SESSION['user_register_code']));
$queries=[];register_codes_log_user_registration(['username'=>'admin-created']);ok($queries===[]);
ok(attempt()===[]);$queries=[];register_codes_log_user_registration(['username'=>addslashes("O'Reilly")]);ok(count($queries)===1);ok(str_contains($queries[0],"O\\'Reilly"));ok(!isset($GLOBALS['register_codes_reservation']));
ok(attempt()===[]);$account=123;$queries=[];register_codes_release_reservation();ok($queries===[]);$account=false;
class PluginMaintain {}include dirname(__DIR__).'/maintain.class.php';$m=new piwigo_register_codes_maintain;$queries=[];$m->deactivate();ok($queries===[]);
echo "PASS: atomic reservation, exhausted codes, failed-validation rollback, created-account retention, escaped history and non-destructive deactivation\n";
