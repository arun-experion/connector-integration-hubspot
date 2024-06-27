<?php
require __DIR__ ."/../vendor/autoload.php";
use ConfigHubspot\Config;
use Connector\Integrations\AbstractIntegration;
use Connector\Integrations\Authorizations\OAuthInterface;
use Connector\Integrations\Authorizations\OAuthTrait;
use Connector\Integrations\Response;
use Connector\Mapping;
use Connector\Record\RecordKey;
use Connector\Record\RecordLocator;
use Connector\Schema\IntegrationSchema;
// use Exception;
use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use PhpParser\Node\NullableType;



class Integration 
// extends AbstractIntegration implements OAuthInterface
{
    // use OAuthTrait;
   
    private static $refresh_token;


  
    public function discover(): IntegrationSchema
    {

        $schema = json_decode(file_get_contents(__DIR__."/../tests/testDiscover.json"),true);
        return new IntegrationSchema($schema);
        // TODO: Implement discover() method.
        // return new HubspotSchema();
    }

    public function extract(RecordLocator $recordLocator, Mapping $mapping, ?RecordKey $scope): Response
    {
        // TODO: Implement extract() method.
    }

    public function load(RecordLocator $recordLocator, Mapping $mapping, ?RecordKey $scope): Response
    {
        // TODO: Implement load() method.

    }

    /**
     * @throws \Connector\Exceptions\InvalidExecutionPlan
     */
    public function setAuthorization(string $authorization): void
    {
        $this->setOAuthCredentials($authorization);
        // TODO: Implement setAuthorization() method.
    }

    public function getAuthorizationProvider(): AbstractProvider
    {
        // TODO: Implement getAuthorizationProvider() method.
    }

    public function getAuthorizedUserName(ResourceOwnerInterface $user): string
    {
        // TODO: Implement getAuthorizedUserName() method.
    }
    
    public function getAccessToken() {
        // $url = Config::TOKEN_URI;
        // $client_id = Config::CLIENT_ID;
        // $client_secret = Config::CLIENT_SECRET;
        // $redirect_uri = Config::REDIRECT_URI;
        $url="https://api.hubapi.com/oauth/v1/token";
        $client_id="bc1e39c3-95f0-4655-8160-df2f2233e296";
        $client_secret= "d8251725-d0bc-4423-afcb-fe1036b25244";
        $redirect_uri="http://localhost";
        $authorization_code = '85bc8c0f-a962-460b-9d74-387a141b6d86';

        if (self::$refresh_token===null) {
            $data = array(
                'grant_type' => 'authorization_code',
                'client_id' => $client_id,
                'client_secret' => $client_secret,
                'redirect_uri' => $redirect_uri,
                'code' => $authorization_code
            );
           
        } else {
            echo 'Refresh Token is: ' . self::$refresh_token . PHP_EOL;

            $data = array(
                'grant_type' => 'refresh_token',
                'client_id' => $client_id,
                'client_secret' => $client_secret,
                'refresh_token' => self::$refresh_token
            );
          
        }

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            echo 'Error: ' . curl_error($ch) . PHP_EOL;
        }

        curl_close($ch);

        $response_data = json_decode($response, true);
        if (isset($response_data['refresh_token'])) {
            self::$refresh_token = $response_data['refresh_token'];
            echo 'New Refresh Token is: ' . self::$refresh_token . PHP_EOL;
        }

         print_r($response_data);
        return $response_data;
    }
}

// $integratin=new Integration();
// $integratin->getAccessToken() ;


$hubspotAPI =  new Integration();


// Use the refresh token to get a new access token
 $hubspotAPI->getAccessToken();

