<?php

# Select host for CDB
function tenderHostSelector($cdb_version){
    if ($cdb_version == 'dev') {
        $host = array('https://api-sandbox.prozorro.openprocurement.net/api/dev/tenders',
            'https://public.api-sandbox.prozorro.openprocurement.net/api/dev/tenders');
    }
    else {
        $host = array('https://lb.api-sandbox.openprocurement.org/api/2.4/tenders',
            'https://public.api-sandbox.openprocurement.org/api/2.4/tenders');
            }
    return $host;
};

// generate headers for create tender
function tenderHeadersRequest($cdb_version, $json_data){
    global $key;
    $authorization = 'Authorization: Basic ' . $key;
    $headers = array(
        "Content-Type: application/json",
        "Content-Length: " . strlen($json_data),
        $authorization
    );
    return $headers;
};




function jsonActivateTender($procurement_method){
    global $above_threshold_procurement;
    global $below_threshold_procurement;
    if (in_array($procurement_method, $above_threshold_procurement))
        $activate_tender_json = array(
                                    "data"=>array(
                                    "status"=>"active.tendering"
                                    )
                                );
    else if (in_array($procurement_method, $below_threshold_procurement)) {
        $activate_tender_json = array(
                                    "data" => array(
                                        "status" => "active.enquiries"
                                    )
                                );
            }
    else{
        $activate_tender_json = array(
                                    "data" => array(
                                        "status" => "active"
                                    )
                                );
    }
    return json_encode($activate_tender_json);
};
