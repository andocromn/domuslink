<?php
/*
 * domus.Link :: PHP Web-based frontend for Heyu (X10 Home Automation)
 * Copyright (c) 2007, Istvan Hubay Cebrian (istvan.cebrian@domus.link.co.pt)
 * Project's homepage: http://domus.link.co.pt
 * Project's dev. homepage: http://domuslink.googlecode.com
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope's that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details. You should have 
 * received a copy of the GNU General Public License along with
 * this program; if not, write to the Free Software Foundation, 
 * Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

## Includes
require_once('..'.DIRECTORY_SEPARATOR.'include.php');
require_once(CLASS_FILE_LOCATION.'heyuconf.class.php');
require_once(CLASS_FILE_LOCATION.'heyusched.class.php');
require_once(CLASS_FILE_LOCATION.'timer.class.php');

## Security validation's
if ($config['seclevel'] != "0" && !$authenticated) {
	header("Location: ../login.php?from=events/timers");
	exit();
}

## Instantiate heyuConf class and get schedule file with absolute path
$heyuconf = new heyuConf($config['heyuconfloc']);
$schedfileloc = $config['heyu_base_real'].$heyuconf->getSchedFile();

## Load aliases and parse so that only code and labels remain
$aliases = $heyuconf->getAliases();
$codelabels = $heyuconf->getCodesAndLabels($aliases);

## Instantiate heyuSched class, get contents and parse timers
$heyusched = new heyuSched($schedfileloc);
$schedObjs = $heyusched->getObjects();
$macros = $heyusched->getMacroObjects();
$timers = $heyusched->getTimerObjects();
$triggers = $heyusched->getTriggerObjects();

## Set-up arrays
$months = array (1 => $lang["jan"], $lang["feb"], $lang["mar"], $lang["apr"], $lang["may"], $lang["jun"], $lang["jul"], $lang["aug"], $lang["sep"], $lang["oct"], $lang["nov"], $lang["dec"]);
$wdayo = array("s","m","t","w","t","f","s");
$days = range(1,31);
$mins = range(0,59);
$hours = range(00,23);

## Set template parameters
$tpl->set('title', $lang['timers']);

$tpl_body = & new Template(TPL_FILE_LOCATION.'timers_view.tpl');
$tpl_body->set('lang', $lang);
$tpl_body->set('timers', $timers);
$tpl_body->set('config', $config);

if (!isset($_GET["action"])) {
	$tpl_add = & new Template(TPL_FILE_LOCATION.'timer_add.tpl');
	$tpl_add->set('lang', $lang);
	$tpl_add->set('codelabels', $codelabels);
	$tpl_add->set('months', $months);
	$tpl_add->set('days', $days);
	$tpl_add->set('hours', $hours);
	$tpl_add->set('mins', $mins);
	$tpl_body->set('form', $tpl_add);
}
else {
	switch ($_GET["action"]) {
		case "enable":
			$sm = get_specific_macros($macros, $_GET['onm'], $_GET['ofm']); 
			change_macro_states($sm, "enable");
			$schedObjs[$_GET['line']]->setEnabled(true);
			save_file($schedObjs, $schedfileloc);
			break;
			
		case "disable":
			//if no timer or trigger in use that uses on/off macros OR
			//disabled timer or trigger exists that uses on/off macro
			$mtim = multiple_macro_use($schedObjs, $_GET['onm'], $_GET['ofm'], $_GET['line']);
			if ($mtim == 0) {
				//get individual macros (get_specific_macros)
				//then change their states (change_macro_states) outputing complete file to $newschedfile
				//finally disable timer sending as input new schedfile
				$sm = get_specific_macros($macros, $_GET['onm'], $_GET['ofm']);
				change_macro_states($sm, "disable");				
			}

			$schedObjs[$_GET['line']]->setEnabled(false);
			save_file($schedObjs, $schedfileloc);
			break;
		
		case "edit":
			$timerObj = $schedObjs[$_GET['line']];
			
			$tpl_edit = & new Template(TPL_FILE_LOCATION.'timer_edit.tpl');
			$tpl_edit->set('lang', $lang);
			$tpl_edit->set('codelabels', $codelabels);
			$tpl_edit->set('months', $months);
			$tpl_edit->set('days', $days);
			$tpl_edit->set('hours', $hours);
			$tpl_edit->set('mins', $mins);
			
			$tpl_edit->set('theTimer', $timerObj);
			
			if($timerObj->getStartMacro() != "null")
				$tpl_edit->set('selcode', strip_code($timerObj->getStartMacro()));
			elseif($offmacro != "null")
				$tpl_edit->set('selcode', strip_code($timerObj->getStopMacro()));

			$tpl_body->set('form', $tpl_edit);
			break;
		
		case "add":
			//build timer line with POST results
			$aTimer = new Timer();
			post_data_to_timer($aTimer);

			// new timer objects already set macros to default of null
			// only need to set macros if they are specific
			if(!isset($_POST["null_macro_on"]))
				$aTimer->setStartMacro(strtolower($_POST["module"])."_on");
			if(!isset($_POST["null_macro_off"]))
				$aTimer->setStopMacro(strtolower($_POST["module"])."_off");

			if($_POST["status"] == "#")
				$aTimer->setEnabled(false);

			$aTimer->rebuildElementLine();

			// if on/off macros exist then make sure they are enabled and add new timer
			// else create macro lines, add them to file and finally add new timer
			$i = 1;
			$sm = get_specific_macros($macros, $aTimer->getStartMacro(), $aTimer->getStopMacro());
			if ($sm) {
				change_macro_states($sm, "enable");
			}
			else {
				if( $aTimer->getStartMacro() != "null") {
					$onMacroObj = new ScheduleElement("macro ".$aTimer->getStartMacro()." 0 on ".strtolower($_POST["module"]));
					array_splice($schedObjs,$heyusched->getLine(MACRO_D, END_D) + 1, 0, array($onMacroObj));
					$heyusched->setLine(MACRO_D, $heyusched->getLine(MACRO_D, END_D) + 1, END_D);
					$i++;
				}
				if( $aTimer->getStopMacro() != "null") {
					$offMacroObj = new ScheduleElement("macro ".$aTimer->getStopMacro()." 0 off ".strtolower($_POST["module"]));
					array_splice($schedObjs,$heyusched->getLine(MACRO_D, END_D) + 1, 0, array($offMacroObj));
					$heyusched->setLine(MACRO_D, $heyusched->getLine(MACRO_D, END_D) + 1, END_D);
					$i++;
				}
			}

			array_splice($schedObjs,$heyusched->getLine(TIMER_D, END_D)+ $i, 0, array($aTimer));
			$heyusched->setLine(TIMER_D, $heyusched->getLine(TIMER_D, END_D) + $i, END_D);

			save_file($schedObjs, $schedfileloc);
			break;
			
		case "save":
			//build timer line with POST results
			post_data_to_timer($schedObjs[$_POST["line"]]);
			$schedObjs[$_POST["line"]]->rebuildElementLine();
			save_file($schedObjs, $schedfileloc);
			break;

		case "del":
			//check if any other timer or trigger (enabled or disabled) is using macros
			//	if no  - delete timer and assiociated macros
			//	if yes - only delete timer
			if (multiple_macro_use($schedObjs, $_GET['onm'], $_GET['ofm'], $_GET['line']) == 0) {
				//delete timer and associated macros
				$smas = get_specific_macros($macros, $_GET['onm'], $_GET['ofm']);
				foreach ($smas as $num => $macroObj) {
						array_splice($schedObjs, $macroObj->getLineNum()-$num, 1); //deletes macros
				}
				delete_line($schedObjs, $schedfileloc, $_GET["line"]-count($smas)); //deletes timer
			}
			else {
				//only delete timer since other timer(s) are using macros
				delete_line($schedObjs, $schedfileloc, $_GET["line"]); //deletes timer
			}
			break;
		
		case "move":
			if ($_GET["dir"] == "up") reorder_array($schedObjs, $_GET['line'], $_GET['line']-1, $schedfileloc);
			if ($_GET["dir"] == "down") reorder_array($schedObjs, $_GET['line'], $_GET['line']+1, $schedfileloc);
			break;
			
	}
}

## Display the page
$tpl->set('content', $tpl_body);

echo $tpl->fetch(TPL_FILE_LOCATION.'layout.tpl');

?>