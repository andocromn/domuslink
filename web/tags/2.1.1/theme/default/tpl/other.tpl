<div id="module">
<table cellspacing="0" cellpadding="0" border="0" class="module" height="37px">

<tr>
<td width="17" valign="top">
<img src="<?php echo $config['url_path'].'/theme/'.$config['theme'].'/images/module_other_'.$state.'.png'; ?>" vspace="2" />
</td>

<td width="110px" rowspan="2" valign="center"><input type="text" disabled="disabled" value="<?php echo $label; ?>" class="module_<?php echo $state; ?>"  /></td>

<td rowspan="2">
<table cellspacing="1" cellpadding="0" border="1" class="onofftb" width="32px">
<tr>
<td class="onoff" align="center"><a href="<?php echo $_SERVER['PHP_SELF']; ?>?action=on&code=<?php echo $code; ?>&page=<?php echo $page; ?>"><?php echo $lang['ON']; ?></a></td>
</tr>
<tr>
<td class="onoff" align="center"><a href="<?php echo $_SERVER['PHP_SELF']; ?>?action=off&code=<?php echo $code; ?>&page=<?php echo $page; ?>"><?php echo $lang['OFF']; ?></a></td>
</tr>
</table></td>
</tr>
<tr>
<?php if ($config['codes'] == "ON"): ?><td colspan="2" align="left"><span class="code"><?php echo $code; ?></span></td>
<?php else: ?>
<td align="left"></td>
<td></td>
<?php endif; ?>
</tr>

</table>
</div>
