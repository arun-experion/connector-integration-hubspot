<?php
require __DIR__.'/../vendor/autoload.php';

use HubSpot\Factory;
use HubSpot\Client\Crm\Companies\ApiException;

$access_token = 'CInOlpmFMhIbAAEBUEAA-SIAAED8BwEA4AeAAATwhwFg--EDGKWVmxYgndi9ICirw9UBMhQ_4b3mZ34-z606F3wQJuQzyQQW5zpSAAAAQQAAAADABwAAAAAAAACGAAAABgAAAAwAIICPAD4A4DEAAAAABMD__x8AEPADAACA__8DAAAAAQDgAQAA7j_5b_H_AAAAAAAA4H_PPwN_GEIUVS0ggPxVXPaIZGYE0eNntqMLHVxKA25hMVIAWgBgAA';
$client = Factory::createWithAccessToken($access_token);

try {
    $apiResponse = $client->crm()->companies()->basicApi()->getPage(10, false);
    var_dump($apiResponse);
} catch (ApiException $e) {
    echo "Exception when calling basic_api->get_page: ", $e->getMessage();
}