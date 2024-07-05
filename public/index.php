<?php
// require_once __DIR__ . '/../src/Hubspot/HubspotSchema.php';
include("src/Hubspot/HubspotSchema.php");

$requestUri = $_SERVER['REQUEST_URI'];

$requestMethod = $_SERVER['REQUEST_METHOD'];

switch ($requestMethod) {
    case 'GET':
        if ($requestUri === '/discover') {
            // echo "hi";die;           
            try {   
                // echo "hi";die;             
                // $HubspotSchema = new HubspotSchema();
                $response = HubspotSchema::describe();
                // return all records
                // $response = $HubspotSchema->describe();
                print_r($response);die;
                header('Content-Type: application/json');
                echo json_encode($response, JSON_PRETTY_PRINT);
            } catch (Exception $e) {
                header('Content-Type: application/json', true, 500);
                echo json_encode(['error' => $e->getMessage()]);
            }
        
        } 
        
        break;
        
        default:
            handleNotFound();
}
function handleNotFound() {
    header('Content-Type: application/json', true, 404);
    echo json_encode(['error' => 'Not Found']);
}
?>
