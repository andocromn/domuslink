<div id="module">
<table cellspacing="0" cellpadding="0" border="0" class="module" height="37px">
<tr>
<td width="17" valign="top"><img src="<?php echo $config['url_path']; ?>/theme/<?php echo $config['theme']; ?>/images/module_multi.png" /></td>
<td width="110px"><input type="text" value="<?php echo $label; ?>" class="module"  /></td>
<td width="33px">

<table cellspacing="1" cellpadding="0" border="1" class="onofftb" width="30px">
<tr>
<td class="onoff" align="center"><a href="<?php echo $_SERVER['PHP_SELF']; ?>?action=on&code=<?php echo $code; ?>&page=<?php echo $page; ?>">ON</a></td>
</tr>
<tr>
<td class="onoff" align="center"><a href="<?php echo $_SERVER['PHP_SELF']; ?>?action=off&code=<?php echo $code; ?>&page=<?php echo $page; ?>">OFF</a></td>
</tr>
</table>

</td>
</tr>
</table>
</div>
