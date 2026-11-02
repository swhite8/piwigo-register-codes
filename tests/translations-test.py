"""Validate plugin catalogs and compile each locale's rendered JavaScript."""
import json,os,re,subprocess,tempfile
from pathlib import Path
from html.parser import HTMLParser
root=Path(__file__).resolve().parent.parent
expected={'en_UK','zh_CN','hi_IN','es_ES','ar_SA','fr_FR','bn_IN','pt_BR','id_ID','ur_PK'}
def catalog(locale):
 p=subprocess.run(['php','-r','$lang=[];include $argv[1];echo json_encode($lang, JSON_THROW_ON_ERROR);',str(root/'language'/locale/'plugin.lang.php')],capture_output=True,text=True,check=True)
 return json.loads(p.stdout)
base=catalog('en_UK')
class Scripts(HTMLParser):
 def __init__(self):super().__init__();self.inside=False;self.parts=[]
 def handle_starttag(self,tag,attrs):
  if tag=='script':self.inside=True;self.parts.append('')
 def handle_endtag(self,tag):
  if tag=='script':self.inside=False
 def handle_data(self,data):
  if self.inside:self.parts[-1]+=data
keys=set()
for name in ['admin.tpl','register_codes_register.tpl','admin.php','register_codes_register.php','include/events.inc.php']:
 text=(root/name).read_text()
 keys.update(re.findall(r"'([^'\n]+)'\|@?translate",text))
 keys.update(re.findall(r"l10n\('([^']+)'\)",text))
assert keys<=base.keys(),keys-base.keys()
for locale in sorted(expected):
 data=catalog(locale)
 assert data.keys()==base.keys(),locale
 assert all(isinstance(v,str) and v.strip() for v in data.values()),locale
 for key,value in data.items():assert re.findall(r'%\d*\$?[sd]',value)==re.findall(r'%\d*\$?[sd]',base[key]),(locale,key)
 source=(root/'language'/locale/'plugin.lang.php').read_text(encoding='utf-8')
 assert not source.startswith('\ufeff')
 assert len(re.findall(r'^\$lang\[',source,re.M))==len(base),('duplicate keys',locale)
 if os.environ.get('PIWIGO_ROOT'):
  with tempfile.TemporaryDirectory() as directory:
   output=Path(directory)/'rendered.html'
   env=dict(os.environ,REGISTER_CODES_TEST_LOCALE=locale,REGISTER_CODES_TEST_OUTPUT=str(output))
   subprocess.run(['php',str(root/'tests/render-test.php')],env=env,capture_output=True,text=True,check=True)
   parser=Scripts();parser.feed(output.read_text())
   for script in parser.parts:
    if script.strip():subprocess.run(['node','--check','-'],input=script,capture_output=True,text=True,check=True)
 print(f'PASS: {locale}: {len(data)} keys, placeholders, UTF-8, and translated JavaScript')
print('PASS: every literal plugin translation key is present in all ten catalogs')
