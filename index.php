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
$page_title = __('Matomo configuration');

$core->blog->settings->addNameSpace('matomo');
$matomo_service_uri = $core->blog->settings->matomo->matomo_service_uri;
$matomo_site = $core->blog->settings->matomo->matomo_site;
$matomo_ips = $core->blog->settings->matomo->matomo_ips;
$matomo_fancy = $core->blog->settings->matomo->matomo_fancy;

$site_url = preg_replace('/\?$/','',$core->blog->url);
$site_name = $core->blog->name;

try {
	dcMatomo::parseServiceURI($matomo_service_uri,$matomo_uri,$matomo_token);
} catch (Exception $e) {}

if (isset($_POST['matomo_uri']) && isset($_POST['matomo_token']))
{
	try
	{
		$matomo_uri = $_POST['matomo_uri'];
		$matomo_token = $_POST['matomo_token'];
		
		if ($matomo_uri && $matomo_token) {
			$matomo_service_uri = dcMatomo::getServiceURI($matomo_uri,$matomo_token);
			new dcMatomo($matomo_service_uri);
		} else {
			$matomo_service_uri = '';
		}
		
		# Dotclear matomo setting
		$core->blog->settings->addNameSpace('matomo');
		$core->blog->settings->matomo->put('matomo_service_uri',$matomo_service_uri);
		
		# More stuff to set
		if ($matomo_uri && isset($_POST['matomo_site']))
		{
			$matomo_site = $_POST['matomo_site'];
			$matomo_ips = $_POST['matomo_ips'];
			$matomo_fancy = $_POST['matomo_fancy'];
			
			if ($matomo_site != '') {
				$o = new dcMatomo($matomo_service_uri);
				if (!$o->siteExists($matomo_site)) {
					throw new Exception(__('Matomo site does not exist.'));
    }
			}
			$core->blog->settings->matomo->put('matomo_site',$matomo_site);
			$core->blog->settings->matomo->put('matomo_ips',$matomo_ips);
			$core->blog->settings->matomo->put('matomo_fancy',$matomo_fancy,'boolean');
			$core->blog->triggerBlog();
		}
		
		http::redirect($p_url.'&upd=1');
	}
	catch (Exception $e)
	{
		$core->error->add($e->getMessage());
	}
}

if ($matomo_uri)
{
	$sites_combo = array(__('Disable Matomo') => '');
	try
	{
		$o = new dcMatomo($matomo_service_uri);
		
		# Create a new site
		if (!empty($_POST['site_name']) && !empty($_POST['site_url']))
		{
			$o->addSite($_POST['site_name'],$_POST['site_url']);
			http::redirect($p_url.'&created=1');
		}
		
		# Get sites list
		$matomo_sites = $o->getSitesWithAdminAccess();
		
		if (count($matomo_sites) < 1) {
			throw new Exception(__('No Matomo sites configured.'));
  }
		
		foreach ($matomo_sites as $k => $v) {
			$sites_combo[html::escapeHTML($k.' - '.$v['name'])] = $k;
		}
		
		if ($matomo_site && !isset($matomo_sites[$matomo_site])) {
			$matomo_site = '';
		}
	}
	catch (Exception $e)
	{
		$core->error->add($e->getMessage());
	}
}
?>
<html>
<head>
  <title><?php echo $page_title; ?></title>
</head>

<body><?php
	echo dcPage::breadcrumb(
		array(
			html::escapeHTML($core->blog->name) => '',
			'<span class="page-title">'.$page_title.'</span>' => ''
		));
if (!empty($_GET['upd'])) {
  dcPage::success(__('Configuration successfully updated.'));
}
if (!empty($_GET['created'])) {
  dcPage::success(__('Successfully created site.'));
}

echo
'<form action="'.$p_url.'" method="post">'.
'<div class="fieldset"><h4>'.__('Your Matomo configuration').'</h4>'.
'<p class="field"><label>'.__('Your Matomo URL:').' '.
form::field('matomo_uri',40,255,html::escapeHTML($matomo_uri)).'</label></p>'.
'<p class="field"><label>'.__('Your Matomo Token:').' '.
form::field('matomo_token',40,255,html::escapeHTML($matomo_token)).'</label></p>';

if (!$matomo_uri)
{
	echo '<p class="warn">'.__('Your Matomo installation is not configured yet.').'</p>';
}
else
{
	echo
	'<p class="field"><label>'.__('Matomo website to track:').' '.
	form::combo('matomo_site',$sites_combo,$matomo_site).'</label></p>'.
	'<p class="field"><label>'.__('Use fancy page names:').' '.
	form::checkbox('matomo_fancy',1,$matomo_fancy).'</label></p>'.
	'<p class="field"><label for="matomo_ips">'.__('Do not track following IP addresses:').'</label> '.
	form::field('matomo_ips',50,600,$matomo_ips).'</p>'.
	'<p>'.sprintf(__('Your current IP address is: %s'),'<strong>'.http::realIP().'</strong>').'</p>';
}

echo
'<p><input type="submit" value="'.__('Save').'" />'.
$core->formNonce().
'</p>';

if ($matomo_site && $matomo_uri) {
	echo '<p><strong><a href="'.$matomo_uri.'">'.
	sprintf(__('View "%s" statistics'),html::escapeHTML($matomo_sites[$matomo_site]['name'])).'</a></strong></p>';
}

echo '</div></form>';

if ($matomo_uri)
{
	echo
	'<form action="'.$p_url.'" method="post">'.
	'<div class="fieldset"><h4>'.__('Create a new Matomo site for this blog').'</h4>'.
	'<p class="field"><label>'.__('Site name:').' '.
	form::field('site_name',40,255,$site_name).'</label></p>'.
	'<p class="field"><label>'.__('Site URL:').' '.
	form::field('site_url',40,255,$site_url).'</label></p>'.
	'<p><input type="submit" value="'.__('Create site').'" />'.
	$core->formNonce().
	'</div></form>';
}

dcPage::helpBlock('matomo');
?>
</body>
</html>