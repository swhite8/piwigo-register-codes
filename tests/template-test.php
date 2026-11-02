<?php
error_reporting(E_ALL);
set_error_handler(function($n,$s){throw new Exception($s);});
define('PHPWG_ROOT_PATH', __DIR__.'/fixtures/');
define('REGISTER_CODES_PATH', dirname(__DIR__).'/');
$temporary = sys_get_temp_dir().'/register-codes-theme-'.bin2hex(random_bytes(6));
$override = $temporary.'/plugins/piwigo-register-codes/register_codes_register.tpl';
mkdir(dirname($override),0700,true);file_put_contents($override,'theme override');
register_shutdown_function(function() use ($override,$temporary){unlink($override);rmdir(dirname($override));rmdir($temporary.'/plugins');rmdir($temporary);});
class TestRegistrationTemplate {
  public $filename;
  function get_template_dir(){global $temporary;return [$temporary];}
  function set_filename($handle,$filename){$this->filename=$filename;}
  function assign(...$args){}
  function parse(...$args){return '';}
}
function add_event_handler(...$args){}
$template=new TestRegistrationTemplate;$prefixeTable='test_';
include dirname(__DIR__).'/register_codes_register.php';
if ($template->filename !== $override)throw new Exception('Theme override ignored');
$field='{$REGISTERCODES.parsed_content}';
foreach (['<form><fieldset>one</fieldset><fieldset>two</fieldset><input type="submit"></form>', '<form><input type="submit"></form>', '<form><button type="submit">Register</button></form>'] as $source) {
  $result=register_codes_prefilter_registration($source);
  if(substr_count($result,$field)!==1 || register_codes_prefilter_registration($result)!==$result)throw new Exception('Duplicate/missing field');
}
if(register_codes_prefilter_registration('<div>unknown</div>')!=='<div>unknown</div>')throw new Exception('Unknown template changed');
$root=rtrim(getenv('PIWIGO_ROOT')?:'','/');
foreach(['default','bootstrap_darkroom'] as $theme){
  $file=$root.'/themes/'.$theme.'/template/register.tpl';
  if(is_file($file) && substr_count(register_codes_prefilter_registration(file_get_contents($file)),$field)!==1)throw new Exception('Installed theme injection failed');
}
echo "PASS: theme override, single/idempotent insertion, submit fallback, and installed theme templates\n";
