<?php

/**
 * @author     Branko Wilhelm <branko.wilhelm@gmail.com>
 * @link       http://www.z-index.net
 * @copyright  (c) 2014 Branko Wilhelm
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die;

JLoader::register('TeamSpeak3', dirname(__FILE__) . '/library/TeamSpeak3/TeamSpeak3.php');

JLoader::register('ModTeamspeak3ViewerHelper', dirname(__FILE__) . '/helper.php');

$ts3_VirtualServer = ModTeamspeak3ViewerHelper::getData($params);

require JModuleHelper::getLayoutPath($module->module, $params->get('layout', 'default'));