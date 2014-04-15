<?php

/**
 * @author     Branko Wilhelm <branko.wilhelm@gmail.com>
 * @link       http://www.z-index.net
 * @copyright  (c) 2014 Branko Wilhelm
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die;

if ($params->get('tooltip', 1)) {
    JHtml::_('behavior.tooltip', '.mod_teamspeak3_viewer .corpus');
}

JFactory::getDocument()->addStyleSheet(JUri::base(true) . '/media/teamspeak3/css/viewer.css');
JFactory::getDocument()->addScript(JUri::base(true) . '/media/teamspeak3/js/viewer.js');
?>
    <div class="mod_teamspeak3_viewer viewer">
        <?php echo $data->viewer; ?>
    </div>
<?php
if ($params->get('viewer_infos')) :
    require JModuleHelper::getLayoutPath($module->module, 'infos');
endif; ?>