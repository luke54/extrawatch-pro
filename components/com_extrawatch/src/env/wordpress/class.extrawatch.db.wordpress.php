<?php

/**
 * @file
 * ExtraWatch - A real-time ajax monitor and live stats
 * @package ExtraWatch
 * @version 2.2
 * @revision 1425
 * @license http://www.gnu.org/licenses/gpl-3.0.txt     GNU General Public License v3
 * @copyright (C) 2013 by CodeGravity.com - All rights reserved!
 * @website http://www.extrawatch.com
 */

defined('_JEXEC') or die('Restricted access');

class ExtraWatchDBWrapWordpress implements ExtraWatchDBWrap
{

  public $query;
  public $dbref;
  public $result;
  public $dbprefix;
  public $errNum;
  public $errMsg;

  function ExtraWatchDBWrapWordpress()
  {
    global $wpdb;
    $host = $wpdb->dbhost;
    $user = $wpdb->dbuser;
    $password = $wpdb->dbpassword;
    $database = $wpdb->dbname;
    $this->dbprefix = $wpdb->base_prefix;
    $select = TRUE;

    if (!($this->dbref = @mysql_connect($host, $user, $password, TRUE))) {
      die("cannot connect");
    }
    if ($select) {
      $this->select($database);
    }
  }

  function __destruct()
  {
    return @mysql_close($this->dbref);
  }

  function getEscaped($sql)
  {
    return mysql_real_escape_string($sql, $this->dbref);
  }

  function query()
  {
    $sql = $this->query;
	ExtraWatchLog::debug("query: $sql"); 
	$sql = str_replace("#__", $this->dbprefix, $sql);
    $this->result = mysql_query($sql, $this->dbref);

    if (!$this->result) {
      $this->errNum = mysql_errno($this->dbref);
      $this->errMsg = mysql_error($this->dbref) . " in query $sql";
      return FALSE;
    }
    return $this->result;
  }

  function loadResult()
  {
    if (!($result = $this->query())) {
      return null;
    }
    $return = null;
    if ($row = mysql_fetch_row($result)) {
      $return = $row[0];
    }
    mysql_free_result($result);
    return $return;
  }

  function loadAssocList($key = '')
  {
    $result = $this->query();
    $array = array();
    while ($row = mysql_fetch_assoc($result)) {
      $array[] = $row;
    }
    mysql_free_result($result);
    return $array;
  }

  function select($database)
  {
    if (!$database) {
      return FALSE;
    }
    if (!mysql_select_db($database, $this->dbref)) {
      die ('Could not connect to database');
      return FALSE;
    }
    return TRUE;
  }

  function setQuery($query)
  {
	ExtraWatchLog::debug("setQuery: $query"); 
	$this->query = $query;
  }

  function getErrorNum()
  {
    return $this->errNum;
  }

  function objectListQuery($query)
  {
    $this->query = $query;
    return $this->loadObjectList();
  }

  function getQuery()
  {
    return $this->query;
  }

  function resultQuery($query)
  {
    $this->query = $query;
    $this->setQuery($query);
    return $this->loadResult();
  }

  function executeQuery($query)
  {
    $this->query = $query;
    return $this->query();
  }

  function assocListQuery($query)
  {
    $this->query = $query;
    return $this->loadAssocList();
  }

  function replaceDbPrefix($sql)
  {
   	ExtraWatchLog::debug("$sql");  
	return str_replace("#__", $this->dbprefix, $sql);
  }

  private function loadObjectList($key = '')
  {
    if (!($cur = $this->query())) {
      return null;
    }
    $array = array();
    while ($row = mysql_fetch_object($cur)) {
      if ($key) {
        $array[$row->$key] = $row;
      } else {
        $array[] = $row;
      }
    }
    mysql_free_result($cur);
    return $array;
  }
}


