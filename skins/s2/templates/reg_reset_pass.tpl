{literal}
<script type='text/javascript'>

    function validate_form(form)
    {
        if ( ! form.new_pass.value || ! form.new_pass_b.value )
        {
            alert('{/literal}{$lang['pass_short']}{literal}');
            form.new_pass.focus();
            return false;
        }
    }

</script>
{/literal}
<div class='content_block'>
    <form action='{$td_url}/index.php?page=register&amp;code=doreset&amp;key={$input['key']}' method='post' onsubmit='return validate_form(this)'>
    {$token_reset_pass}
    <h2>{$lang['reset_password']}</h2>
    {if $error}
    <div id='smallerror'>
        <p>{$error}</p>
    </div>
    {/if}
    <table width='100%' cellpadding='3' cellspacing='1'>
    <tr>
        <td class='row1' width='28%'><label for='new_pass'>{$lang['new_password']}</label></td>
        <td width='72%'><input type='password' name='new_pass' id='new_pass' size='30' /></td>
    </tr>
    <tr>
        <td class='row1'><label for='new_pass_b'>{$lang['new_password_confirm']}</label></td>
        <td><input type='password' name='new_pass_b' id='new_pass_b' size='30' /></td>
    </tr>
    <tr>
        <td colspan='2' class='padtop'><input type='submit' class='submit' name='submit' id='change' value='{$lang['reset_pass_button']}' /></td>
    </tr>
    </table>
    </form>
</div>