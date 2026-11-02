<?php
error_reporting(E_ALL);
define('PHPWG_ROOT_PATH',__DIR__.'/fixtures/');
class PluginMaintain {}
$prefixeTable='test_';$queries=[];
function pwg_query($q){global $queries;$queries[]=$q;return true;}
include dirname(__DIR__).'/maintain.class.php';
$m=new piwigo_register_codes_maintain;$errors=[];
$m->install('1.5.1',$errors);$m->activate('1.5.1',$errors);$m->activate('1.5.1',$errors);$m->update('1.5','1.5.1',$errors);
if(count($queries)!==8 || $errors)throw new Exception('Missing lifecycle schema check');
foreach($queries as $q)if(!str_contains($q,'CREATE TABLE IF NOT EXISTS test_'))throw new Exception('Non-idempotent schema initialization');
$queries=[];$m->deactivate();if($queries)throw new Exception('Deactivation changed data');
$m->uninstall();if(count($queries)!==2)throw new Exception('Incomplete uninstall');
foreach($queries as $q)if(!str_contains($q,'DROP TABLE IF EXISTS test_'))throw new Exception('Unexpected uninstall');
echo "PASS: explicit install/update, idempotent activation, non-destructive deactivation and scoped uninstall\n";
