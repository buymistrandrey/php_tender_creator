<?php
include 'tender_additional_data.php';
include 'plugins/faker/autoload.php';
include 'tender/dk021.php';

$faker = Faker\Factory::create('uk_UA');

function getClassification(){
    global $classifications;
    $classification = $classifications[rand(0, count($classifications) - 1)];
    return $classification;
};

function getUnit(){
    $unit = [['BX', 'ящик'], ['D64', 'блок'], ['E48', 'послуга']];
    return $unit[array_rand($unit, 1)];
};

function generateIdForItem(){
    return bin2hex(openssl_random_pseudo_bytes(16));
};


function generateIdForLot($number_of_lots){
    $list_of_id = [];
    foreach (range(0, $number_of_lots - 1) as $lot)
        array_push($list_of_id, bin2hex(openssl_random_pseudo_bytes(16)));
    return $list_of_id;
};


function tenderPeriod($accelerator, $procurement_method, $received_tender_status){
    timeNow();
    # tender_start_date
    $tender_start_date = timeNow()->format('Y-m-d\TH:i:sO');
    # tender_end_date
    $date_day = timeNow()->add(new DateInterval('PT' . round(ceil(31 * (1440.0 / $accelerator)), 1) . 'M'));
    $tender_end_date = $date_day->format('Y-m-d\TH:i:sO');
    $tender_period_data = array("tenderPeriod"=>array("startDate"=>$tender_start_date, "endDate"=>$tender_end_date));

    if ($procurement_method == 'belowThreshold'){
        $one_day = timeNow()->add(new DateInterval('PT' . round(ceil(1 * (1440.0 / $accelerator)), 1) . 'M'));
        $ten_days = timeNow()->add(new DateInterval('PT' . round(ceil(10 * (1440.0 / $accelerator)), 1) . 'M'));
        $five_dozens_days = timeNow()->add(new DateInterval('PT' . round(ceil(60 * (1440.0 / $accelerator)), 1) . 'M'));
        $tender_start_date = $one_day->format('Y-m-d\TH:i:sO');
        $tender_end_date = $five_dozens_days->format('Y-m-d\TH:i:sO');

        if ($received_tender_status == 'active.qualification') {
            $tender_end_date = $ten_days->format('Y-m-d\TH:i:sO');
        }

        $tender_period_data = array("tenderPeriod"=>array(
                                            "startDate"=>$tender_start_date,
                                            "endDate"=>$tender_end_date),
                                    "enquiryPeriod"=>array(
                                            "startDate"=>timeNow()->format('Y-m-d\TH:i:sO'),
                                            "endDate"=>$tender_start_date));

    }


    return $tender_period_data;
};


function generateFeatures($tender_data){
    global  $faker;
    if (array_key_exists('lots', $tender_data['data'])) {
        $number_of_lots = count($tender_data['data']['lots']);
    }
    else {
        $number_of_lots = 0;
    };

    $features = array(array(
                "code"=>generateIdForItem(),
                "description"=>"Описание неценового критерия для тендера",
                "title"=>"Неценовой критерий для тендера",
                "enum"=>array(),
                "title_en"=>"Feature of tender",
                "description_en"=>"Description of feature for tender",
                "featureOf"=>"tenderer"
    ));
    foreach (range(0, 5) as $feature_number){
        $feature_number += 1;
        $feature = array(
                        "title_en"=>"Feature option " . ($feature_number),
                        "value"=>(float)('0.0' . $feature_number),
                        "title"=>"Опция " . ($feature_number) . ' ' . str_replace('\n', ' ', $faker->text(20))
        );
        array_push($features[0]['enum'], $feature );
    };

    if ($number_of_lots != 0) {
        foreach (range(0, $number_of_lots - 1) as $lot){
            $lot_feature = array(
                            "code"=>generateIdForItem(),
                            "description"=>"Описание неценового критерия Лот " . ($lot + 1),
                            "title"=>"Неценовой критерий Лот ". ($lot + 1),
                            "enum"=>array(),
                            "title_en"=>"Title of feature for lot " . ($lot + 1),
                            "description_en"=>"Description of feature for lot " . ($lot + 1),
                            "relatedItem"=>$tender_data['data']['lots'][$lot]['id'],
                            "featureOf"=>"lot"
            );
            foreach (range(0, 5) as $feature_number){
                $feature_number += 1;
                $feature = array(
                    "title_en"=>"Feature option " . ($feature_number),
                    "value"=>(float)('0.0' . $feature_number),
                    "title"=>"Опция " . ($feature_number) . " Лот " . ($lot + 1) . " " . str_replace('\n', ' ', $faker->text(20))
                );
                array_push($lot_feature['enum'],$feature);
            };
            array_push($features, $lot_feature);
        };
    };

    return $features;
};


function generateValues($procurement_method, $number_of_lots){
    global $limited_procurement;
    if (!isset($number_of_lots) or $number_of_lots == 0){
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


function generateItems($number_of_items, $procurement_method, $classification){
    global $faker;
    $unit = getUnit();
    $items = [];
    $item_number = 0;
    foreach (range(0, $number_of_items - 1) as $item_number) {
        $item_number += 1;
        $item_data = array(
            "description" => "Предмет закупки " . $item_number . ' ' . str_replace('\n', ' ', $faker->text(200)),
            "classification" => array(
                "scheme" => "ДК021",
                "description" => $classification[key($classification)],
                "id" => key($classification)
            ),
            "description_en" => "Description",
            "deliveryAddress" => array(
                "postalCode" => "00000",
                "countryName" => "Україна",
                "streetAddress" => "Улица",
                "region" => "Дніпропетровська область",
                "locality" => "Город"
            ),
            "deliveryDate" => array(
                "startDate" => timeNow()->add(new DateInterval('P' . 7 . 'D'))->format('Y-m-d\TH:i:sO'),
                "endDate" => timeNow()->add(new DateInterval('P' . 120 . 'D'))->format('Y-m-d\TH:i:sO'),
            ),
            "id" => generateIdForItem(),
            "unit" => array(
                "code" => $unit[0],
                "name" => $unit[1]
            ),
            "quantity" => rand(1, 10000)
        );

        if ($procurement_method == 'esco') {
            unset($item_data['deliveryDate'], $item_data['unit'], $item_data['quantity']);
        };
        array_push($items, $item_data);  //where add, what add
    };
    return $items;
};

function generateLots($lots_id, $values){
    global $faker;
    $lots = [];
    $lot_number = 0;
    foreach (range(0, count($lots_id) - 1) as $lot) {
        $lot_number += 1;
        $lots_data = array(
                        "status"=>"active",
                        "description"=>"Описание лота Лот " . $lot_number . ' ' . str_replace('\n', ' ', $faker->text(200)),
                        "title"=>"Лот " . $lot_number,
                        "title_en"=>"Title of lot in English",
                        "description_en"=>"Description of lot in English",
                        "id"=>$lots_id[$lot]
        );
        foreach($values as $key => $value){
            $lots_data[$key] = $values[$key];
        }
        array_push($lots, $lots_data);
    }

    return $lots;
};

function generateTenderJson($procurement_method, $number_of_lots, $number_of_items, $accelerator, $received_tender_status, $list_of_lots_id, $if_features, $skip_auction)
{
    global $limited_procurement;
    global $negotiation_procurement;
    global $faker;
    timeNow();

    $tender_data = json_decode('{
                    "data": {
                        "procurementMethodType": "",
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
                                "name": "Тарас Бульба",
                                "email": "testik@gmail.test"
                            },
                            "identifier": {
                                "scheme": "UA-EDR",
                                "legalName_en": "Test organization TEST Ltd.",
                                "id": "00000000",
                                "legalName": "Тестовая организация ООО Тест"
                            },
                            "name_en": "Company name en english"
                        }
                    }
                }', true);

    $tender_data['data']['title'] = str_replace('\n', ' ', $faker->text(200));
    $tender_data['data']['description'] = "Примечания для тендера Тест " . timeNow()->format('d-His');

    $tender_data['data']['procurementMethodType'] = $procurement_method;
    $tender_data['data']['procurementMethodDetails'] = 'quick, accelerator=' . $accelerator . '';

    //Select submission method details if isn't in limited procurement
    if (!in_array($procurement_method, $limited_procurement)){
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
    };

    //Add tender periods
    if (!in_array($procurement_method, $limited_procurement)){
        $tender_periods = tenderPeriod($accelerator, $procurement_method, $received_tender_status);
        foreach($tender_periods as $key => $value){
            $tender_data['data'][$key] = $tender_periods[$key];
        }
    };

    $classification = getClassification();

    if ($number_of_lots == 0) {
        $items = generateItems($number_of_items, $procurement_method, $classification);
        $tender_data['data']['items'] = $items;
    }
    else {
        $items = [];
        $lots = generateLots($list_of_lots_id, $values['lotValues']);
        foreach (range(0, $number_of_lots - 1) as $lot) {
            $lot_items = generateItems($number_of_items, $procurement_method, $classification);
            foreach (range(0, count($lot_items) - 1) as $item) {
                $lot_items[$item]['description'] = "Предмет закупки " . ($item + 1) . " Лот " . ($lot + 1) . ' ' . str_replace('\n', ' ', $faker->text(200));
                $lot_items[$item]['relatedLot'] = $list_of_lots_id[$lot];
                array_push($items, $lot_items[$item]);
            };
        };
        $tender_data['data']['items'] = $items;
        $tender_data['data']['lots'] = $lots;
    };


    if (!in_array($procurement_method, $limited_procurement)){
        if ($if_features == 1) {
            $tender_data['data']['features'] = generateFeatures($tender_data);
                };
    };

    return json_encode($tender_data);
};

$number_of_lots = 2;
$list_of_lots_id = generateIdForLot($number_of_lots);
echo generateTenderJson('reportin', $number_of_lots, 3, 1440,
'active.qualification', $list_of_lots_id, 1, true);
