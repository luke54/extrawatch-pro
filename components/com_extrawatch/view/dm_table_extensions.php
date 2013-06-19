<?php
/**
 * @file
 * ExtraWatch - A real-time ajax monitor and live stats
 * @package ExtraWatch
 * @version 2.1
 * @revision 767
 * @license http://www.gnu.org/licenses/gpl-3.0.txt     GNU General Public License v3
 * @copyright (C) 2013 by CodeGravity.com - All rights reserved!
 * @website http://www.codegravity.com
 */
defined('_JEXEC') or die('Restricted access'); ?>


<table width="95%" border="0" align="center" cellpadding="3" cellspacing="0" class="">
    <tr>
        <td class="table" height="23" width="73%" align="left"><?php echo(_EW_DOWNLOADS_EXTENSION);?></td>
        <td colspan="2" class="table"><div align="center"><?php echo(_EW_DOWNLOADS_ACTION);?></div></td>
    </tr>
    <?php
    if (@$extensionar)
        foreach($extensionar as $extension)
        {

            ?>
            <tr>
                <td class="table" height="23" width="73%" align="left"><?php echo $extension->extname;?></td>
                <td width="9%" class="table" height="23"><div align="center"><a href="<?php echo $extraWatch->config->renderLink("downloads","editExtension&eid=".$extension->eid);?>"><img src="<?php echo $extraWatch->config->getLiveSiteWithSuffix();?>components/com_extrawatch/img/icons/edit.jpg" /></a></div></td>
                <td width="11%" class="table" height="23"><div align="center"><a href="javascript:confirmChoice('<?php echo $extension->eid?>')"><img src="<?php echo $extraWatch->config->getLiveSiteWithSuffix();?>components/com_extrawatch/img/icons/delete.jpg" /></a></div></td>
            </tr>

            <?php
        }
    ?>
</table>
