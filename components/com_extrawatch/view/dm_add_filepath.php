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


<form action="" method="post" name="addfilepathform" onsubmit="return check_add_filepath();">
    <table width="43%" border="0" cellpadding="3" cellspacing="0" class="table" style="width: 50%">
        <tr>
            <td colspan="2" class="Subtitle">
                <h3><?php echo(_EW_DOWNLOADS_ADD_FILE_PATH);?></h3> </td>
        </tr>
        <tr>
            <td height="23" colspan="3" align="left" style='color: red'><?php echo(_EW_DOWNLOADS_WARNING);?><br/><br/></td>
        </tr>
        <tr>
            <td width="31%">
                <div align="right"><?php echo(_EW_DOWNLOADS_FILE_PATH_NAME);?><font color="#FF0000"> *</font>                    </div>                  </td>
            <td width="29%" align="left">
                <input name="filepathname" type="text" size="50" value="">                  </td>
            <td height="20" colspan="2">
                <div align="left">
                    <input name="task" type="hidden"  value="downloads">
                    <input name="action" type="hidden"  value="saveAddFile">
                    <input name="Submit" type="submit" class="button" value="Add">
                    <input name="Submit" type="button" class="button" value="Back" onclick="window.location.href='<?php echo $extraWatch->config->renderLink("downloads","");?>'">
                </div>
            </td>
        </tr>
        <tr>
            <td>
                <div align="right"><?php echo(_EW_DOWNLOADS_ALLOW_ONLY_REFERRER);?>                    </div>                  </td>
            </td>
            <td width="39%" align="left">
                <input name="allowedReferrer" type="text" size="50" value="<?php echo $addAllowedReferer?>">
            </td>
        </tr>
    </table>
    <br>
</form>

