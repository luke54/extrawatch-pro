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

class ExtraWatchHeatmapHTML
{

    public $extraWatch;
    public $extraWatchHeatmap;
    public $stat;
    public $extraWatchStatHTML;
    public $projectSite;

    const TRUNCATE_LEN = 200;

  function __construct($database)
  {
    $this->extraWatch = new ExtraWatchMain();
    $this->extraWatchHeatmap = new ExtraWatchHeatmap($database);
    $this->visit = new ExtraWatchVisit();
    $this->extraWatchStat = new ExtraWatchStat($database);
    $this->extraWatchStatHTML = new ExtraWatchStatHTML($this->extraWatch);
    $this->projectSite = $this->extraWatch->config->getProjectUrlByUsername(_EW_PROJECT_ID);
  }

    
    function renderHeatmapTable($day = 0, $limit = 20)
    {
        $rows = $this->extraWatchHeatmap->getTopHeatmapUris($day, 20);
        if (!$rows) {
            return ExtraWatchHelper::renderNoData();
        }
        $maxClicksForDay = $this->extraWatchHeatmap->getMaxClicksForDay($day);

        $output = sprintf("<table style='border: 1px solid #dddddd; padding: 0px' class='tablesorter'>
        <thead>
        <tr><th>%s</th>
        <th>%s</th>
        <th>%s</th>",_EW_HEATMAP_CLICKS, _EW_HEATMAP_TITLE, _EW_URI);

        $output .= sprintf("<th align='center'>%s</th><th>%s</th><th>%s</th><th></th></tr>", _EW_EMAIL_REPORTS_1DAY_CHANGE, _EW_EMAIL_REPORTS_7DAY_CHANGE, _EW_EMAIL_REPORTS_28DAY_CHANGE);

        $output .= "</thead><tbody>";

        $i = 0;
        if ($rows)
            foreach ($rows as $row) {
                if (@$row->uri2titleId) {
                    $output .= $this->renderHeatmapTableRow($row, $day, $maxClicksForDay, $i);
                    $i++;
                }
            }
        $output .= "</tbody></table>";

        return $output;
    }

    function renderHeatmapTableRow($row, $day, $maxClickForDay, $i)
    {
        $uri = $this->visit->getUriNameByUri2TitleId($row->uri2titleId);
        $title = ExtraWatchHelper::truncate($this->visit->getTitleByUriId($row->uri2titleId), self::TRUNCATE_LEN);
        ExtraWatchLog::debug("renderHeatmapTableRow - uri2titleId: ".$row->uri2titleId." uri found; ".$uri. "title: ".$title);
        $ratio = $row->count / $maxClickForDay;
        $color = ExtraWatchHelper::rgbFromRatio($ratio);
        $trendsCells = $this->extraWatchStatHTML->renderDiffTableCellsAndIcon(EW_DB_KEY_HEATMAP, $row->uri2titleId, $day);

        $openAllClicksLink = "";
        if (!$day) {
            //$openAllClicksLink = $this->renderHeatmapLink($uri, $row->uri2titleId, "", "all", "", TRUE);
        }

        $output = sprintf("<tr class='tableRow" . ($i % 2) . "'><td align='center' style='color: %s' width='5%%'>%d</td><td>%s</td><td>%s</td>",
            $color,
            $row->count,
            $this->renderHeatmapLink($uri, $row->uri2titleId, $day, $title, "", TRUE),
            ExtraWatchHelper::truncate($uri, 30)
            );
        if ($day) {
         $output .= $trendsCells;
        } else {
            $output .= "<td></td><td></td><td></td><td></td>";
        }

        $output .="</tr>";
        return $output;
    }

    function renderHeatmapLatestClicksTableRow($row)
    {
        $countryCode = $this->extraWatch->helper->countryByIpCached($row->ip);

        $countryIcon = $this->extraWatchStatHTML->renderCountryFlagIcon($countryCode, $countryCode);

        return sprintf("<tr id='heatmapTableRowId_".$row->clickId."'>
        <td align='center' width='5%%'>%s</td>
        <td>%s</td>
        <td>%s</td>
        <td>%s</td>
        <td>%s</td>
        <td>%s</td>
        <td>%s</td>
        <td>%s</td>
        </tr>",
            $row->clickCount,
            $this->renderHeatmapLink($row->uri, $row->uri2titleId, $this->extraWatch->date->jwDateToday(), "<span title='".$row->title."'>".ExtraWatchHelper::truncate($row->title)."</span>", $row->ip),
            $row->uri,
            $countryIcon,
            $row->ip,
            $row->x, $row->y,
            ($row->w."x".$row->h)
        );
    }


    function renderHeatmapLink($uri, $uri2titleId, $day, $linkContent, $ip = "", $truncate = FALSE)
    {
        if ($truncate) {
            $linkContent = ExtraWatchHelper::truncate($linkContent, 100);
        }

        $separator = "?"; // if there already is a question mark in the REQUEST_URI
        if (strstr($uri, "?")) {
            $separator = "&";
        }
        $projectSite = $this->extraWatch->config->getProjectUrlByUsername(_EW_PROJECT_ID);
        return sprintf("<a href='%s". $separator . ExtraWatchHeatmap::HEATMAP_PARAM_NAME . "=%d&" . ExtraWatchHeatmap::HEATMAP_PARAM_DAY_NAME . "=%d&ip=%s&" . ExtraWatchHeatmap::HEATMAP_PARAM_HASH . "=%s&uri2titleId=%d' target='_heatmap'>%s</a>", $projectSite.$uri, 1, $day, $ip, $this->extraWatch->database->getEscaped($this->extraWatch->config->getRandHash()), $uri2titleId, $linkContent);
    }

    function renderMostClickedHTMLElementsTable($day = 0) {
        $rows = $this->extraWatchHeatmap->getMostClickedHTMLElements($day);
		$liveSite = $this->extraWatch->config->getLiveSiteWithSuffix();

        if (!@$rows) {
            return ExtraWatchHelper::renderNoData();
        }

        $output = "<table style='border: 1px solid #dddddd' class='tablesorter'>";
        $output .= sprintf("<thead><tr><th align='center'>%s</th><th align='center'>%s</th><th>%s</th><th>%s</th><th>%s</th><th>%s</th><th>%s</th><th></th></tr></thead><tbody>",
            _EW_HEATMAP_CLICKS,
            _EW_GOALS,
            "element ID",
            _EW_HEATMAP_TITLE,
            _EW_EMAIL_REPORTS_1DAY_CHANGE, _EW_EMAIL_REPORTS_7DAY_CHANGE, _EW_EMAIL_REPORTS_28DAY_CHANGE);
        $i = 0;

        if (@$rows) {
            $maxClicksForDay = $this->extraWatchHeatmap->getMaxClicksForDay($day);
            foreach($rows as $row) {
                $trendsCells = $this->extraWatchStatHTML->renderDiffTableCellsAndIcon(EW_DB_KEY_HTML_ELEMENT, $liveSite.addslashes($row->xpath), $day);

                $ratio = $row->clickCount / $maxClicksForDay;
                $color = ExtraWatchHelper::rgbFromRatio($ratio);

                $highlightElementLink = $this->renderHighlightElementLink($liveSite.$row->uri, $row->xpath);

                if ($day) {
                    $link = "<td title='".ExtraWatchHelper::htmlspecialchars($row->xpath)."'>". $highlightElementLink. "</td>";
                } else {
                    $link = "<td>".$this->renderHighlightElementLink($liveSite.$row->uri, "all", ExtraWatchHelper::htmlspecialchars(ExtraWatchHelper::truncate($row->xpath)))."</td>";
                }



                $output .= "<tr  class='tableRow" . ($i % 2) . "'>".
                    "<td align='center' style='color: ".$color."' width='5%%'>".$row->clickCount."</td><td align='center'>";

                if (@$row->clicked_element_xpath_condition) {    //render goal name instead of link
                    $output .=  "<a href='" . $this->extraWatch->config->renderLink("goals", "edit&goalId=".((int)$row->id)."") . "'>".$row->name."</a>";
                } else {
                    $output .=  "<a href='" . $this->extraWatch->config->renderLink("goals", "insert&clicked_element_xpath_condition=".urlencode($row->xpath)."") . "' title='" . _EW_STATS_ADD_TO_GOALS . "'><img src='" . $this->extraWatch->config->getLiveSiteWithSuffix() . "components/com_extrawatch/img/icons/goal-add.gif' border='0'/></a>";
                }

                $output .= "</td>".$link.
                    "<td><a href='".$liveSite.$row->uri."' title='".ExtraWatchHelper::htmlspecialchars($row->title)."' target='_blank'>".ExtraWatchHelper::truncate($row->title)."</a></td>".
                    $trendsCells.
                    "</tr>";
                $i++;
            }
        }
        $output .= "</tbody></table>";

        return $output;
    }

    public function renderHighlightElementLink($uri, $xpath, $linkName = "")
    {
        return sprintf("<a href='%s?" . ExtraWatchHeatmap::HEATMAP_PARAM_NAME . "=%d&" . ExtraWatchHeatmap::HEATMAP_PARAM_HASH . "=%s&xpath=%s' target='_heatmap' title='%s'>%s</a>", $this->projectSite . $uri, 1, $this->extraWatch->database->getEscaped($this->extraWatch->config->getRandHash()), urlencode($xpath), htmlentities($xpath), $linkName ? $linkName : ExtraWatchHelper::truncate($xpath) );
    }

    function renderLatestHeatmapClicksTable()
    {
        $rows = $this->extraWatchHeatmap->getLatestHeatmapUris(20);
        if (!$rows) {
            return ExtraWatchHelper::renderNoData();
        }
        $output = sprintf("<table style='border: 1px solid #dddddd; padding: 0px' class='tablesorter'>
        <thead>
        <tr>
        <th align='center'>%s</th>
        <th>%s</th>
        <th>%s</th>
        <th>%s</th>
        <th>%s</th>
        <th>%s</th>
        <th>%s</th>
        <th>%s</th>
        </tr>
        </thead><tbody>",
            "clicks",
            _EW_HEATMAP_TITLE,
            _EW_URI,
            "country",
            _EW_STATS_IP,
            "x",
            "y",
            "screen resolution"
        );

        if ($rows)
            foreach ($rows as $row) {
                if (@$row->uri2titleId) {
                    $output .= $this->renderHeatmapLatestClicksTableRow($row);
                }
            }
        $output .= "</tbody></table>";

        return $output;
    }


    function renderLatestHeatmapElementClicksTable()
    {
        $rows = $this->extraWatchHeatmap->getLatestHeatmapUris(20);
        if (!$rows) {
            return ExtraWatchHelper::renderNoData();
        }
        $output = sprintf("<table style='border: 1px solid #dddddd; padding: 0px' class='tablesorter'>
        <thead>
        <tr>
        <th align='center'>%s</th>
        <th align='center'>%s</th>
        <th>%s</th>
        <th>%s</th>
        <th>%s</th>
        <th>%s</th>
        <th>%s</th>
        <th>%s</th>
        <th>%s</th>
        </tr>
        </thead><tbody>",
            "clicks",
            _EW_GOALS,
            _EW_HEATMAP_TITLE,
            _EW_URI,
            "country",
            _EW_STATS_IP,
            "x",
            "y",
            "screen resolution"
        );

        if ($rows)
            foreach ($rows as $row) {
                if (@$row->uri2titleId) {
                    $output .= $this->renderHeatmapLatestElementClicksTableRow($row);
                }
            }
        $output .= "</tbody></table>";

        return $output;
    }


    function renderHeatmapLatestElementClicksTableRow($row)
    {
        $countryCode = $this->extraWatch->helper->countryByIpCached($row->ip);

        $countryIcon = $this->extraWatchStatHTML->renderCountryFlagIcon($countryCode, $countryCode);

        if (@$row->clicked_element_xpath_condition) {    //render goal name instead of link
            $goalLink =  "<a href='" . $this->extraWatch->config->renderLink("goals", "edit&goalId=".((int)$row->id)."") . "'>".$row->name."</a>";
        } else {
            $goalLink =  "<a href='" . $this->extraWatch->config->renderLink("goals", "insert&clicked_element_xpath_condition=".urlencode($row->xpath)."") . "' title='" . _EW_STATS_ADD_TO_GOALS . "'><img src='" . $this->extraWatch->config->getLiveSiteWithSuffix() . "components/com_extrawatch/img/icons/goal-add.gif' border='0'/></a>";
        }


        return sprintf("<tr id='heatmapTableRowId_".$row->clickId."'>
        <td align='center' width='5%%'>%s</td>
        <td align='center'>%s</td>
        <td>%s</td>
        <td>%s</td>
        <td>%s</td>
        <td>%s</td>
        <td>%s</td>
        <td>%s</td>
        <td>%s</td>
        </tr>",
            $row->clickCount,
            $goalLink,
            $this->renderHighlightElementLink($row->uri, $row->xpath, "<span title='".$row->xpath."'>".ExtraWatchHelper::truncate($row->xpath)."</span>"),
            $row->uri,
            $countryIcon,
            $row->ip,
            $row->x, $row->y,
            ($row->w."x".$row->h)
        );
    }



    
}
