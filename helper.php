<?php

/**
 * @author     Branko Wilhelm <branko.wilhelm@gmail.com>
 * @link       http://www.z-index.net
 * @copyright  (c) 2014 Branko Wilhelm
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @see        http://addons.teamspeak.com/directory/addon/integration/TeamSpeak-3-PHP-Framework.html
 */

defined('_JEXEC') or die;

class ModTeamspeak3ViewerHelper
{
    public static function getData(JRegistry &$params)
    {
        if (!$params->get('host') || !$params->get('query_port') || !$params->get('udp_port') || !$params->get('server_login') || !$params->get('server_password')) {
            return false;
        }

        $cache = JFactory::getCache('ts3', 'output');
        $cache->setCaching(1);
        $cache->setLifeTime($params->get('cache_time', 5));

        $query = array();
        $query['server_port'] = $params->get('udp_port');
        $query['timeout'] = $params->get('connection_timeout', 10);
        if ($params->get('no_query_clients', 1)) {
            $query['no_query_clients'] = 1;
        }

        $query = http_build_query($query);

        $url = 'serverquery://' . $params->get('server_login') . ':' . $params->get('server_password') . '@' . $params->get('host') . ':' . $params->get('query_port') . '/?' . $query;

        $key = md5($url);

        if (!$data = $cache->get($key)) {
            try {
                $ts3 = TeamSpeak3::factory($url);
            } catch (TeamSpeak3_Exception $e) {
                return $e->getMessage() . ' (' . $e->getCode() . ')';
            }

            $images = JUri::base(true) . '/media/teamspeak3/images';

            $data = new stdClass;
            $data->viewer = $ts3->getViewer(new TeamSpeak3_Viewer_Html($images . '/', $images . '/flags/', 'data:image'));
            $data->infos = $ts3->getInfo(true, true);

            $cache->store($data, $key);
        }

        return $data;
    }

    public static function infoString($str, $type)
    {
        $str = (string)$str;

        switch ($type) {
            case 'virtualserver_created':
                return JHtml::_('date', $str, JText::_('DATE_FORMAT_LC2'));
                break;

            case 'virtualserver_flag_password':
                return ($str == 1) ? JText::_('JYES') : JText::_('JNO');
                break;

            default:
                return $str;
                break;
        }
    }
}