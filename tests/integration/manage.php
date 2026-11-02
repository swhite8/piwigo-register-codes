<?php
$bootstrap = getenv('REGISTER_CODES_TEST_BOOTSTRAP');
if (!$bootstrap || !is_file($bootstrap)) { throw new RuntimeException('Set REGISTER_CODES_TEST_BOOTSTRAP to a CLI Piwigo bootstrap'); }
require $bootstrap;
$tag=$argv[2];if(!preg_match('/^rctest_[a-f0-9]{12}$/D',$tag))exit(2);
$code=pwg_db_real_escape_string($tag);
if($argv[1]==='setup'){
$limit=(int)$argv[3];$expiry=$argv[4]==='expired'?"'2000-01-01 00:00:00'":'NULL';
pwg_query('INSERT INTO '.$prefixeTable."register_codes (code,comment,uses,used,expiry) VALUES ('$code','Temporary concurrency regression test',$limit,0,$expiry)");
}elseif($argv[1]==='status'){
$r=pwg_query('SELECT used FROM '.$prefixeTable."register_codes WHERE code='$code'");list($used)=pwg_db_fetch_row($r);
$r=pwg_query('SELECT COUNT(*) FROM '.USERS_TABLE." WHERE username LIKE '".$code."\\_%'");list($users)=pwg_db_fetch_row($r);
$r=pwg_query('SELECT COUNT(*) FROM '.$prefixeTable."register_codes_users WHERE code='$code'");list($history)=pwg_db_fetch_row($r);
echo json_encode(['used'=>(int)$used,'users'=>(int)$users,'history'=>(int)$history]);
}elseif($argv[1]==='cleanup'){
include_once PHPWG_ROOT_PATH.'admin/include/functions.php';
$r=pwg_query('SELECT id FROM '.USERS_TABLE." WHERE username LIKE '".$code."\\_%'");$ids=[];while($row=pwg_db_fetch_row($r))$ids[]=(int)$row[0];
foreach($ids as $id)delete_user($id);
pwg_query('DELETE FROM '.$prefixeTable."register_codes_users WHERE code='$code'");pwg_query('DELETE FROM '.$prefixeTable."register_codes WHERE code='$code'");
echo 'Removed '.count($ids).' temporary accounts and the test code';
}
