<?php
if ( ! defined( 'RNCBC_PLUGIN_DIR' ) ) {
	exit; // Exit if accessed directly
}

function rncbc_import($file_name) {
	require_once RNCBC_PLUGIN_DIR.'/libs/'.$file_name.'.php';
}

function rncbc_render($file_name, $var = array()) {
	extract($var);
	require_once RNCBC_PLUGIN_DIR.'/views/'.$file_name.'.php';
}


function rncbc_setting() {
	return array(
		'rncbc_email2customer' => get_option('rncbc_email2customer', 1),
		'rncbc_email_customer_subject' => get_option('rncbc_email_customer_subject', 'Thanks for your booking'),
		'rncbc_email_customer_body' => get_option('rncbc_email_customer_body', 'We have received your booking, the information you send is:<br /> [booking_information]'),

		'rncbc_email2admin' => get_option('rncbc_email2admin', 0),
		'rncbc_email_admin_address' => get_option('rncbc_email_admin_address'),
		'rncbc_email_admin_subject' => get_option('rncbc_email_admin_subject'),
		'rncbc_email_admin_body' => get_option('rncbc_email_admin_body'),


		'rncbc_license_key' => get_option('rncbc_license_key'),
	);
}

function rncbc_default_setting() {
	return array(
		'rncbc_email_customer_subject' => 'Thanks for your booking',
		'rncbc_email_customer_body' => 'We have received your booking, the information you send is:<br /> [booking_information]',

		'rncbc_email_admin_subject' => 'There is a new booking',
		'rncbc_email_admin_body' => 'There is a new booking, the information is:<br /> [booking_information]',
	);
}


function rncbc_load_yui() {
	$yui_js = is_ssl() ? 'https://yui-s.yahooapis.com/3.18.1/build/yui/yui-min.js' : 'http://yui.yahooapis.com/3.18.1/build/yui/yui-min.js';
	wp_enqueue_script('yui', $yui_js);
}


function rncbc_admin_redirect($uri) {
	echo '<script>document.write("<p>Success! redirecting...</p>");window.location.href="'.admin_url('admin.php?').''.$uri.'"</script>';
}


function rncbc_send_mail($calendar_title, $date, $info) {
	$setting = rncbc_setting();
	$is_pro = rncbc_valid_license() === TRUE;

	if($setting['rncbc_email2customer']) {
		$to = $info['email'];
		if($is_pro) {
			$subject = $setting['rncbc_email_customer_subject'];
			$body = rncbc_mail_body($calendar_title, $date, $info, $setting['rncbc_email_customer_body']);
		} else {
			$default_setting = rncbc_default_setting();
			$subject = $default_setting['rncbc_email_customer_subject'];
			$body = rncbc_mail_body($calendar_title, $date, $info, $default_setting['rncbc_email_customer_body']);
		}


		wp_mail($to, $subject, $body, array(
			'Content-Type: text/html; charset=UTF-8',
			'From: '. get_option('blogname') .' < ' . get_option('admin_email') . '>',
		));
	}

	if($setting['rncbc_email2admin']) {
		$to = $setting['rncbc_email_admin_address'];
		if($is_pro) {
			$subject = $setting['rncbc_email_admin_subject'];
			$body = rncbc_mail_body($calendar_title, $date, $info, $setting['rncbc_email_admin_body']);
		} else {
			$default_setting = rncbc_default_setting();
			$subject = $default_setting['rncbc_email_admin_subject'];
			$body = rncbc_mail_body($calendar_title, $date, $info, $default_setting['rncbc_email_admin_body']);
		}


		wp_mail($to, $subject, $body, array(
			'Content-Type: text/html; charset=UTF-8',
			'From: '. get_option('blogname') .' < ' . get_option('admin_email') . '>',
		));
	}

}

function rncbc_mail_body($calendar_title, $date, $info, $body) {
	$booking_info = '';
	$booking_info .= '<h3>'.$calendar_title.'</h3>';
	$booking_info .= '<p>Name:'. $info['name'] .'</p>';
	$booking_info .= '<p>Phone number :'. $info['phone'] .'</p>';
	$booking_info .= '<p>Comment :'. $info['comments'] .'</p>';
	$booking_info .= '<br />';
	$booking_info .= '<h3>Booking detail</h3>';
	$booking_info .= '<h4>'. date('m/d/Y', strtotime($date['day'])) .'</h4>';
	$booking_info .= "<p>".$date['from']." - ".$date['to']."</p>";
	$booking_info .= '<p>Court:'. $date['court'] .'</p>';
	$booking_info .= '<br />';

	return str_replace('[booking_information]', $booking_info, $body);
}


function rncbc_valid_license() {
	$licensekey = get_option('rncbc_license_key');
	if(!$licensekey)
		return 'license key can not be blank';

	$localkey  = get_transient( 'rncbc_cached_local_key' );
	$check_result = rncbc_check_license($licensekey, $localkey);

	if($check_result['status'] == 'Active') {
		if($check_result['localkey'])
			set_transient( 'rncbc_cached_local_key', $check_result['localkey'], 86400 * 7 );

		return true;
	} else {
		return $check_result['message'];
	}
}

function rncbc_check_license($licensekey = '', $localkey= '') {

	$whmcsurl = 'https://www.ezihosting.com/billing/';
	$licensing_secret_key = 'ezi-ou@yikja26#78$';
	$localkeydays = 1;
	$allowcheckfaildays = 0;


	$check_token = time() . md5(mt_rand(1000000000, 9999999999) . $licensekey);
	$checkdate = date("Ymd");
	$domain = $_SERVER['SERVER_NAME'];
	$usersip = isset($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : $_SERVER['LOCAL_ADDR'];
	$dirpath = dirname(__FILE__);
	$verifyfilepath = 'modules/servers/licensing/verify.php';
	$localkeyvalid = false;
	if ($localkey) {
		$localkey = str_replace("\n", '', $localkey); # Remove the line breaks
		$localdata = substr($localkey, 0, strlen($localkey) - 32); # Extract License Data
		$md5hash = substr($localkey, strlen($localkey) - 32); # Extract MD5 Hash
		if ($md5hash == md5($localdata . $licensing_secret_key)) {
			$localdata = strrev($localdata); # Reverse the string
			$md5hash = substr($localdata, 0, 32); # Extract MD5 Hash
			$localdata = substr($localdata, 32); # Extract License Data
			$localdata = base64_decode($localdata);
			$localkeyresults = unserialize($localdata);
			$originalcheckdate = $localkeyresults['checkdate'];
			if ($md5hash == md5($originalcheckdate . $licensing_secret_key)) {
				$localexpiry = date("Ymd", mktime(0, 0, 0, date("m"), date("d") - $localkeydays, date("Y")));
				if ($originalcheckdate > $localexpiry) {
					$localkeyvalid = true;
					$results = $localkeyresults;
					$validdomains = explode(',', $results['validdomain']);
					if (!in_array($_SERVER['SERVER_NAME'], $validdomains)) {
						$localkeyvalid = false;
						$localkeyresults['status'] = "Invalid";
						$results = array();
					}
					$validips = explode(',', $results['validip']);
					if (!in_array($usersip, $validips)) {
						$localkeyvalid = false;
						$localkeyresults['status'] = "Invalid";
						$results = array();
					}
					$validdirs = explode(',', $results['validdirectory']);
					if (!in_array($dirpath, $validdirs)) {
						$localkeyvalid = false;
						$localkeyresults['status'] = "Invalid";
						$results = array();
					}
				}
			}
		}
	}
	if (!$localkeyvalid) {
		$responseCode = 0;
		$postfields = array(
			'licensekey' => $licensekey,
			'domain' => $domain,
			'ip' => $usersip,
			'dir' => $dirpath,
		);
		if ($check_token) $postfields['check_token'] = $check_token;
		$query_string = '';
		foreach ($postfields AS $k=>$v) {
			$query_string .= $k.'='.urlencode($v).'&';
		}
		if (function_exists('curl_exec')) {
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $whmcsurl . $verifyfilepath);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $query_string);
			curl_setopt($ch, CURLOPT_TIMEOUT, 30);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			$data = curl_exec($ch);
			$responseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			curl_close($ch);
		} else {
			$responseCodePattern = '/^HTTP\/\d+\.\d+\s+(\d+)/';
			$fp = @fsockopen($whmcsurl, 80, $errno, $errstr, 5);
			if ($fp) {
				$newlinefeed = "\r\n";
				$header = "POST ".$whmcsurl . $verifyfilepath . " HTTP/1.0" . $newlinefeed;
				$header .= "Host: ".$whmcsurl . $newlinefeed;
				$header .= "Content-type: application/x-www-form-urlencoded" . $newlinefeed;
				$header .= "Content-length: ".@strlen($query_string) . $newlinefeed;
				$header .= "Connection: close" . $newlinefeed . $newlinefeed;
				$header .= $query_string;
				$data = $line = '';
				@stream_set_timeout($fp, 20);
				@fputs($fp, $header);
				$status = @socket_get_status($fp);
				while (!@feof($fp)&&$status) {
					$line = @fgets($fp, 1024);
					$patternMatches = array();
					if (!$responseCode
						&& preg_match($responseCodePattern, trim($line), $patternMatches)
					) {
						$responseCode = (empty($patternMatches[1])) ? 0 : $patternMatches[1];
					}
					$data .= $line;
					$status = @socket_get_status($fp);
				}
				@fclose ($fp);
			}
		}
		if ($responseCode != 200) {
			$localexpiry = date("Ymd", mktime(0, 0, 0, date("m"), date("d") - ($localkeydays + $allowcheckfaildays), date("Y")));
			if ($originalcheckdate > $localexpiry) {
				$results = $localkeyresults;
			} else {
				$results = array();
				$results['status'] = "Invalid";
				$results['description'] = "Remote Check Failed";
				return $results;
			}
		} else {
			preg_match_all('/<(.*?)>([^<]+)<\/\\1>/i', $data, $matches);
			$results = array();
			foreach ($matches[1] AS $k=>$v) {
				$results[$v] = $matches[2][$k];
			}
		}
		if (!is_array($results)) {
			die("Invalid License Server Response");
		}
		if ($results['md5hash']) {
			if ($results['md5hash'] != md5($licensing_secret_key . $check_token)) {
				$results['status'] = "Invalid";
				$results['description'] = "MD5 Checksum Verification Failed";
				return $results;
			}
		}
		if ($results['status'] == "Active") {
			$results['checkdate'] = $checkdate;
			$data_encoded = serialize($results);
			$data_encoded = base64_encode($data_encoded);
			$data_encoded = md5($checkdate . $licensing_secret_key) . $data_encoded;
			$data_encoded = strrev($data_encoded);
			$data_encoded = $data_encoded . md5($data_encoded . $licensing_secret_key);
			$data_encoded = wordwrap($data_encoded, 80, "\n", true);
			$results['localkey'] = $data_encoded;
		}
		$results['remotecheck'] = true;
	}
	unset($postfields,$data,$matches,$whmcsurl,$licensing_secret_key,$checkdate,$usersip,$localkeydays,$allowcheckfaildays,$md5hash);
	return $results;
}

function rncbc_time($timestamp = false) {
	return $timestamp ? $timestamp : time() + get_option( 'gmt_offset' ) * HOUR_IN_SECONDS;
}


function rncbc_paypal_available($front_end = false) {
	if($front_end) {
		return get_option('rncbc_paypal_enabled') == 1;
	} else {
		return is_plugin_active( 'rn-bc-paypal/rn_bc_paypal.php' );
	}
}

function rncbc_paypal_url($uri = '') {
	$uri = ltrim($uri, '/');
	$uri = str_replace(array('.php', '?'), array('', '&'), $uri);
	return get_bloginfo('url', 'display')."/index.php?rncbc_pay=".$uri;
}


function rncbc_coaching_available() {
	return get_option('rncbc_coaching_enable') == 1;
}