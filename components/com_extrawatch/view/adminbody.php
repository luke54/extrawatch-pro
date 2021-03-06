<?php

/**
 * @file
 * ExtraWatch - A real-time ajax monitor and live stats
 * @package ExtraWatch
 * @version 2.2
 * @revision 1425
 * @license http://www.gnu.org/licenses/gpl-3.0.txt     GNU General Public License v3
 * @copyright (C) 2013 by CodeGravity.com - All rights reserved!
 * @website http://www.codegravity.com
 */

defined('_JEXEC') or die('Restricted access'); ?>


<script type="text/javascript"
        src="<?php echo $extraWatch->config->getLiveSiteWithSuffix(); ?>components/com_extrawatch/js/jxml.js"></script>
<script type="text/javascript"
        src="<?php echo $extraWatch->config->getLiveSiteWithSuffix(); ?>components/com_extrawatch/js/jdownloadurl.js"></script>

<?php if ($extraWatch->config->getConfigValue("EXTRAWATCH_MAP_GOOGLEMAP")) { ?>
<script type="text/javascript" src="http://maps.google.com/maps/api/js?sensor=false"></script>
<?php } ?>

<script type="text/javascript"
        src="<?php echo $extraWatch->config->getLiveSiteWithSuffix(); ?><?php echo $extraWatch->env->renderAjaxLink('js','maps.js');?>&rand=<?php echo $extraWatch->config->getRand();?>&env=<?php echo ($extraWatch->config->getEnvironment());?>&projectId=<?php echo(_EW_PROJECT_ID); ?>"></script>

<?php
if (!$extraWatch->config->checkLiveSite()) {
  ?>
<div style="border: 1px solid red; width:40%; padding: 10px;">
  <?php echo sprintf("" . _EW_ADMINBODY_LIVE_SITE . "", "<b>" . ExtraWatchEnvFactory::getEnvironment()->getRootSite() . "</b>", "<b>" . $extraWatch->config->getLiveSiteWithSuffix() . "</b>"); ?>
  <br/><br/>
  <a href="<?php echo ExtraWatchEnvFactory::getEnvironment()->renderLink("resetLiveSite","&rand=".$extraWatch->config->getRand());?>"><?php echo sprintf("" . _EW_ADMINBODY_SET_LIVE_SITE . "", ExtraWatchEnvFactory::getEnvironment()->getRootSite()); ?></a>
</div>
<?php
} ?>

<?php echo $extraWatch->block->checkBlocked(ExtraWatchVisit::getRemoteIPAddress()); ?>

<center>
  <table border='0' cellpadding='2' width='100%' <?php echo $extraWatch->helper->getTooltipOnEventHide(); ?> >
      <tr>
      <td rowspan="2" id="visits" valign='top' align='left' width='80%'>
          <table cellpadding='2' cellspacing='0' width='100%' border='0'>
              <span style='color: black;'>&nbsp;<?php echo (ExtraWatchDate::getActualDateTime()); ?></span>
              <br/>
              <?php echo($extraWatchBlockHTML->renderInfoPanel(TRUE)); ?>
              <br/><br/>
          <?php
            echo $extraWatchVisitHTML->getVisitorsCached(FALSE);
            echo $extraWatchVisitHTML->getVisitorsCached(TRUE);
          ?>
        </table>

      </td>
      <td valign="top">
        <?php require_once "lastvisitmap.php";?>
      </td>
    </tr>
    <tr>
      <td id="stats" valign='top' align='left'>
        <?php echo _EW_STATS_PANE_LOADING; ?>
      </td>
    </tr>
  </table>
</center>

<?php
if (!$extraWatch->config->isFree()) {
  if ($extraWatch->config->getConfigValue("EXTRAWATCH_IPINFODB_KEY")) {
    ?>
  <?php if ($extraWatch->config->getConfigValue("EXTRAWATCH_MAP_OPENMAP")) { ?>
    <script
        src="<?php echo $extraWatch->config->getLiveSiteWithSuffix();?>components/com_extrawatch/js/OpenLayers/OpenLayers.js"></script>
    <?php } ?>

  <script type='text/javascript'>
      <?php if ($extraWatch->config->getConfigValue("EXTRAWATCH_MAP_GOOGLEMAP")) { ?>
    extraWatchGoogleMapLoad();
      <?php } ?>

    <?php 
	if ($extraWatch->config->getConfigValue("EXTRAWATCH_MAP_OPENMAP")) { ?>
    try {
      extraWatchOpenMapLoad();
    } catch (e) {
      alert("Could not initialize ExtraWatch javascript functions, browser could not load: <?php echo $extraWatch->config->getLiveSite(); ?><?php echo $extraWatch->env->renderAjaxLink('js','maps.js');?>&rand=<?php echo $extraWatch->config->getRand();?>, because it returned HTTP 500 internal server error. This is a misconfiguration of your directory permissions. Please read: http://support.lunarpages.com/knowledge_bases/article/324 or search for: PHP permissions 500 Internal server error");
    }
      <?php } ?>
  </script>

  <?php
  }
} ?>


<script type="text/javascript">extrawatch_sendLastIdReq();</script>

