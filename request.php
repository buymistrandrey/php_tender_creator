<?php
require_once 'config.php';
require_once 'tender/data_for_tender.php';


$host = 'https://lb.api-sandbox.openprocurement.org/api/2.4/tenders';


function generateHeaders($key){
    $authorization = 'Authorization: Basic ' . $key;
    $headers = array(
        "Cache-Control: no-cache",
        "Content-Type: application/json",
        $authorization
    );
    return $headers;
}
$headers = generateHeaders($key);


function sendRequestToCdb($headers, $host, $data)
{
    $cookieFile = "cookies.txt";
    if (!file_exists($cookieFile)) {
        $fh = fopen($cookieFile, "w");
        fwrite($fh, "");
        fclose($fh);
    }

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $host);
    curl_setopt($ch, CURLOPT_NOBODY, TRUE);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile); // Cookie aware
    curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile); // Cookie aware
    curl_exec($ch);
    curl_close($ch);

//    $fp = fopen(dirname(__FILE__) . '/logs/' . date('Y-m-d H-i-s', time()) . '.txt', 'w');

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $host);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_HEADER, 1);
    curl_setopt($ch, CURLOPT_FAILONERROR, FALSE);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile); // Cookie aware
    curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile); // Cookie aware
    //curl_setopt($ch, CURLOPT_VERBOSE, 1); // Cookie aware
    //curl_setopt($ch, CURLOPT_STDERR, $fp); // Cookie aware
    $content = curl_exec($ch);

//    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_info = curl_getinfo($ch);

    curl_close($ch);

    $header_size = $curl_info['header_size'];
//    $header = substr($content, 0, $header_size);  //get header from response
    $body = substr($content, $header_size);  //body = substrate header from content (content - header = body)

    return $body;
}

//echo $content;
//echo $http_code . ' ';

//echo sendRequestToCdb($headers, $host, $data);
