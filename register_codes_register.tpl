<br><br>
{* 
  Registration code field: autocomplete disabled to reduce the chance that
  browsers suggest previously used codes from other sessions/devices.
*}
{'Registration Code'|@translate} *
<input type="text"
 name="register_code"
 id="register_code"
 value=""
 class="form-control" 
 placeholder="{'Registration Code Required'|@translate}"
 autocomplete="off"
 required="required">
