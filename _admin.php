<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of Matomo.
#
# Copyright (c) 2019 Pierre Van Glabeke and contributors
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------
if (!defined('DC_CONTEXT_ADMIN')) { return; }

$_menu['Blog']->addItem('Matomo','plugin.php?p=matomo','index.php?pf=matomo/icon.png',
		preg_match('/plugin.php\?p=matomo(&.*)?$/',$_SERVER['REQUEST_URI']),
		$core->auth->check('admin',$core->blog->id));
		
$core->addBehavior('adminDashboardFavorites','matomoDashboardFavorites');

function matomoDashboardFavorites($core,$favs)
{
	$favs->register('matomo', array(
		'title' => __('Matomo'),
		'url' => 'plugin.php?p=matomo',
		'small-icon' => 'index.php?pf=matomo/icon.png',
		'large-icon' => 'index.php?pf=matomo/icon-big.png',
		'permissions' => 'usage,contentadmin'
	));
}