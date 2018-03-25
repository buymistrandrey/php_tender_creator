<?php
define ('ROOT_DIR', __DIR__);
require_once 'request.php';
include 'tender/tender.php';


$host = $_SERVER['SERVER_NAME']  . $_SERVER['REQUEST_URI'];

if ($_SERVER['REQUEST_URI'] == '/api/tenders' && $_SERVER['REQUEST_METHOD'] == 'POST'){
    $requestBody = file_get_contents('php://input');
    $result = creationOfTender($requestBody, 1);
    header('Content-Type: application/json');
    echo json_encode($result);
}
else{
    echo '404';
}

