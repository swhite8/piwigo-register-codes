{html_style}
#register-codes-admin {
--table-header-bg: #f9f9f9;
--table-header-color: white;
--row-odd-bg: #f9f9f9;
--row-even-bg: #f1f1f1;
--border-color: #ddd;
--hover-color: #ffedde;
--text-color: #000000;
--btn-bg: #dddddd;
--btn-color: #000000;
}
#register-codes-admin tbody tr:hover {
background-color: var(--hover-color) !important;
}
#register-codes-admin #code_table .row-one {
background-color: var(--row-odd-bg);
}
#register-codes-admin #code_table .row-two {
background-color: var(--row-even-bg);
}
#register-codes-admin .btn-bg {
background-color: var(--btn-bg);
color: var(--btn-color);
}

#register-codes-admin th, #register-codes-admin td {
border: 1px solid var(--border-color);
}
#register-codes-admin tbody tr:nth-child(even):not(.row1):not(.row2) {
background-color: var(--row-even-bg);
}

#register-codes-admin .details {
  border: 1px solid var(--border-color);
  border-radius: 5px;
}
{/html_style}
{function name=displayCodesTable table_title='' codes_data=null enable_copy=true}
<div class="rc-table-scroll{if $enable_copy} active_codes_table{/if}" id="code_table" tabindex="0" role="region" aria-label="{$table_title|@translate|escape:'html'}">
  <table border=1>
    <tr>
      <th colspan="8" class="row-one">{$table_title|@translate|escape:'html'}</th>
    </tr>
    <tr>
      <th>{'ID'|@translate|escape:'html'}</th>
      <th>{'Code'|@translate|escape:'html'}</th>
      <th>{'Comment'|@translate|escape:'html'}</th>
      <th>{'Number Of Uses'|@translate|escape:'html'}</th>
      <th>{'Times Used'|@translate|escape:'html'}</th>
      <th>{'Expires At'|@translate|escape:'html'}</th>
      <th>{'Created At'|@translate|escape:'html'}</th>
      <th></th>
    </tr>

    {foreach from=$codes_data item=data}
        <tr class="{cycle values='row-one,row-two'}">
          <td>
            <input class="column-short" name="id" value="{$data.id|escape:'html'}" id="id" readonly />
          </td>
          <td>
            {if $enable_copy}
              <button type="button" class="btn pluginActionLevel1 btn-copy" data-code="{$data.code|escape:'html'}" onclick="copyCode(this.dataset.code)">{'Copy code'|@translate|escape:'html'}</button>
            {/if}
            <input name="code" value="{$data.code|escape:'html'}" id="code" readonly />
          </td>
          <td>
            {if !empty($data.comment)}<textarea class="span2" name="comment"
              id="comment">{$data.comment|escape:'html'}</textarea>{else}-
              {/if}
          </td>
          <td>
            <input name="uses" class="column-medium" value="{if $data.uses == '0'}{'Unlimited'|@translate|escape:'html'}{else}{$data.uses|escape:'html'}{/if}" id="uses"
              readonly />
          </td>
          <td>
            <input class="column-short" name="used" value="{$data.used|escape:'html'}" id="used" readonly />
          </td>
          <td>
            <input class="column-medium" name="expiry" value="{if isset($data.expiry)}{$data.expiry|escape:'html'}{else}-{/if}" id="expiry" readonly />
          </td>
          <td>
            <input class="column-medium" name="created_at" value="{$data.created_at|escape:'html'}" id="created_at" readonly />
          </td>
          <td>
            <form method="post">
              <input type="hidden" name="pwg_token" value="{$REGISTER_CODES_TOKEN|escape:'html'}">
              <input type="hidden" name="id" value="{$data.id|escape:'html'}">
              <input type="hidden" name="code" value="{$data.code|escape:'html'}">
            <button class="btn btn-red" type="submit">{'Delete'|@translate|escape:'html'}</button>
            </form>
          </td>
        </tr>
    {/foreach}
  </table>
  </div>
{/function}
<div id="register-codes-admin" dir="{$REGISTER_CODES_DIRECTION|escape:'html'}">
<div class="titlePage">
  <h2>{'Register Codes Plugin'|@translate|escape:'html'}</h2>
</div>
{combine_css path=$REGISTER_CODES_ASSET_PATH|cat:'css/foundation-datepicker.css'}
{combine_script id='register_codes.datepicker' require='jquery' load='header' path=$REGISTER_CODES_ASSET_PATH|cat:'js/foundation-datepicker.js'}
<script>
  $(function() {
    $('#register_expiry').fdatepicker({
      format: 'yyyy-mm-dd hh:ii:ss',
      disableDblClickSelection: true,
      language: 'vi',
      pickTime: true
    });

    // Add dark theme detection and styling
    function isDarkTheme() {
      const bodyBg = window.getComputedStyle(document.body).backgroundColor;
      return bodyBg === 'rgb(68, 68, 68)'; // #444
    }

    function applyThemeStyles() {
      if (isDarkTheme()) {
        document.getElementById('register-codes-admin').style.setProperty('--table-header-bg', '#333333');
        document.getElementById('register-codes-admin').style.setProperty('--table-header-color', '#ffffff');
        document.getElementById('register-codes-admin').style.setProperty('--row-odd-bg', '#333333');
        document.getElementById('register-codes-admin').style.setProperty('--row-even-bg', '#3d3d3d');
        document.getElementById('register-codes-admin').style.setProperty('--border-color', '#666');
        document.getElementById('register-codes-admin').style.setProperty('--hover-color', '#4a3c2e');
        document.getElementById('register-codes-admin').style.setProperty('--text-color', '#ffffff');
        document.getElementById('register-codes-admin').style.setProperty('--btn-bg', '#333333');
        document.getElementById('register-codes-admin').style.setProperty('--btn-color', '#ffffff');
      }
    }

    applyThemeStyles();
  });

  function generateCode() {
    var bytes = new Uint8Array(16);
    window.crypto.getRandomValues(bytes);
    var code = Array.from(bytes, b => b.toString(16).padStart(2, '0')).join('');
    $('#register_code').val(code);
  }

  function copyCode(code) {
    if (navigator.clipboard && navigator.clipboard.writeText) {
      navigator.clipboard.writeText(code).then(
        () => {
          console.log("{'Code copied to clipboard'|@translate|escape:'javascript'}!");
        },
        (err) => {
          console.error("{'Failed to copy code'|@translate|escape:'javascript'}: ", err);
        }
      );
    } else {
      const textarea = document.createElement("textarea");
      textarea.value = code;
      document.body.appendChild(textarea);
      textarea.select();
      try {
        document.execCommand("copy");
        console.log("{'Code copied to clipboard'|@translate|escape:'javascript'}!");
      } catch (err) {
        console.error("{'Failed to copy code'|@translate|escape:'javascript'}: ", err);
      }
      document.body.removeChild(textarea);
    }
  }

  function copyCodesWithSameComment() {
    const searchComment = document.querySelector('#batch-code textarea#batch_code_copy').value.trim();
    if (!searchComment) {
      alert("{'Please enter a comment to search for'|@translate|escape:'javascript'}");
      return;
    }
    const codeTable = document.querySelector('.active_codes_table');
    const commentTextareas = codeTable.querySelectorAll('textarea[name="comment"]');
    
    let matchingCodes = [];
    
    commentTextareas.forEach(textarea => {
      if (textarea.value.trim() === searchComment) {
        const row = textarea.closest('tr');
        const codeInput = row.querySelector('input[name="code"]');
        if (codeInput && codeInput.value) {
          matchingCodes.push(codeInput.value);
        }
      }
    });
    
    if (matchingCodes.length === 0) {
      alert("{'No codes found with the matching comment'|@translate|escape:'javascript'}");
      return;
    }
    
    const codesText = matchingCodes.join('\n');
    
    if (navigator.clipboard && navigator.clipboard.writeText) {
      navigator.clipboard.writeText(codesText).then(
        () => {
            alert("{'Codes copied to clipboard: %d'|@translate|escape:'javascript'}".replace('%d', matchingCodes.length));
        },
        (err) => {
          console.error("{'Failed to copy codes'|@translate|escape:'javascript'}: ", err);
          alert("{'Failed to copy codes to clipboard'|@translate|escape:'javascript'}");
        }
      );
    } else {
      const textarea = document.createElement("textarea");
      textarea.value = codesText;
      document.body.appendChild(textarea);
      textarea.select();
      try {
        document.execCommand("copy");
        alert("{'Codes copied to clipboard: %d'|@translate|escape:'javascript'}".replace('%d', matchingCodes.length));
      } catch (err) {
        console.error("{'Failed to copy codes'|@translate|escape:'javascript'}: ", err);
        alert("{'Failed to copy codes to clipboard'|@translate|escape:'javascript'}");
      }
      document.body.removeChild(textarea);
    }
  }
</script>
{combine_css path=$REGISTER_CODES_ASSET_PATH|cat:'css/admin.css' order=1}
<fieldset>
  <div id="new-code">
    <p class="rc-description">{'Register Codes Description'|@translate|escape:'html'}</p>
    <form method="post">
    <input type="hidden" name="pwg_token" value="{$REGISTER_CODES_TOKEN|escape:'html'}">
    <table border=1 class="rc-form-table">
        <tr>
          <th colspan="5">{'Add New Code'|@translate|escape:'html'}</th>
        </tr>
        <tr class="rc-column-headings">
          <th>{'Code'|@translate|escape:'html'}</th>
          <th>{'Comment'|@translate|escape:'html'}</th>
          <th>{'Number Of Uses'|@translate|escape:'html'}<br>({'0 for unlimited'|@translate|escape:'html'})</th>
          <th>{'Expires At'|@translate|escape:'html'}</th>
          <th></th>
        </tr>
        <tr>
          <td data-label="{'Code'|@translate|escape:'html'}"><button type="button" class="btn btn-bg" onclick="generateCode()">{'Generate Code'|@translate|escape:'html'}</button>
            <p><input type="textarea" class="span2" name="register_code" placeholder="{'Example Code'|@translate|escape:'html'}"
                id="register_code"></p>
          </td>
          <td data-label="{'Comment'|@translate|escape:'html'}">
            <p><textarea class="span2" name="register_comment" placeholder="{'Optional Comment'|@translate|escape:'html'}"
                id="register_comment"></textarea></p>
          </td>
          <td data-label="{'Number Of Uses'|@translate|escape:'html'}">
            <p>
              <center><input type="number" id="uses" name="uses"
                  value="{if isset($reg_codes_uses_default) && $reg_codes_uses_default != ''}{$reg_codes_uses_default|escape:'html'}{else}1{/if}"
                  min="0"></center>
              <small class="rc-stacked-hint">{'0 for unlimited'|@translate|escape:'html'}</small>
            </p>
          </td>
          <td data-label="{'Expires At'|@translate|escape:'html'}">
            <p><input type="text" class="span2" name="register_expiry"
                value="{$register_expiry|escape:'html'}" id="register_expiry"></p>
          </td>
          <td class="rc-form-action"><button type="submit" class="btn btn-bg">{'Add'|@translate|escape:'html'}</button></td>
        </tr>
    </table>
    </form>
  </div>
  <div id="batch-code">
    <details class="details">
      <summary style="font-size: 1.2em; white-space: normal;">{'Batch Code Generator'|@translate|escape:'html'}</summary>
      <form method="post">
          <input type="hidden" name="pwg_token" value="{$REGISTER_CODES_TOKEN|escape:'html'}">
          <table border=1 class="table-margin rc-form-table">
          <tr class="rc-column-headings">
          <th>{'Number of Codes'|@translate|escape:'html'}</th>
          <th>{'Comment'|@translate|escape:'html'}</th>
          <th>{'Number Of Uses'|@translate|escape:'html'}<br>({'0 for unlimited'|@translate|escape:'html'})</th>
          <th>{'Expires At'|@translate|escape:'html'}</th>
          <th></th>
        </tr>
        <tr>
          <td data-label="{'Number of Codes'|@translate|escape:'html'}">
            <input type="number" name="batch_count" id="batch_count" value="10" min="1" max="100">
          </td>
          <td data-label="{'Comment'|@translate|escape:'html'}">
            <textarea class="span2" name="batch_comment" placeholder="{'Optional Comment'|@translate|escape:'html'}" id="batch_comment"></textarea>
          </td>
          <td data-label="{'Number Of Uses'|@translate|escape:'html'}">
            <center><input type="number" id="batch_uses" name="batch_uses" 
                   value="{if isset($reg_codes_uses_default) && $reg_codes_uses_default != ''}{$reg_codes_uses_default|escape:'html'}{else}1{/if}"
                   min="0"></center>
              <small class="rc-stacked-hint">{'0 for unlimited'|@translate|escape:'html'}</small>
          </td>
          <td data-label="{'Expires At'|@translate|escape:'html'}">
            <input type="text" class="span2" name="batch_expiry" 
                   value="{$batch_expiry|escape:'html'}" id="batch_expiry">
          </td>
          <td class="rc-form-action">
            <button type="submit" class="btn btn-bg">{'Generate'|@translate|escape:'html'}</button>
          </td>
        </tr>
        </table>
      </form>
      <div>
        <table>
          <tr>
          <th colspan="5">{'Copy codes with the same comment'|@translate|escape:'html'}</th>
      </tr>
      
      <tr>
        <td>
          <textarea class="span2" id="batch_code_copy" placeholder="{'Comment to query for copy'|@translate|escape:'html'}"></textarea>
        </td>
        <td><button type="button" class="btn btn-bg" onclick="copyCodesWithSameComment()">{'Copy'|@translate|escape:'html'}</button></td>
      </tr>
    </table>
      </div>
    </details>
  </div>
  
    {displayCodesTable table_title='Active Codes' codes_data=$register_codes enable_copy=true}
  {if $expired_codes != null}
    <details class="details">
      <summary style="font-size: 1.2em; white-space: normal;">{'Expired Codes'|@translate|escape:'html'}</summary>
      {displayCodesTable table_title="Expired Codes" codes_data=$expired_codes enable_copy=false}
    </details>
  {/if}
  <!--- Users who used the codes --->
  <div class="adminContent rc-table-scroll" tabindex="0" role="region" aria-label="{'Registration History'|@translate|escape:'html'}">
    <table id="code_table" class="table" border=1>
      <thead>
        <tr>
          <th colspan="3" class="row-one">{'Registration History'|@translate|escape:'html'}</th>
        </tr>
        <tr>
          <th>{'Code'|@translate|escape:'html'}</th>
          <th>{'Username'|@translate|escape:'html'}</th>
          <th>{'Registered At'|@translate|escape:'html'}</th>
        </tr>
      </thead>
      <tbody>
        {if empty($registration_history)}
          <tr>
            <td colspan="3">{'No registration history found'|translate|escape:'html'}</td>
          </tr>
        {else}
	  {foreach $registration_history as $k=>$v}
		<tr class="{cycle values='row-one,row-two'}">
      <td>{$registration_history[$k].code|escape:'html'}</td>
      <td>{$registration_history[$k].user_name|escape:'html'}</td>
      <td>{$registration_history[$k].created_at|escape:'html'}</td>
		</tr>
    {/foreach}
        {/if}

      </tbody>
    </table>
  </div>
</fieldset>
</div>
