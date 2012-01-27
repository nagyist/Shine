<?php

require 'includes/master.inc.php';

$res = 'ERR';

$bundle_id = getParams($_REQUEST, 'BundleId', 'str');
$data['trx_id'] = getParams($_REQUEST, 'TrxId', 'str');
$data['inapp_id'] = getParams($_REQUEST, 'InappId', 'str');
$data['trx_date'] = getParams($_REQUEST, 'TrxDate', 'str');
$data['bundle_version'] = getParams($_REQUEST, 'BundleVersion', 'str');
$data['price'] = getParams($_REQUEST, 'Price', 'str');
$data['currency'] = getParams($_REQUEST, 'Currency', 'str');
$data['uuid'] = getParams($_REQUEST, 'UUID', 'str');
$sign = getParams($_REQUEST, 'Sign', 'str');

if (!empty($bundle_id) && !empty($data['trx_id']) && !empty($data['inapp_id']) && !empty($data['trx_date']) && !empty($data['bundle_version']) 
		&& isset($_REQUEST['Price']) && !empty($data['currency']) && !empty($data['uuid']) && !empty($sign)) {
	
	$app = new Application();
	$app->select($bundle_id, 'bundle_id'); # Try getting app by bundle id
	if ($app->ok()) {
		# check request signature
		if ($sign == md5($bundle_id.implode($data).$app->custom_salt)) {
			# we got here through all the checks, now add track to db
			$inapp = new Inapp();
			$inapp->trx_id = $data['trx_id'];
			$inapp->app_id = $app->id;
			$inapp->inapp_id = $data['inapp_id'];
			$inapp->trx_date = $data['trx_date'];
			$inapp->bundle_version = $data['bundle_version'];
			$inapp->price = $data['price'];
			$inapp->currency = $data['currency'];
			$inapp->uuid = $data['uuid'];
			$inapp->ip = $_SERVER['REMOTE_ADDR'];
			if (function_exists('geoip_country_name_by_name')) $inapp->country = geoip_country_name_by_name($inapp->ip);
			
			if ($inapp->insert()) $res = 'OK';
			else $res .= '[Couldn\'t save to DB]';
		}
		else $res .= '[Invalid params]';
	}
	else $res .= '[Application not found]';
}
else $res .= '[Not enough params]';

echo $res;