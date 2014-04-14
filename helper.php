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
        $query['no_query_clients'] = 1;
        $query['timeout'] = $params->get('connection_timeout', 10);

        $query = http_build_query($query);

        $url = 'serverquery://' . $params->get('server_login') . ':' . $params->get('server_password') . '@' . $params->get('host') . ':' . $params->get('query_port') . '/?' . $query;

        $key = md5($url);

        if (!$ts3 = $cache->get($key)) {
            $ts3 = TeamSpeak3::factory($url);
            try {
            } catch (TeamSpeak3_Exception $e) {
                return $e->getMessage() . ' (' . $e->getCode() . ')';
            }
            $cache->store($ts3, $key);
        }

        $dataKey = md5($key . $params->get('layout'));

        if (!$data = $cache->get($dataKey)) {
            switch ($params->get('layout')) {
                default:
                case 'default':
                    $images = JUri::base(true) . '/media/teamspeak3/images';

                    $data = new stdClass;
                    $data->viewer = $ts3->getViewer(new TeamSpeak3_Viewer_Html($images . '/', $images . '/flags/', 'data:image'));

                    break;

                case 'stats':
                    $data = $ts3->getInfo(true, true);

                    break;
            }
            $cache->store($data, $dataKey);
        }

        return $data;
    }
}