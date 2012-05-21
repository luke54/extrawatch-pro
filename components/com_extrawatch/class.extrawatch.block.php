<?php

/**
 * @file
 * ExtraWatch - A real-time ajax monitor and live stats
 * @package ExtraWatch
 * @version 1.2.18
 * @revision 150
 * @license http://www.gnu.org/licenses/gpl-3.0.txt     GNU General Public License v3
 * @copyright (C) 2012 by Matej Koval - All rights reserved!
 * @website http://www.codegravity.com
 */

/** ensure this file is being included by a parent file */
if (!defined('_JEXEC') && !defined('_VALID_MOS'))
    die('Restricted access');

class ExtraWatchBlock
{

    public $database;
    public $config;
    public $helper;
    public $date;
    public $BLOCKING_REASON = "The IP: %s is blocked due to attempt to access back-end without security code";

    function __construct($database)
    {
        $this->database = $database;
        $this->config = new ExtraWatchConfig($this->database);
        $this->helper = new ExtraWatchHelper($this->database);
        $this->date = new ExtraWatchDate($this->database);
    }

    /**
     * block
     */
    function blockIp($ip, $reason = "", $date = 0, $spamWord)
    {
        $ip = htmlentities(strip_tags($ip));
        $query = sprintf("INSERT into #__extrawatch_blocked (id, ip, hits, `date`, reason, country, badWord) values ('','%s','','%d', '%s', null, '%s')", $this->database->getEscaped($ip), (int)$date, $this->database->getEscaped($reason), $this->database->getEscaped($spamWord));
        $this->database->executeQuery($query);
    }

    /**
     * block
     */
    function unblockIp($ip)
    {
        $query = sprintf("delete from #__extrawatch_blocked where ip = '%s'", $this->database->getEscaped($ip));
        $this->database->executeQuery($query);
    }

    /**
     * block
     */
    function extraWatchBlockIpToggle($ip)
    {

        $count = $this->getBlockedIp($ip);
        $today = $this->date->jwDateToday();

        if (!$count) {
            $this->blockIp($ip, "", $today, _EW_BLOCKING_BLOCKED_MANUALLY);
        } else {
            $this->unblockIp($ip);
        }

    }

    /**
     * block
     */
    function searchBlockedIp($ip)
    {
        $query = sprintf("select count(ip) as count from #__extrawatch_blocked where ip = '%s' limit 1", $this->database->getEscaped($ip)); //starting % ommited
        $count = $this->database->resultQuery($query);
        return $count;
    }

    /**
     * Check whether the IP is blocked
     * @param  $ip
     * @return bool
     */
    function isBlockedIp($ip)
    {
        if ($this->searchBlockedIp($ip) > 0) {
            return TRUE;
        }
        return FALSE;
    }

    /**
     * block
     */
    function searchBlockedIpWildcard($term)
    {
        $query = sprintf("select count(ip) as count from #__extrawatch_blocked where ip like '%s%%' limit 1", $this->database->getEscaped($term)); //starting % ommited
        $count = $this->database->resultQuery($query);
        return $count;
    }

    /**
     * block
     */
    function getBlockedIp($ip)
    {

        if ($this->searchBlockedIp($ip)) {
            return $ip;
        } else {
            if (strstr($ip, '.')) { //is IPv4
                $ipExploded = explode('.', $ip);

                $ip = $ipExploded[0] . "." . $ipExploded[1] . "." . $ipExploded[2] . ".*";
                if ($this->searchBlockedIpWildcard($ip)) {
                    return $ip;
                } else {
                    $ip = $ipExploded[0] . "." . $ipExploded[1] . ".*";
                    if ($this->searchBlockedIpWildcard($ip)) {
                        return $ip;
                    } else {
                        $ip = $ipExploded[0] . ".*";
                        if ($this->searchBlockedIpWildcard($ip))
                            return $ip;
                    }

                }
            } else { //IPv6
                if ($this->searchBlockedIpWildcard($ip))
                    return $ip;
            }

        }

        return "";

    }


    /**
     * block
     */
    function increaseHitsForBlockedIp($ip)
    {

        $ip = $this->getBlockedIp($ip);
        $query = sprintf("select hits from #__extrawatch_blocked where ip = '%s' ", $this->database->getEscaped($ip));
        $hits = $this->database->resultQuery($query);

        $hits++;
        if ($hits) { //update
            $query = sprintf("update #__extrawatch_blocked set hits = '%d' where ip = '%s'", (int)$hits, $this->database->getEscaped($ip));
            $this->database->executeQuery($query);
        }
    }


    /**
     * block
     */
    function getBlockedIPs($date = 0, $limitCount = 0)
    {

        if (@$limitCount) {
            $limit = " limit " . (int)$limitCount;
        } else {
            $limit = "";
        }

        if (@$date != 0) {
            $query = sprintf("select ip,hits,reason from #__extrawatch_blocked where `date` = '%d' order by hits desc %s", (int)$date, $this->database->getEscaped($limit));
        } else {
            $query = sprintf("select * from #__extrawatch_blocked order by `date` desc %s", $this->database->getEscaped($limit));
        }
        return $rows = $this->database->objectListQuery($query);
    }

    /**
     * block
     */
    function countBlockedIPs($date = 0)
    {
        if (@$date != 0) {
            $query = sprintf("select count(id) as count from #__extrawatch_blocked where `date` = '%d' order by hits desc ", (int)$date);
            return $this->database->resultQuery($query);
        } else {
            $query = sprintf("select count(id) as count from #__extrawatch_blocked order by hits desc ", (int)$date);
            return $this->database->resultQuery($query);
        }
    }

    /**
     * blocking
     *
     * @return unknown
     */
    function dieWithBlockingMessage($ip)
    {
        $this->increaseHitsForBlockedIp($ip);
        die($this->config->getConfigValue('EXTRAWATCH_BLOCKING_MESSAGE'));
    }


    /**
     * block
     */
    function getBlockedCountByDate($date = 0)
    {
        if (@$date != 0) {
            $query = sprintf("select sum(hits) as count from #__extrawatch_blocked where `date` = '%d'", (int)$date);
            return $this->database->resultQuery($query);
        }
    }

    /**
     * block
     */
    function getBlockedCountTotal()
    {
        $query = sprintf("select sum(hits) as count from #__extrawatch_blocked limit 1");
        return $this->database->resultQuery($query);
    }


    function checkPostRequestForSpam($post)
    {

        /** if nothing is there in the post request */
        if (@!$post) {
            return TRUE;
        }
        $ip = $_SERVER['REMOTE_ADDR'];

        if (@$this->searchBlockedIp($ip)) {
            $this->dieWithBlockingMessage($ip);
        }
        $today = $this->date->jwDateToday();

        if (@ $this->config->getCheckboxValue('EXTRAWATCH_SPAMWORD_BANS_ENABLED')) {

            $spamList = explode("\n", $this->config->getConfigValue('EXTRAWATCH_SPAMWORD_LIST'));
            foreach ($post as $key => $value) {

                foreach ($spamList as $spamWord) {
                    $spamWord = trim($spamWord);
                    if (is_array($value)) {
                        foreach ($value as $valueNested) {
                            $this->blockSpamWord($valueNested, $spamWord, $ip, $today);
                        }
                    } else {
                        $this->blockSpamWord($value, $spamWord, $ip, $today);
                    }
                }
            }

        }

    }

    function checkForSpamWord($spamWordToCheck)
    {
        $spamList = explode("\n", $this->config->getConfigValue('EXTRAWATCH_SPAMWORD_LIST'));
        foreach ($spamList as $spamWord) {
            $spamWord = trim($spamWord);
            if ($spamWordToCheck && $spamWord && ExtraWatchHelper :: wildcardSearch("*" . $spamWord . "*", $spamWordToCheck)) {
                return $spamWord;
            }
        }
        return;
    }

    function blockSpamWord($value, $spamWord, $ip, $today)
    {
        $value = trim($value);
        if (@ $spamWord && @$value && ExtraWatchHelper :: wildcardSearch("*" . $spamWord . "*", $value)) {
            $this->blockIp($ip, htmlspecialchars($value), $today, $spamWord);
            $this->dieWithBlockingMessage($ip);
        }

    }

    function checkBlocked($ip)
    {
        if ($this->isBlockedIp($ip)) {
            return sprintf($this->BLOCKING_REASON, $ip);
        }
    }

    function checkPermissions()
    {
        $ip = @$_SERVER['REMOTE_ADDR'];
        $reason = sprintf($this->BLOCKING_REASON, $ip);
        if ($this->checkBlocked($ip)) {
            die($reason);
        }
        if (!$this->config->isPermitted()) {
            $this->blockIp($ip, $reason, $this->date->jwDateToday(), _EW_BLOCKING_UNAUTHORIZED_ACCESS);
            die($reason);
        }
    }

    /**
     * block
     */
    function updateCountryForBlockedIp($ip, $countryCode)
    {
        $query = sprintf("update #__extrawatch_blocked set country = '%s' where ip = '%s'", $this->database->getEscaped($countryCode), $this->database->getEscaped($ip));
        $this->database->executeQuery($query);
    }

    /**
     * block
     */
    function updateSpamWordForBlockedIp($ip, $spamWord)
    {
        $query = sprintf("update #__extrawatch_blocked set badWord = '%s' where ip = '%s'", $this->database->getEscaped($spamWord), $this->database->getEscaped($ip));
        $this->database->executeQuery($query);
    }


}


