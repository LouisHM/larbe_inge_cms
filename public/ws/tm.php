<?php
echo '<pre>';
$opts = [
  'http' => [
    'method'=>"GET",
    'header'=>"User-Agent: Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:59.0) \r\n"
  ]
];
$context = stream_context_create($opts);
$json = file_get_contents("http://nominatim.openstreetmap.org/search/?postalcode=79500&country=france&format=json&addressdetails=1&limit=1", false, $context);
$tbrep = json_decode($json, true);
print_r($tbrep);