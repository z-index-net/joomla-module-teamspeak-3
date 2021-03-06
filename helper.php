<?php

/**
 * @author     Branko Wilhelm <branko.wilhelm@gmail.com>
 * @link       http://www.z-index.net
 * @copyright  (c) 2014 Branko Wilhelm
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die;

JLoader::register('TeamSpeak3', JPATH_LIBRARIES . '/TeamSpeak3/TeamSpeak3.php');

TeamSpeak3::init();

JLoader::import('joomla.filesystem.folder');
JLoader::import('joomla.filesystem.file');

class ModTeamspeak3Helper
{
    public static function getData(JRegistry $params, stdClass $module)
    {
        if (!$params->get('server_host') || !$params->get('server_port') || !$params->get('query_port') || !$params->get('query_login') || !$params->get('query_password')) {
            return JText::_('MOD_TEAMSPEAK3_BASIC_CONFIGURATION_MISSING');
        }

        $cache = JFactory::getCache('teamspeak3', 'output');
        $cache->setCaching(1);
        $cache->setLifeTime($params->get('cache_time', 5));

        $query = array();
        $query['server_port'] = $params->get('server_port');
        $query['timeout'] = $params->get('connection_timeout', 10);
        if ($params->get('no_query_clients', 1)) {
            $query['no_query_clients'] = 1;
        }

        $query = http_build_query($query);

        $url = 'serverquery://' . $params->get('query_login') . ':' . $params->get('query_password') . '@' . $params->get('server_host') . ':' . $params->get('query_port') . '/?' . $query;

        $key = md5($url);

        if (!$data = $cache->get($key)) {
            try {
                $ts3 = TeamSpeak3::factory($url);

                $html = new TeamSpeak3_Viewer_Html_Joomla($params);

                $html->loadCacheIcons();

                $data = new stdClass;
                $data->infos = $ts3->getInfo(true, true);
                $data->infos['caching_timestamp'] = JFactory::getDate('now', 'UTC')->toSql();

                if ($params->get('channel_id')) {
                    try {
                        $channel = $ts3->channelGetById($params->get('channel_id'));
                    } catch (TeamSpeak3_Exception $e) {
                        return $e->getMessage() . ' (' . $e->getCode() . ')';
                    }

                    $data->viewer = $channel->getViewer($html);
                } else {
                    $data->viewer = $ts3->getViewer($html);
                }

                $html->storeCacheIcons();

                $data->title = $html->getModuleTitle();

                $cache->store($data, $key);

            } catch (TeamSpeak3_Exception $e) {
                return $e->getMessage() . ' (' . $e->getCode() . ')';
            }
        }

        if ($params->get('module_title')) {
            $module->title = $data->title;
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

            case 'caching_timestamp':
                $from_time = JFactory::getDate($str, 'UTC')->toUnix();
                $to_time = JFactory::getDate('now', 'UTC')->toUnix();

                return JHtml::_('date', $str, 'H:i:s') . JText::sprintf('MOD_TEAMSPEAK3_INFOS_CACHING_TIMESTAMP_AGO', ($to_time - $from_time));
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

class TeamSpeak3_Viewer_Html_Joomla extends TeamSpeak3_Viewer_Html
{
    protected $params;

    protected $title;

    protected $linkPrefix;

    protected $images = '/media/mod_teamspeak3/images/';

    public function __construct(JRegistry $params)
    {
        $this->params = $params;

        if ($this->params->get('join_links')) {
            $this->linkPrefix = 'ts3server://' . $this->params->get('server_host') . '?port=' . $this->params->get('server_port');

            if ($this->params->get('join_password')) {
                $this->linkPrefix .= '&amp;password=' . $this->params->get('join_password');
            }

            if ($this->params->get('join_nickname')) {
                $this->linkPrefix .= '&amp;nickname=' . rawurlencode($this->params->get('join_nickname'));
            }
        }

        parent::__construct(JUri::root(false) . $this->images, JUri::root(false) . $this->images . 'flags/', 'data:image');
    }

    public function getModuleTitle()
    {
        return $this->title;
    }

    public function fetchObject(TeamSpeak3_Node_Abstract $node, array $siblings = array())
    {
        $obj = parent::fetchObject($node, $siblings);

        if ($this->hideNode()) {
            return '';
        }

        if ($this->params->get('module_title') && $this->currObj instanceof TeamSpeak3_Node_Server) {
            $this->title = $obj;
            return '';
        }

        return $obj;
    }

    private function hideNode()
    {
        if ($this->currObj instanceof TeamSpeak3_Node_Channel) {
            if ($this->params->get('channel_hide')) {
                $channel_hide = explode(',', $this->params->get('channel_hide'));
                JArrayHelper::toInteger($channel_hide);

                return in_array($this->currObj->getId(), $channel_hide);
            }
        }

        if ($this->currObj instanceof TeamSpeak3_Node_Client) {
            if ($this->params->get('client_hide') == -1) {
                return true;
            }

            if ($this->params->get('client_hide')) {
                $client_hide = explode(',', $this->params->get('client_hide'));
                JArrayHelper::toInteger($client_hide);

                return in_array($this->currObj->getId(), $client_hide);
            }
        }

        return false;
    }

    protected function getCorpusTitle()
    {
        $title = parent::getCorpusTitle();

        if ($this->currObj instanceof TeamSpeak3_Node_Channel) {
            $topic = $this->currObj->getProperty('channel_topic');

            if (!empty($topic)) {
                $title = 'Topic: ' . $topic . ' | ' . $title;
            }
        }

        if ($this->currObj instanceof TeamSpeak3_Node_CLient && $this->params->get('avatars')) {
            $title .= $this->getClientAvatar();
        }

        $title = JString::str_ireplace('|', '<br/>', $title);

        return htmlspecialchars($title);
    }

    protected function getCorpusName()
    {
        $name = parent::getCorpusName();

        if ($this->params->get('join_links') == 'all') {
            if ($this->currObj instanceof TeamSpeak3_Node_Channel && !$this->currObj->isSpacer()) {
                $name = JHtml::_('link', $this->linkPrefix . '&amp;channel=' . rawurlencode((string)$this->currObj->getPathway()), $name, array('class' => 'join'));
            }
        }

        if ($this->params->get('join_links') == 'server' || $this->params->get('join_links') == 'all') {
            if ($this->currObj instanceof TeamSpeak3_Node_Server) {
                $name = JHtml::_('link', $this->linkPrefix, $name, array('class' => 'join'));
            }
        }

        return $name;
    }

    protected function getClientAvatar()
    {
        if ($this->currObj['client_flag_avatar'] == 0) return;

        $avatar_name = $this->currObj['client_base64HashClientUID'] . '.jpg';

        $path = JPATH_ROOT . $this->images . 'avatars/';

        $this->deleteExpiredAvatars($path);

        if (!JFile::exists($path . $avatar_name)) {
            JFile::write($path . $avatar_name, $this->currObj->avatarDownload());
        }

        if (JFile::exists($path . $avatar_name)) {
            return JHtml::_('image', JUri::root(false) . $this->images . 'avatars/' . $avatar_name, '', array('class' => 'avatar'));
        }

        return '';
    }

    protected function deleteExpiredAvatars($path)
    {
        $files = JFolder::files($path);

        if (empty($files)) {
            return;
        }

        $expired = time() - (60 * 60 * (int)$this->params->get('avatar_expired', 48));

        foreach ($files as $file) {
            if (filemtime($path . $file) <= $expired) {
                JFile::delete($path . $file);
            }
        }
    }

    public function loadCacheIcons()
    {
        $path = JPATH_ROOT . $this->images . 'icons/';

        $icons = JFolder::files($path);

        foreach ($icons as $icon) {
            $this->cacheIcon[$icon] = new TeamSpeak3_Helper_String(JFile::read($path . $icon));
        }
    }

    public function storeCacheIcons()
    {
        $path = JPATH_ROOT . $this->images . 'icons/';

        foreach ($this->cacheIcon as $name => $icon) {
            if (!JFile::exists($path . $name)) {
                $content = (string)$icon;
                JFile::write($path . $name, $content);
            }
        }
    }
}