<?php
require_once 'config.php';
include 'tender/tender_data_for_requests.php';


$host = 'https://lb.api-sandbox.openprocurement.org/api/2.4/tenders';


function sendRequestToCdb($headers, $host, $endpoint, $method, $json_request, $request_name, $entity)
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

//    echo $host . $endpoint;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $host . $endpoint);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $json_request);
    curl_setopt($ch, CURLOPT_HEADER, 1);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    curl_setopt($ch, CURLOPT_FAILONERROR, FALSE);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile); // Cookie aware
    curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile); // Cookie aware
    $content = curl_exec($ch);

//    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_info = curl_getinfo($ch);

    curl_close($ch);

    $header_size = $curl_info['header_size'];
//    $header = substr($content, 0, $header_size);  //get header from response
    $body = substr($content, $header_size);  //body = substrate header from content (content - header = body)

    return json_decode($body, true);
}



class TenderRequests{

    public function __construct($cdb){
        $this->cdb = $cdb;
        $this->host = tenderHostSelector($cdb)[0];
        $this->host_public = tenderHostSelector($cdb)[1];
        $this->entity = 'tenders';

    }

    public function publishTender($json_tender){
        return sendRequestToCdb(tenderHeadersRequest($this->cdb, $json_tender),  $this->host, '', 'POST', $json_tender, 'Publish tender', $this->entity);
    }

    public function activateTender($tender_id_long, $token, $procurement_method){
        global $json_activate_tender;
        $json_tender_activation = jsonActivateTender($procurement_method);
        return sendRequestToCdb(tenderHeadersRequest($this->cdb, $json_tender_activation),  $this->host, '/' . $tender_id_long . '?acc_token=' . $token, 'PATCH', $json_tender_activation, 'Activate tender', $this->entity);
    }
}

