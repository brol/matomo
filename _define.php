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
if (!defined('DC_RC_PATH')) { return; }

$this->registerModule(
	/* Name */			"Matomo",
	/* Description*/		"Matomo statistics integration",
	/* Author */			"Pierre Van Glabeke",
	/* Version */			'0.1',
	/* Properties */
	array(
		'permissions' => 'admin',
		'type' => 'plugin',
		'dc_min' => '2.14',
		'support' => 'https://forum.dotclear.org/viewforum.php?id=16',
		'details' => 'https://github.com/brol/matomo'
		)
);