<?php
include 'data_for_tender.php';


function creationOfTender($tc_request, $user_id){
    $data = json_decode($tc_request, true);
    $procurement_method = $data['procurementMethodType'];
    $number_of_items = $data['number_of_items'];
    $accelerator = $data['accelerator'];
    $received_tender_status = $data['received_tender_status'];
    $api_version = $data['api_version'];


    $skip_auction = False;
    if (array_key_exists('skip_auction', $data)){
        $skip_auction = True;
    };

    $number_of_lots = 0;
    if (array_key_exists('number_of_lots', $data)) {
        $number_of_lots = $data["number_of_lots"];
    };

    $if_features = False;
    if (array_key_exists('if_features', $data)) {
        $if_features = 1;
    };

    if ($procurement_method == 'reporting') {
        $number_of_lots = 0;
    };

    $list_of_id_lots = generateIdForLot($number_of_lots);
    $json_tender = generateTenderJson($procurement_method, $number_of_lots, $number_of_items, $accelerator,
        $received_tender_status, $list_of_id_lots, $if_features, $skip_auction);

    $tender = new TenderRequests($api_version);
    $t_publish = $tender->publishTender($json_tender);

    $tender_id_long = $t_publish['data']['id'];
    $tender_token = $t_publish['access']['token'];
    $tender_id_short = $t_publish['data']['tenderID'];

    sleep(1);
    $t_activate = $tender->activateTender($tender_id_long, $tender_token, $procurement_method);

    return $t_activate;
}