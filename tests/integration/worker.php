<?php
$bootstrap = getenv('REGISTER_CODES_TEST_BOOTSTRAP');
if (!$bootstrap || !is_file($bootstrap)) { throw new RuntimeException('Set REGISTER_CODES_TEST_BOOTSTRAP to a CLI Piwigo bootstrap'); }
require $bootstrap;
trigger_notify('loc_begin_register');
$tag=$argv[1];$i=(int)$argv[2];$code=$argv[3];$start=(float)$argv[4];$mode=$argv[5]??'normal';
if($mode==='late-failure')add_event_handler('register_user_check',function($errors){$errors[]='intentional late validation failure';return $errors;},PHP_INT_MAX);
$_POST['register_code']=addslashes($code);
$errors=$mode==='pre-failure'?['intentional validation failure']:[];
while(microtime(true)<$start)usleep(1000);
$id=register_user($tag.'_'.$i,bin2hex(random_bytes(20)),$tag.'_'.$i.'@example.invalid',false,$errors,false);
echo json_encode(['id'=>$id,'errors'=>array_values($errors)])."\n";
