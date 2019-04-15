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

$core->addBehavior('publicHeadContent',		array('matomoPublic','publicHeadContent'));
class matomoPublic
{
	public static function publicHeadContent($core,$_ctx)
	{
		$matomo_service_uri = $core->blog->settings->matomo->matomo_service_uri;
		$matomo_site = $core->blog->settings->matomo->matomo_site;
		$matomo_ips = $core->blog->settings->matomo->matomo_ips;
		
		if (!$matomo_service_uri || !$matomo_site) {
			return;
		}
		
		$matomo_ips = array_flip(preg_split('/(\s*[;,]\s*|\s+)/',trim($matomo_ips),-1,PREG_SPLIT_NO_EMPTY));
		
		if (isset($matomo_ips[http::realIP()])) {
			return;
		}
		
		$action = $_SERVER['URL_REQUEST_PART'];
		if ($core->blog->settings->matomo->matomo_fancy) {
			$action = $action == '' ? 'home' : str_replace('/',' : ',$action);
		}
		
		# Check for 404 response
		$h = headers_list();
		foreach ($h as $v) {
			if (preg_match('/^status: 404/i',$v)) {
				$action = '404 Not Found/'.$action;
			}
		}
		
		echo dcMatomo::getScriptCode($matomo_service_uri,$matomo_site,$action);
	}
}
