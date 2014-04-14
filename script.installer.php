<?php

/**
 * @author     Branko Wilhelm <branko.wilhelm@gmail.com>
 * @link       http://www.z-index.net
 * @copyright  (c) 2014 Branko Wilhelm
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die;

class mod_teamspeak3_viewerInstallerScript
{
    public function preflight()
    {
        if (!function_exists('stream_socket_client')) {
            JFactory::getApplication()->enqueueMessage(__CLASS__ . ': network functions are not available in this PHP installation', 'error');
            return false;
        }

        return true;
    }

    public function install(JAdapterInstance $adapter)
    {
        $lib = $adapter->getParent()->getPath('source') . '/library/';

        $installer = new JInstaller;
        $installer->install($lib);
    }

    public function update(JAdapterInstance $adapter)
    {
        $this->install($adapter);
    }
}