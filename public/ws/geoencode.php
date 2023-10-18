<?php

/** geoencode Via nominatim
 * 
 * @param type $codpost
 * @param type $address
 * @param type $country
 * @return string
 */
function geoencode($codpost,$address = '',$country='france') {
	//echo '<pre>';
	$opts = [
	  'http' => [
		'method'=>"GET",
		'header'=>"User-Agent: Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:59.0) \r\n"
	  ]
	];
	$context = stream_context_create($opts);
	if (trim($address) != '') {
		$address = urlencode ($address);
		$q = "?q=$address&";
	} else {
		$q = "?postalcode=$codpost&";
	}
	$url = "http://nominatim.openstreetmap.org/search/".$q."country=$country&format=json&addressdetails=1&limit=1";
	//echo $url;
	$json = file_get_contents($url, false, $context);
	try {
		$tbrep = json_decode($json, true);
		if (is_array($tbrep[0])) {
			return $tbrep[0]['lat'].';'.$tbrep[0]['lon'];
		} else return '';
	} catch (Exception $ex) {
            return '';
    }
}