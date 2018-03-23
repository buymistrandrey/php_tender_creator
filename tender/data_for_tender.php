<?php
include 'tender_additional_data.php';


function tenderPeriod($accelerator, $procurement_method, $received_tender_status){
    global $date_now;
    # tender_start_date
    $tender_start_date = $date_now->format('Y-m-d\TH:i:sO');
    # tender_end_date
    $date_day = $date_now->add(new DateInterval('PT' . round(ceil(31 * (1440.0 / $accelerator)), 1) . 'M'));
    $tender_end_date = $date_day->format('Y-m-d\TH:i:sO');
    $tender_period_data = array("tenderPeriod"=>array("startDate"=>$tender_start_date, "endDate"=>$tender_end_date));

    if ($procurement_method == 'belowThreshold'){
        $one_day = $date_now->add(new DateInterval('PT' . round(ceil(1 * (1440.0 / $accelerator)), 1) . 'M'));
        $ten_days = $date_now->add(new DateInterval('PT' . round(ceil(10 * (1440.0 / $accelerator)), 1) . 'M'));
        $five_dozens_days = $date_now->add(new DateInterval('PT' . round(ceil(60 * (1440.0 / $accelerator)), 1) . 'M'));
        $tender_start_date = $one_day->format('Y-m-d\TH:i:sO');
        $tender_end_date = $five_dozens_days->format('Y-m-d\TH:i:sO');

        if ($received_tender_status == 'active.qualification') {
            $tender_end_date = $ten_days->format('Y-m-d\TH:i:sO');
        }

        $tender_period_data = array("tenderPeriod"=>array(
                                            "startDate"=>$tender_start_date,
                                            "endDate"=>$tender_end_date),
                                    "enquiryPeriod"=>array(
                                            "startDate"=>$date_now->format('Y-m-d\TH:i:sO'),
                                            "endDate"=>$tender_start_date));

    };

    return $tender_period_data;
};


function generateValues($procurement_method, $number_of_lots){
    global $limited_procurement;
    if (!isset($number_of_lots)){
    $number_of_lots = 1;
    };
    $generated_value = rand(100000, 1000000000);
    $currencies = ['UAH', 'USD', 'EUR', 'RUB'];  // 'GBP'
    $currency = $currencies[rand(0, count($currencies) - 1)];

    if ($procurement_method == 'esco'){
        $value = array("tenderValues"=>array(
                            "NBUdiscountRate"=>0.99,
                            "yearlyPaymentsPercentageRange"=>0.8,
                            "minimalStepPercentage"=>0.02
                            ),
                 "lotValues"=>array(
                            "yearlyPaymentsPercentageRange"=>0.8,
                            "minimalStepPercentage"=>0.02)
        );
    }
    else{
        $value = array("tenderValues"=>array(
                            "value"=>array(
                                "currency"=>$currency,
                                "amount"=>$generated_value,
                                "valueAddedTaxIncluded"=>true
                            ),
                            "guarantee"=>array(
                                "currency"=>$currency,
                                "amount"=>round(($generated_value * 0.05), 2)
                            ),
                            "minimalStep"=>array(
                                "currency"=>$currency,
                                "amount"=>round(($generated_value * 0.01), 2),
                                "valueAddedTaxIncluded"=>true
                        )),
                 "lotValues"=>array(
                            "value"=>array(
                                "currency"=>$currency,
                                "amount"=>round(($generated_value / $number_of_lots), 2),
                                "valueAddedTaxIncluded"=>true
                            ),
                            "guarantee"=>array(
                                "currency"=>$currency,
                                "amount"=>round((($generated_value * 0.05) / $number_of_lots), 2)
                            ),
                            "minimalStep"=>array(
                                "currency"=>$currency,
                                "amount"=>round((($generated_value * 0.01) / $number_of_lots), 2),
                                "valueAddedTaxIncluded"=>true
                            )
                 ));

        if (in_array($procurement_method, $limited_procurement, true)){
            unset($value['tenderValues']['guarantee'], $value['tenderValues']['minimalStep'], $value['lotValues']['guarantee'], $value['lotValues']['minimalStep']);
        };

    };

    return $value;
};


function generateTenderJson($procurement_method, $number_of_lots, $number_of_items, $accelerator, $received_tender_status, $list_of_lots_id, $if_features, $skip_auction)
{
    global $limited_procurement;
    global $negotiation_procurement;

    $tender_data = json_decode('{
                    "data": {
                        "procurementMethodType": "",
                        "description": "Примечания для тендера Тест !!!!!!!!!!!",
                        "title": "TITLE!!!",
                        "status": "draft",
                        "title_en": "Title of tender in english",
                        "description_en": "",
                        "mode": "test",
                        "title_ru": "",
                        "procuringEntity": {
                            "kind": "defense",
                            "name": "Тестовая организация ООО Тест",
                            "address": {
                                "postalCode": "12345",
                                "countryName": "Україна",
                                "streetAddress": "Улица Койкого",
                                "region": "місто Київ",
                                "locality": "Київ"
                            },
                            "contactPoint": {
                                "telephone": "+380002222222",
                                "url": "http://www.site.site",
                                "name_en": "Name of person in english",
                                "name": "!!!!!!!!!!!!!!!!",
                                "email": "testik@gmail.test"
                            },
                            "identifier": {
                                "scheme": "UA-EDR",
                                "legalName_en": "!!!!!!!!!!!!!!!!!!!",
                                "id": "00000000",
                                "legalName": "Тестовая организация ООО Тест"
                            },
                            "name_en": "Company name en english"
                        }
                    }
                }', true);

    $tender_data['data']['procurementMethodType'] = $procurement_method;
    $tender_data['data']['procurementMethodDetails'] = 'quick, accelerator=' . $accelerator . '';


    //Select submission method details if isn't in limited procurement
    if (in_array($procurement_method, $limited_procurement, false)){
        if ($skip_auction == True){
            if ($procurement_method == 'esco'){
                $submission_method_details = 'quick(mode:no-auction)';
            }
            else{
                $submission_method_details = 'quick(mode:fast-forward)';
            }
        }
        else{
            $submission_method_details = 'quick';
        }
        $tender_data['data']['submissionMethodDetails'] = $submission_method_details;
    };


    //Add reason for negotiation procedures
    if (in_array($procurement_method, $negotiation_procurement, true)){
        $tender_data['data']['cause'] = 'noCompetition';
        $tender_data['data']['causeDescription'] = 'Створення закупівлі для переговорної процедури за нагальною потребою';
    };

    //Add tender values
    $values = generateValues($procurement_method, $number_of_lots);
    foreach($values['tenderValues'] as $key => $value){
        $tender_data['data'][$key] = $values['tenderValues'][$key];
    }

    //Add tender periods
    if (in_array($procurement_method, $limited_procurement, false)){
        $tender_periods = tenderPeriod($accelerator, $procurement_method, $received_tender_status);
        foreach($tender_periods as $key => $value){
            $tender_data['data'][$key] = $tender_periods;
        }
        }

    return json_encode($tender_data);
};

echo generateTenderJson('above', 2, 3, 1440,
'STATUS', [1, 2], 1, true);
