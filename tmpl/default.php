<?php

/**
 * @author     Branko Wilhelm <branko.wilhelm@gmail.com>
 * @link       http://www.z-index.net
 * @copyright  (c) 2014 Branko Wilhelm
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die;

JFactory::getDocument()->addStyleSheet(JUri::base(true) . '/modules/' . $module->module . '/tmpl/default.css');

$images = JUri::base(true) . '/media/teamspeak3/images';
?>
<div class="mod_teamspeak3_viewer">
    <?php echo $ts3->getViewer(new TeamSpeak3_Viewer_Html($images . '/', $images . '/flags/', 'data:image')); ?>
</div>