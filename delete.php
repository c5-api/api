<?php
$url = 'http://localhost/web/-/api/config/api/test';

$curl_handle = curl_init();
curl_setopt($curl_handle, CURLOPT_URL, $url);
//curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT, $timeout);
curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($curl_handle, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($curl_handle, CURLOPT_CUSTOMREQUEST, 'DELETE');
$contents = curl_exec($curl_handle);
echo $contents;
//var_dump(json_decode($contents));