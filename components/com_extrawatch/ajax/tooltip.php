<?php

/**
 * @file
 * ExtraWatch - A real-time ajax monitor and live stats
 * @package ExtraWatch
 * @version 1.2.18
 * @revision 211
 * @license http://www.gnu.org/licenses/gpl-3.0.txt     GNU General Public License v3
 * @copyright (C) 2012 by Matej Koval - All rights reserved!
 * @website http://www.codegravity.com
 */

define('_JEXEC', 1);
define('DS', DIRECTORY_SEPARATOR);
$jBasePath = realpath(dirname(__FILE__) . DS . ".." . DS . ".." . DS . "..". DS);
define('JPATH_BASE2', $jBasePath);

sleep(1); // it's a tooltip. Let's sleep for a while

include_once JPATH_BASE2 . DS . "components" . DS . "com_extrawatch" . DS. "includes.php";

$extraWatch = new ExtraWatch();
require_once (JPATH_BASE2 . DS . "components" . DS . "com_extrawatch" . DS. "lang" . DS . $extraWatch->config->getLanguage() . ".php");

$extraWatchHTML = new ExtraWatchHTML();
$extraWatch->block->checkPermissions();

echo $extraWatchHTML->renderCloseWindow();
require_once JPATH_BASE2 . DS . "components" . DS . "com_extrawatch" . DS . "view" . DS . "trendtooltip.php";


