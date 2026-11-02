import subprocess,json
from pathlib import Path
base=str(Path(__file__).resolve().parent)+'/'
def run(data):
 r=subprocess.run(['php',base+'admin-harness.php',json.dumps(data)],capture_output=True,text=True)
 assert r.returncode==0,r.stderr
 return json.loads(r.stdout)
for data in [{'register_code':'x'}, {'batch_count':'5'}, {'id':'1','code':'x'}, {'pwg_token': 'wrong','batch_count':'5'}]:
 r=run(data);assert r['status']=='denied' and not r['queries'],r
for data in [{'batch_count':'101'},{'batch_count':'-1'},{'batch_count':[]},{'id':'1 OR 1=1','code':'x'},{'uses':'-1','register_code':'x','register_comment':'','register_expiry':''},{'register_code':[]},{'batch_expiry':'2026-02-31 00:00:00','batch_count':'1'}]:
 r=run(dict(pwg_token='expected-token',**data));assert r['status']=='invalid' and not r['queries'],r
r=run(dict(pwg_token='expected-token',register_code="a'b\\c",register_comment="</textarea><script>alert(1)</script>",uses='0',register_expiry=''))
assert r['status']=='redirect' and len(r['queries'])==2,r
assert "a\\'b\\\\c" in r['queries'][1],r
r=run(dict(pwg_token='expected-token',id='12',code="a'b"));assert r['status']=='redirect' and "id=\"12\"" in r['queries'][0],r
r=run(dict(pwg_token='expected-token',batch_count='3',batch_uses='1',batch_expiry='2027-01-01 12:00:00'))
assert r['status']=='redirect' and len(r['session']['generated_batch_codes'])==3,r
assert len(set(r['session']['generated_batch_codes']))==3
print('PASS: CSRF rejection, malformed fields, bounds/date checks, escaped create/delete, secure batch generation')
