{html_style}
:root {
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
tbody tr:hover {
background-color: var(--hover-color) !important;
}
#code_table .row-one {
background-color: var(--row-odd-bg);
}
#code_table .row-two {
background-color: var(--row-even-bg);
}
.btn-bg {
background-color: var(--btn-bg);
color: var(--btn-color);
}

th, td {
border: 1px solid var(--border-color);
}
tbody tr:nth-child(even):not(.row1):not(.row2) {
background-color: var(--row-even-bg);
}

.details {
  border: 1px solid var(--border-color);
  border-radius: 5px;
}
{/html_style}
{function name=displayCodesTable table_title='' codes_data=null enable_copy=true}
<div {if $enable_copy}class="active_codes_table"{/if} id="code_table">
  <table border=1>
    <tr>
      <th colspan="8" class="row-one">{$table_title|@translate}</th>
    </tr>
    <tr>
      <th>{'ID'|@translate}</th>
      <th>{'Code'|@translate}</th>
      <th>{'Comment'|@translate}</th>
      <th>{'Number Of Uses'|@translate}</th>
      <th>{'Times Used'|@translate}</th>
      <th>{'Expires At'|@translate}</th>
      <th>{'Created At'|@translate}</th>
      <th></th>
    </tr>

    {foreach from=$codes_data item=data}
      <form method='post'>
        <tr class="{cycle values='row-one,row-two'}">
          <td>
            <input class="column-short" name="id" value="{$data.id}" id="id" readonly />
          </td>
          <td>
            {if $enable_copy}
              <button type="button" class="btn pluginActionLevel1 btn-copy" onclick="copyCode('{$data.code}')">{'Copy'|@translate}
                Code</button>
            {/if}
            <input name="code" value="{$data.code}" id="code" readonly />
          </td>
          <td>
            {if !empty($data.comment)}<textarea class="span2" name="comment"
              id="comment">{$data.comment}</textarea>{else}-
              {/if}
          </td>
          <td>
            <input name="uses" class="column-medium" value="{if $data.uses == '0'}{'Unlimited'|@translate}{else}{$data.uses}{/if}" id="uses"
              readonly />
          </td>
          <td>
            <input class="column-short" name="used" value="{$data.used}" id="used" readonly />
          </td>
          <td>
            <input class="column-medium" name="expiry" value="{if isset($data.expiry)}{$data.expiry}{else}-{/if}" id="expiry" readonly />
          </td>
          <td>
            <input class="column-medium" name="created_at" value="{$data.created_at}" id="created_at" readonly />
          </td>
          <td>
            <button class="btn btn-red" type="submit">{'Delete'|@translate}</button>
          </td>
        </tr>
      </form>
    {/foreach}
  </table>
  </div>
{/function}
<div class="titlePage">
  <h2>{'Register Codes Plugin'|@translate}</h2>
</div>
<link rel="stylesheet" href="/plugins/piwigo-register-codes/css/foundation-datepicker.css">
<script src="/plugins/piwigo-register-codes/js/foundation-datepicker.js"></script>
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
        document.documentElement.style.setProperty('--table-header-bg', '#333333');
        document.documentElement.style.setProperty('--table-header-color', '#ffffff');
        document.documentElement.style.setProperty('--row-odd-bg', '#333333');
        document.documentElement.style.setProperty('--row-even-bg', '#3d3d3d');
        document.documentElement.style.setProperty('--border-color', '#666');
        document.documentElement.style.setProperty('--hover-color', '#4a3c2e');
        document.documentElement.style.setProperty('--text-color', '#ffffff');
        document.documentElement.style.setProperty('--btn-bg', '#333333');
        document.documentElement.style.setProperty('--btn-color', '#ffffff');
      }
    }

    applyThemeStyles();
  });

  function generateCode() {
    var code = Math.random().toString(36).substring(2, 15) + Math.random().toString(36).substring(2, 15);
    $('#register_code').val(code);
  }

  function copyCode(code) {
    if (navigator.clipboard && navigator.clipboard.writeText) {
      navigator.clipboard.writeText(code).then(
        () => {
          console.log("Code copied to clipboard!");
        },
        (err) => {
          console.error("Failed to copy code: ", err);
        }
      );
    } else {
      const textarea = document.createElement("textarea");
      textarea.value = code;
      document.body.appendChild(textarea);
      textarea.select();
      try {
        document.execCommand("copy");
        console.log("Code copied to clipboard!");
      } catch (err) {
        console.error("Fallback: Failed to copy code: ", err);
      }
      document.body.removeChild(textarea);
    }
  }

  function copyCodesWithSameComment() {
    const searchComment = document.querySelector('#batch-code textarea#batch_code_copy').value.trim();
    if (!searchComment) {
      alert("{'Please enter a comment to search for'|@translate}");
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
      alert("{'No codes found with the matching comment'|@translate}");
      return;
    }
    
    const codesText = matchingCodes.join('\n');
    
    if (navigator.clipboard && navigator.clipboard.writeText) {
      navigator.clipboard.writeText(codesText).then(
        () => {
            alert(`Copied ` + matchingCodes.length + ` codes to clipboard!`);
        },
        (err) => {
          console.error("{'Failed to copy codes'|@translate}: ", err);
          alert("{'Failed to copy codes to clipboard'|@translate}");
        }
      );
    } else {
      const textarea = document.createElement("textarea");
      textarea.value = codesText;
      document.body.appendChild(textarea);
      textarea.select();
      try {
        document.execCommand("copy");
        alert(`Copied ` + matchingCodes.length + ` codes to clipboard!`);
      } catch (err) {
        console.error("{'Failed to copy codes'|@translate}: ", err);
        alert("{'Failed to copy codes to clipboard'|@translate}");
      }
      document.body.removeChild(textarea);
    }
  }
</script>
{combine_css path="plugins/piwigo-register-codes/css/admin.css" order=1}
<fieldset>
  <div id="new-code">
    <legend>{'Register Codes Description'|@translate}</legend>
    <table border=1>
      <form method="post">
        <tr>
          <th colspan="5">{'Add New Code'|@translate}</th>
        </tr>
        <tr>
          <th>{'Code'|@translate}</th>
          <th>{'Comment'|@translate}</th>
          <th>{'Number Of Uses'|@translate}<br>({'0 for unlimited'|@translate})</th>
          <th>{'Expires At'|@translate}</th>
          <th></th>
        </tr>
        <tr>
          <!-- <td><p><textarea style="border: none;" class="span2" name="register_code" placeholder="Example Code" id="register_code"></textarea></p></td> -->
          <td><button type="button" class="btn btn-bg" onclick="generateCode()">Generate Code</button>
            <p><input type="textarea" class="span2" name="register_code" placeholder="Example Code"
                id="register_code"></p>
          </td>
          <td>
            <p><textarea class="span2" name="register_comment" placeholder="Optional Comment"
                id="register_comment"></textarea></p>
          </td>
          <td>
            <p>
              <center><input type="number" id="uses" name="uses"
                  value="{if isset($reg_codes_uses_default) && $reg_codes_uses_default != ''}{$reg_codes_uses_default}{else}1{/if}"
                  min="0"></center>
            </p>
          </td>
          <td>
            <p><input type="text" class="span2" name="register_expiry"
                value="{date("Y-m-d H:i:00", strtotime("+1 week", strtotime("now")))}" id="register_expiry"></p>
          </td>
          <td><button type="submit" class="btn btn-bg">Add</button></td>
        </tr>
      </form>
    </table>
  </div>
  <div id="batch-code">
    <details class="details">
      <summary style="font-size: 1.2em; text-wrap: nowrap;">{'Batch Code Generator'|@translate}</summary>
      <form method="post">
          <table border=1 class="table-margin">
          <tr>
          <th>{'Number of Codes'|@translate}</th>
          <th>{'Comment'|@translate}</th>
          <th>{'Number Of Uses'|@translate}<br>({'0 for unlimited'|@translate})</th>
          <th>{'Expires At'|@translate}</th>
          <th></th>
        </tr>
        <tr>
          <td>
            <input type="number" name="batch_count" id="batch_count" value="10" min="1" max="100">
          </td>
          <td>
            <textarea class="span2" name="batch_comment" placeholder="Optional Comment" id="batch_comment"></textarea>
          </td>
          <td>
            <center><input type="number" id="batch_uses" name="batch_uses" 
                   value="{if isset($reg_codes_uses_default) && $reg_codes_uses_default != ''}{$reg_codes_uses_default}{else}1{/if}" 
                   min="0"></center>
          </td>
          <td>
            <input type="text" class="span2" name="batch_expiry" 
                   value="{date("Y-m-d H:i:00", strtotime("+1 week", strtotime("now")))}" id="batch_expiry">
          </td>
          <td>
            <button type="submit" class="btn btn-bg">Generate</button>
          </td>
        </tr>
        </table>
      </form>
      <form method="post">
        <table>
          <tr>
          <th colspan="5">{'Copy codes with the same comment'|@translate}</th>
      </tr>
      
      <tr>
        <td>
          <textarea class="span2" id="batch_code_copy" placeholder="{'Comment to query for copy'|@translate}"></textarea>
        </td>
        <td><button type="button" class="btn btn-bg" onclick="copyCodesWithSameComment()">{'Copy'|@translate}</button></td>
      </tr>
    </table>
    </details>
  </div>
  
    {displayCodesTable table_title="Active Codes" codes_data=$register_codes enable_copy=true}
  {if $expired_codes != null}
    <details class="details">
      <summary style="font-size: 1.2em; text-wrap: nowrap;">{'Expired Codes'|@translate}</summary>
      {displayCodesTable table_title="Expired Codes" codes_data=$expired_codes enable_copy=false}
    </details>
  {/if}
  <!--- Users who used the codes --->
  <div class="adminContent">
    <table id="code_table" class="table" border=1>
      <thead>
        <tr>
          <th colspan="5" class="row-one">{'Registration History'|@translate}</th>
        </tr>
        <tr>
          <th>{'Code'|@translate}</th>
          <th>{'Username'|@translate}</th>
          <th>{'Registered At'|@translate}</th>
        </tr>
      </thead>
      <tbody>
        {if empty($registration_history)}
          <tr>
            <td colspan="3">{'No registration history found'|translate}</td>
          </tr>
        {else}
	  {foreach $registration_history as $k=>$v}
		<tr class="{cycle values='row-one,row-two'}">
      <td>{$registration_history[$k].code}</td>
      <td>{$registration_history[$k].user_name}</td>
      <td>{$registration_history[$k].created_at}</td>
		</tr>
    {/foreach}
        {/if}

      </tbody>
    </table>
  </div>
</fieldset>
