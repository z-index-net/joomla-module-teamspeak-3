<?php

/**
 * @author     Branko Wilhelm <branko.wilhelm@gmail.com>
 * @link       http://www.z-index.net
 * @copyright  (c) 2014 Branko Wilhelm
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die;

$infos = $params->get('infos');
?>
<div class="mod_teamspeak3 infos">
    <?php if (!empty($infos)) : ?>
        <dl>
            <?php foreach ($infos as $info) : ?>
                <dt><?php echo JText::_('MOD_TEAMSPEAK3_INFOS_' . strtoupper($info)); ?></dt>
                <dd><?php echo ModTeamspeak3ViewerHelper::infoString($data->infos[$info], $info); ?></dd>
            <?php endforeach; ?>
        </dl>
    <?php else: ?>
        <p><?php echo JText::_('MOD_TEAMSPEAK3_INFOS_NOTHING_SELECTED'); ?></p>
    <?php endif; ?>
</div>