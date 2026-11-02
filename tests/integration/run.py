import subprocess,secrets,time,json,concurrent.futures
from pathlib import Path
root=str(Path(__file__).resolve().parent)+'/'
def manage(op,tag,*args):
 p=subprocess.run(['php',root+'manage.php',op,tag,*map(str,args)],capture_output=True,text=True)
 if p.returncode:raise RuntimeError(p.stdout+p.stderr)
 return p.stdout
for name,limit,n,expected,mode,expiry in [('single',1,12,1,'normal','normal'),('limited',3,12,3,'normal','normal'),('unlimited',0,8,8,'normal','normal'),('expired',1,4,0,'normal','expired'),('pre-failure',1,4,0,'pre-failure','normal'),('late-failure',1,4,0,'late-failure','normal')]:
 tag='rctest_'+secrets.token_hex(6)
 try:
  manage('setup',tag,limit,expiry)
  start=time.time()+3
  def work(i):
   p=subprocess.run(['php',root+'worker.php',tag,str(i),tag,str(start),mode],capture_output=True,text=True)
   if p.returncode:raise RuntimeError(p.stdout+p.stderr)
   return json.loads(p.stdout.strip().splitlines()[-1])
  with concurrent.futures.ThreadPoolExecutor(max_workers=n) as pool:results=list(pool.map(work,range(n)))
  state=json.loads(manage('status',tag));success=sum(bool(x['id']) for x in results)
  assert success==expected and state==dict(used=expected,users=expected,history=expected),(name,results,state)
  print('PASS:',name,n,'concurrent attempts;',state,flush=True)
  if mode in ('pre-failure','late-failure'):
   p=subprocess.run(['php',root+'worker.php',tag,'99',tag,'0','normal'],capture_output=True,text=True)
   assert p.returncode==0,p.stdout+p.stderr
   state=json.loads(manage('status',tag))
   assert state==dict(used=1,users=1,history=1),state
   print('PASS: code remains redeemable after failed registration',flush=True)
 finally:print(manage('cleanup',tag),flush=True)
