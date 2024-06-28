<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require 'vendor/autoload.php';

use Connector\Integrations\Hubspot\Integration;
use Connector\Schema\IntegrationSchema;

try {
    $integration = new Integration();
    $schema = $integration->discover();
    
    if ($schema instanceof IntegrationSchema) {
        echo "Discover method called successfully.\n";
        var_dump($schema);
    } else {
        echo "Failed to call discover method.\n";
    }
} catch (Exception $e) {
    echo "An error occurred: " . $e->getMessage();
}
