<?php
/*
 * domus.Link :: Web-based frontend for Heyu
 * Copyright 2007, Istvan Hubay Cebrian
 * All Rights Reserved
 * http://domus.link.co.pt
 *
 * This software is licensed free of charge for non-commercial distribution
 * and for personal and internal business use only.  Inclusion of this
 * software or any part thereof in a commercial product is prohibited.
 *
 */

require_once('..'.DIRECTORY_SEPARATOR.'include.php');

## Set template parameters
$tpl->set('title', $lang['login']);

if ($_POST)
{
	if ($_POST['txtPassword'] != $config['password'])
	{
		$authenticated = false;
	}
	else
	{
		$authenticated = true;
	}
}
else
{
	$authenticated = false;
}

if ($authenticated)
{
	setcookie("dluloged", "admin", 0, $config['url_path']);
	if ($_POST['from'] != "")
		if ($_POST['from'] == "index")
			header("Location: ".$config['url_path']."/".$_POST['from'].".php");
		else
			header("Location: ".$_POST['from'].".php");
	else
		header("Location: setup.php");
}
else
{
	$tpl_body = & new Template(TPL_FILE_LOCATION.'login.tpl');
	$tpl_body->set('lang', $lang);
}


## Display the page
if (!empty($tpl_body))
{
	$tpl->set('content', $tpl_body);
}

echo $tpl->fetch(TPL_FILE_LOCATION.'layout.tpl');

?>