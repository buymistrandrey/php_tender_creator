<?php
include 'tender_additional_data.php';

function generateTenderJson($procurement_method, $number_of_lots, $number_of_items, $accelerator, $received_tender_status, $list_of_lots_id, $if_features, $skip_auction)
{
    global $limited_procurement;


    $tender_data = json_decode('{
                    "data": {
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

    $submission_method_details = 'quick';
    if ($skip_auction == True){
        if ($procurement_method == $limited_procurement){
            if ($procurement_method == 'esco'):
                $submission_method_details = 'quick(mode:no-auction)';
            else:
                $submission_method_details = 'quick(mode:fast-forward)';
            endif;
            };
    };

    $tender_data['data']['submissionMethodDetails'] = $submission_method_details;

    return json_encode($tender_data);
};

echo generateTenderJson('METHOD', 2, 3, 1440,
'STATUS', [1, 2], 1, true);
