<?php
include 'tender_additional_data.php';


function generateValues($procurement_method, $number_of_lots){
    global $limited_procurement;
    if (!isset($number_of_lots)){
    $number_of_lots = 1;
    };
    $generated_value = rand(100000, 1000000000);
    $currencies = ['UAH', 'USD', 'EUR', 'RUB'];  // 'GBP'
    $currency = $currencies[rand(0, count($currencies) - 1)];

    if ($procurement_method == 'esco'){
        $value = json_decode('{"tenderValues": {
                            "NBUdiscountRate": 0.99,
                            "yearlyPaymentsPercentageRange": 0.8,
                            "minimalStepPercentage": 0.02},
                 "lotValues": {
                            "yearlyPaymentsPercentageRange": 0.8,
                            "minimalStepPercentage": 0.02}
                 }', true);
    }
    else{
        $value = json_decode('{"tenderValues": {
                            "value": {
                                "currency": currency,
                                "amount": 0,
                                "valueAddedTaxIncluded": True},

                            "guarantee": {
                                "currency": currency,
                                "amount": 0
                            },
                            "minimalStep": {
                                "currency": currency,
                                "amount": "",
                                "valueAddedTaxIncluded": True
                            }},
                 "lotValues": {
                            "value": {
                                "currency": currency,
                                "amount": "",
                                "valueAddedTaxIncluded": True},

                            "guarantee": {
                                "currency": currency,
                                "amount": ""
                            },
                            "minimalStep": {
                                "currency": currency,
                                "amount": ",
                                "valueAddedTaxIncluded": True
                            }
                 }}', true);
        $value['tenderValues']['value']['amount'] = $generated_value;
        $value['tenderValues']['guarantee']['amount'] = round(($generated_value * 0.05), 2);
        $value['tenderValues']['minimalStep']['amount'] = round(($generated_value * 0.01), 2);
        $value['lotValues']['value']['amount'] = round(($generated_value / $number_of_lots), 2);
        $value['lotValues']['guarantee']['amount'] = round((($generated_value * 0.05) / $number_of_lots), 2);
        $value['lotValues']['minimalStep']['amount'] = round((($generated_value * 0.01) / $number_of_lots), 2);

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

    //Select submission method details
    $submission_method_details = 'quick';
    if ($skip_auction == True){
        if (in_array($procurement_method, $limited_procurement, false)){
            if ($procurement_method == 'esco'):
                $submission_method_details = 'quick(mode:no-auction)';
            else:
                $submission_method_details = 'quick(mode:fast-forward)';
            endif;
            };
    };
    $tender_data['data']['submissionMethodDetails'] = $submission_method_details;

    //Add reason for negotiation procedures
    if (in_array($procurement_method, $negotiation_procurement, true)){
        $tender_data['data']['cause'] = 'noCompetition';
        $tender_data['data']['causeDescription'] = 'Створення закупівлі для переговорної процедури за нагальною потребою';
    };

    //Add tender values
    $values = generateValues($procurement_method, $number_of_lots);
    foreach($values as $key => $value){
        $tender_data['data'][$key] = $values['tenderValues'];
    }


    return json_encode($tender_data);
};

echo generateTenderJson('above', 2, 3, 1440,
'STATUS', [1, 2], 1, true);
