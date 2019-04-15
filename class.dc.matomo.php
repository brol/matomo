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
if (!defined('DC_RC_PATH')) {return;}

class dcMatomo extends netHttp
{
	protected $api_path;
	protected $api_token;
	
	public function __construct($uri)
	{
		self::parseServiceURI($uri,$base,$token);
		
		if (!self::readURL($base,$ssl,$host,$port,$path,$user,$pass)) {
			throw new Exception(__('Unable to read Matomo URI.'));
		}
		
		parent::__construct($host,$port,10);
		$this->useSSL($ssl);
		$this->setAuthorization($user,$pass);
		$this->api_path = $path;
		$this->api_token = $token;
	}
	
	public function siteExists($id)
	{
		try
		{
			$sites = $this->getSitesWithAdminAccess();
			foreach ($sites as $v) {
				if ($v['idsite'] == $id) {
					return true;
				}
			}
		}
		catch (Exception $e) {}
		return false;
	}
	
	public function getSitesWithAdminAccess()
	{
		$get = $this->methodCall('SitesManager.getSitesWithAdminAccess');
		$this->get($get['path'],$get['data']);
		$rsp = $this->readResponse();
		$res = array();
		foreach ($rsp as $v) {
			$res[$v['idsite']] = $v;
		}
		return $res;
	}
	
	public function addSite($name,$url)
	{
		$data = array(
			'siteName' => $name,
			'urls' => $url
		);
		$get = $this->methodCall('SitesManager.addSite',$data);
		$this->get($get['path'],$get['data']);
		return $this->readResponse();
	}
	
	protected function methodCall($method,$data=array())
	{
		$data['token_auth'] = $this->api_token;
		$data['module'] = 'API';
		$data['format'] = 'php';
		$data['method'] = $method;
		
		return array('path' => $this->api_path, 'data' => $data);
	}
	
	protected function readResponse()
	{
		$res = $this->getContent();
		$res = @unserialize($res);
		
		if ($res === false) {
			throw new Exception(__('Invalid Matomo Response.'));
		}
		
		if (is_array($res) && !empty($res['result']) && $res['result'] == 'error') {
			$this->matomoError($res['message']);
		}
		return $res;
	}
	
	protected function matomoError($msg)
	{
		throw new Exception(sprintf(__('Matomo returned an error: %s'),strip_tags($msg)));
	}
	
	public static function getServiceURI(&$base,$token)
	{
		if (!preg_match('/^[a-f0-9]{32}$/i',$token)) {
			throw new Exception('Invalid Matomo Token.');
		}
		
		$base = preg_replace('/\?(.*)$/','',$base);
		if (!preg_match('/index\.php$/',$base)) {
			if (!preg_match('/\/$/',$base)) {
				$base .= '/';
			}
			$base .= 'index.php';
		}
		
		return $base.'?token_auth='.$token;
	}
	
	public static function parseServiceURI(&$uri,&$base,&$token)
	{
		$err = new Exception(__('Invalid Service URI.'));
		
		$p = parse_url($uri);
		$p = array_merge(array('scheme'=>'','host'=>'','user'=>'','pass'=>'','path'=>'','query'=>'','fragment'=> ''),
			$p);
		
		if ($p['scheme'] != 'http' && $p['scheme'] != 'https') {
			throw $err;
		}
		
		if (empty($p['query'])) {
			throw $err;
		}
		
		parse_str($p['query'],$query);
		if (empty($query['token_auth'])) {
			throw $err;
		}
		
		$base = $uri;
		$token = $query['token_auth'];
		$uri = self::getServiceURI($base,$token);
	}
	
	public static function getScriptCode($uri,$idsite,$action='')
	{
		$a=parse_url($uri);
		$hostname=$a['host'];
		
		return
		"\n".'<script type="text/javascript">'."\n".
		'var _paq = window._paq || [];'."\n".
		"_paq.push(['setDocumentTitle', document.domain + '/' + document.title]);\n".
		"_paq.push(['trackPageView']);\n".
		"_paq.push(['enableLinkTracking']);\n".
		"(function() {\n".
    'var u="//'.$hostname.'/";'."\n".
    "_paq.push(['setTrackerUrl', u+'matomo.php']);\n".
    "_paq.push(['setSiteId', '".$idsite."']);\n".
    "var d=document, g=d.createElement('script'), s=d.getElementsByTagName('script')[0];\n".
    "g.type='text/javascript'; g.async=true; g.defer=true; g.src=u+'matomo.js'; s.parentNode.insertBefore(g,s);\n".
		"})();\n".
		"</script>\n".
		"<noscript><p><img src='//$hostname/matomo.php?idsite=$idsite&amp;rec=1' alt='' /></p></noscript>\n".
		"<!-- End Matomo Code -->\n";
		}
}