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
        $cache->setLifeTime($params->get('ts3cachetime', 60));

        $query = array();
        $query['server_port'] = $params->get('udp_port');
        $query['no_query_clients'] = 1;
        $query['timeout'] = $params->get('connection_timeout', 10);

        $query = http_build_query($query);

        $url = 'serverquery://' . $params->get('server_login') . ':' . $params->get('server_password') . '@' . $params->get('host') . ':' . $params->get('query_port') . '/?' . $query;

        $key = md5($url);

        if (!$ts3_VirtualServer = $cache->get($key)) {
            $ts3_VirtualServer = TeamSpeak3::factory($url);
            //$cache->store($ts3_VirtualServer, $key); // TODO
        }

        return $ts3_VirtualServer;
    }
}