<?php

/**
 * @author     Branko Wilhelm <branko.wilhelm@gmail.com>
 * @link       http://www.z-index.net
 * @copyright  (c) 2014 Branko Wilhelm
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

if (PHP_SAPI !== 'cli') {
    die('This is a command line only application.');
}

define('_JEXEC', 1);
define('DS', DIRECTORY_SEPARATOR);

error_reporting(E_ALL | E_NOTICE);
ini_set('display_errors', 1);

// Load system defines
if (file_exists(dirname(__DIR__) . '/defines.php')) {
    require_once dirname(__DIR__) . '/defines.php';
}

if (!defined('_JDEFINES')) {
    define('JPATH_BASE', dirname(__DIR__));
    require_once JPATH_BASE . '/includes/defines.php';
}

require_once JPATH_LIBRARIES . '/import.php';
require_once JPATH_LIBRARIES . '/cms.php';

require_once JPATH_CONFIGURATION . '/configuration.php';

class Teamspeak3Cli extends JApplicationCli
{
    public function doExecute()
    {
        JLoader::register('ModTeamspeak3Helper', JPATH_BASE . '/modules/mod_teamspeak3/helper.php');

        $db = JFactory::getDbo();

        $query = $db->getQuery(true)
            ->select('m.params')
            ->select('m.title')
            ->from('#__modules AS m')
            ->where('m.module = ' . $db->quote('mod_teamspeak3'))
            ->where('m.published = ' . $db->quote(1));

        $db->setQuery($query);

        $results = $db->loadObjectList();

        if (empty($results)) {
            $this->out('no active teamspeak3 modules found');
            return;
        }

        foreach ($results as $row) {
            $module = new stdClass;
            $row->params = new JRegistry($row->params);

            $data = ModTeamspeak3Helper::getData($row->params, $module);

            if (is_string($data)) {
                $this->out($data);
            } else {
                $this->out($row->title . ' (' . $row->params->get('server_host') . ') successful');
            }
        }
    }
}

JApplicationCli::getInstance('Teamspeak3Cli')->execute();
