<?php

/**
 * @file
 * ExtraWatch - A real-time ajax monitor and live stats
 * @package ExtraWatch
 * @version 2.2
 * @revision 123
 * @license http://www.gnu.org/licenses/gpl-3.0.txt     GNU General Public License v3
 * @copyright (C) 2013 by CodeGravity.com - All rights reserved!
 * @website http://www.extrawatch.com
 */

/** ensure this file is being included by a parent file */
defined('_JEXEC') or die('Restricted access');

class ExtraWatchDrupalEnv implements ExtraWatchEnv
{
  const EW_ENV_NAME = "drupal";

  function __construct() {
  }

  function getDatabase($user = "")
  {
    return new ExtraWatchDBWrapDrupal();
  }

  function getRequest()
  {
    return new EnvRequest();
  }

  function & getURI()
  {
    return "fakeURL";
  }

  function isSSL()
  {
    //TODO change
    return FALSE;
  }

  function getRootSite()
  {
    //print_r($_SERVER);
    $hostname = "http://" . $_SERVER['HTTP_HOST'];
    $scriptName = $_SERVER['SCRIPT_NAME'];
    $subdir = str_replace("/index.php", "", $scriptName);
	
	$url = parse_url($hostname . $subdir);
    $liveSitePath = $url['path'];

    return $liveSitePath. "/sites/all/modules/extrawatch/";
  }

  function getAdminDir()
  {
    return "";
  }


  function getCurrentUser()
  {
    return $this->getUsername();
  }

  function getUsersCustomTimezoneOffset()
  {
    return 0;
  }

  function getEnvironmentSuffix()
  {
    return "";
  }

  function renderLink($task, $otherParams)
  {
    return "?task=$task&action=$otherParams";
  }

  function getUser()
  {
    return "matto";
  }

  function getTitle()
  {
    return drupal_get_title();
  }

  function getUsername()
  {
    global $user;
    if ($user && $user->uid) {
      return @$user->name;
    }
    return "";
  }

  function getAdminEmail()
    {
        global $user;
        if ($user && $user->uid) {
            return @$user->email;
        }
        return "";
    }

    function sendMail($recipient, $sender, $recipient, $subject, $body, $true, $cc, $bcc, $attachment, $replyto, $replytoname)
  {
    //TODO send mail
  }

  function getDbPrefix()
  {
    $databaseArray = $GLOBALS['databases']['default']['default'];
    return $databaseArray['prefix'];
  }

  function getTimezoneOffset()
  {
    return ExtraWatchHelper::getTimezoneOffsetByTimezoneName(@date_default_timezone_get());
  }

  function getAllowedDirsToCheckForSize()
  {
    // TODO: Implement getDirsToCheckForSize() method.
  }

  function getDirsToCheckForSize($directory)
  {
    $dirs = array();

    $dirs[ExtraWatchSizes::SCAN_DIR_MAIN] = "..";
    $dirs[ExtraWatchSizes::SCAN_DIR_ADMIN] = "../administrator";

    $dirs[ExtraWatchSizes::REAL_DIR_MAIN] = "..";
    $dirs[ExtraWatchSizes::REAL_DIR_ADMIN] = "../administrator";

    return $dirs;
  }

  /**
   * env
   * @return unknown
   */
  function getAgentNotPublishedMsg($database) {
    //TODO implement
    return FALSE;
  }

  function getFormKey() {
        return "";
  }

    public function getReviewLink()
    {
        // TODO: Implement getReviewLink() method.
    }

    public function getVoteLink()
    {
        // TODO: Implement getVoteLink() method.
    }

    public function getEnvironmentName()
    {
        return self::EW_ENV_NAME;
    }

    public function getRootPath() {
        return ;
    }

    public function getTempDirectory() {
       return ini_get('upload_tmp_dir');
    }

    function getUserId()
    {
        //TODO implement
    }

    public function getUsernameById($userId) {

    }

    public function renderAjaxLink($task, $action) {
        return $action;
    }


    public function addStyleSheet($cssURL)
    {
        $output = "<style type=\"text/css\" media=\"screen, projection\">
        <!--
        @import url(" . $cssURL . ");
        -->
        </style>";
        return $output;
    }
}


