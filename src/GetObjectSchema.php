<?php
require __DIR__.'/../vendor/autoload.php';

use HubSpot\Factory;
use HubSpot\Client\Crm\Schemas\ApiException;
use HubSpot\Client\Crm\Schemas\Model\ObjectTypePropertyCreate;
// use HubSpot\Client\CrmObjectSchemas\Schemas\ApiException;

$access_token = 'CInOlpmFMhIbAAEBUEAA-SIAAED8BwEA4AeAAATwhwFg--EDGKWVmxYgndi9ICirw9UBMhQ_4b3mZ34-z606F3wQJuQzyQQW5zpSAAAAQQAAAADABwAAAAAAAACGAAAABgAAAAwAIICPAD4A4DEAAAAABMD__x8AEPADAACA__8DAAAAAQDgAQAA7j_5b_H_AAAAAAAA4H_PPwN_GEIUVS0ggPxVXPaIZGYE0eNntqMLHVxKA25hMVIAWgBgAA';
$client = Factory::createWithAccessToken($access_token);

try {
    // $apiResponse = $client->crmObjectSchemas()->schemas()->coreApi()->getAll(false);
    $apiResponse = $client->crm()->schemas()->coreApi()->getAll(false);
    var_dump($apiResponse);
} catch (Exception $e) {
    echo "Exception when calling core_api->get_all: ", $e->getMessage();
}