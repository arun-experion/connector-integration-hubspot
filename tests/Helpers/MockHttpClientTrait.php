<?php /** @noinspection PhpAccessingStaticMembersOnTraitInspection */

namespace Tests\Helpers;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use League\OAuth2\Client\Token\AccessToken;

defined("MOCK_FOLDER") || define("MOCK_FOLDER", "mocks/http");

define("OAUTH_ACCESS_TOKEN", "__MOCK_ACCESS_TOKEN__");
define("OAUTH_REFRESH_TOKEN","__MOCK_REFRESH_TOKEN__");
define("OAUTH_EXPIRES", 1601592098);

/**
 * Mock HTTP Client to provide mocked API requests to unit tests.
 */
trait MockHttpClientTrait
{
    public array $container = [];

    public static function setUpBeforeClass():void
    {
        static::$accessToken =  new AccessToken([
              'access_token'  => OAUTH_ACCESS_TOKEN,
              'refresh_token' => OAUTH_REFRESH_TOKEN,
              'expires'       => OAUTH_EXPIRES
          ]);
    }
    /**
     * @param \GuzzleHttp\Psr7\Response[] $httpResponses
     *
     * @return \GuzzleHttp\Client
     */
    function getHttpClient(array $httpResponses): Client
    {
        $this->container = [];
        $history         = Middleware::history($this->container);
        $mock            = new MockHandler($httpResponses);
        $handlerStack    = HandlerStack::create($mock);
        $handlerStack->push($history);

        return new Client(['handler' => $handlerStack]);
    }
}
