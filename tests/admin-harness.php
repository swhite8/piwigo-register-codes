<?php
error_reporting(E_ALL);
set_error_handler(function($n,$s){throw new Exception($s);});
define('PHPWG_ROOT_PATH', __DIR__.'/fixtures/');define('ACCESS_ADMINISTRATOR',1);
$prefixeTable='test_';$_SESSION=[];$queries=[];
class Stop extends Exception {}
class FakeTemplate {function assign(...$v){} function set_filenames(...$v){} function assign_var_from_handle(...$v){}}
$template=new FakeTemplate;
function l10n($s){return $s;}
function check_status($s){} function get_pwg_token(){return 'expected-token';}
function access_denied(){throw new Stop('denied');} function bad_request($s){throw new Stop('invalid');}
function check_pwg_token(){if(($_REQUEST['pwg_token']??null)!==get_pwg_token())access_denied();}
function pwg_db_real_escape_string($s){return addslashes($s);}
function pwg_query($q){global $queries;$queries[]=$q;return new ArrayIterator(str_contains($q,'COUNT(*)')?[[0]]:[]);}
function pwg_db_num_rows($r){return count($r);} function pwg_db_fetch_row($r){$v=$r->current();$r->next();return $v;}
function pwg_db_fetch_assoc($r){if(!$r->valid())return false;$v=$r->current();$r->next();return $v;}
function get_admin_plugin_menu_link($s){return 'admin.php?page=plugin&section=piwigo-register-codes';}
function redirect($s){throw new Stop('redirect');}
$input=json_decode($argv[1],true);$_POST=$input;array_walk_recursive($_POST,function(&$v){$v=addslashes($v);});$_REQUEST=$_POST;$_SERVER['REQUEST_METHOD']='POST';
try{include dirname(__DIR__).'/admin.php';$status='ok';}catch(Stop $e){$status=$e->getMessage();}
echo json_encode(['status'=>$status,'queries'=>$queries,'session'=>$_SESSION]);
