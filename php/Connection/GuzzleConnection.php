<?php
/**
 * Tozny E3DB
 *
 * LICENSE
 *
 * Permission is hereby granted, free of charge, to any person obtaining
 * a copy of this software and associated documentation files (the
 * "Software"), to deal in the Software without restriction, including
 * without limitation the rights to use, copy, modify, merge, publish,
 * distribute, sublicense, and/or sell copies of the Software, and to
 * permit persons to whom the Software is furnished to do so, subject to
 * the following conditions:
 *
 * The above copyright notice and this permission notice shall be
 * included in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 * NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
 * LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
 * OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
 * WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 *
 * @package    Tozny\E3DB
 * @copyright  Copyright (c) 2017 Tozny, LLC (https://tozny.com)
 * @license    MIT License
 */

declare(strict_types=1);

namespace Tozny\E3DB\Connection;

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Sainsburys\Guzzle\Oauth2\GrantType\ClientCredentials;
use Sainsburys\Guzzle\Oauth2\GrantType\RefreshToken;
use Sainsburys\Guzzle\Oauth2\Middleware\OAuthMiddleware;
use Tozny\E3DB\Config;
use Sainsburys\Guzzle\Oauth2\GrantType\PasswordCredentials;
use Tozny\E3DB\Types\Record;

class GuzzleConnection extends Connection
{
    /**
     * @var Client GuzzleHTTP client.
     */
    private $client;

    public function __construct(Config $config)
    {
        parent::__construct($config);

        $auth_client = new Client(['base_uri' => $config->api_url]);
        $auth_config = [
            PasswordCredentials::CONFIG_CLIENT_ID => $config->api_key_id,
            PasswordCredentials::CONFIG_CLIENT_SECRET => $config->api_secret,
            PasswordCredentials::CONFIG_TOKEN_URL => '/v1/auth/token',
            'scope' => null,
        ];
        $grant = new ClientCredentials($auth_client, $auth_config);
        $refresh = new RefreshToken($auth_client, $auth_config);
        $middleware = new OAuthMiddleware($auth_client, $grant, $refresh);

        $handlerStack = HandlerStack::create();
        $handlerStack->push($middleware->onBefore());
        $handlerStack->push($middleware->onFailure(5));

        $this->client = new Client([
            'handler' => $handlerStack,
            'base_uri' => $config->api_url,
            'auth' => 'oauth2',
        ]);
    }

    /**
     * Retrieve an access key from the server.
     *
     * @param string $writer_id Writer/Authorizer for the access key
     * @param string $user_id   Record subject
     * @param string $reader_id Authorized reader
     * @param string $type      Record type for which the key will be used
     *
     * @return string|null Decrypted access key on success, NULL if no key exists.
     */
    function get_access_key(string $writer_id, string $user_id, string $reader_id, string $type)
    {
        $cache_key = "{$writer_id}.{$user_id}.{$type}";
        if (array_key_exists($cache_key, $this->ak_cache)) {
            return $this->ak_cache[ $cache_key ];
        }

        $path = $this->uri('v1', 'storage', 'access_keys', $writer_id, $user_id, $reader_id, $type);
        $response = $this->client->request('GET', $path);
        $data = json_decode((string) $response->getBody(), true);

        if (null === $data) {
            return null;
        }

        $key = $this->decrypt_eak($data);
        $this->ak_cache[ $cache_key ] = $key;

        return $key;
    }

    /**
     * Create an access key on the server.
     *
     * @param string $writer_id Writer/Authorizer for the access key
     * @param string $user_id   Record subject
     * @param string $reader_id Authorized reader
     * @param string $type      Record type for which the key will be used
     * @param string $ak        Unencrypted access key
     */
    function put_access_key(string $writer_id, string $user_id, string $reader_id, string $type, string $ak): void
    {
        $cache_key = "{$writer_id}.{$user_id}.{$type}";
        $this->ak_cache[ $cache_key ] = $ak;

        // Get the reader's public key
        $client_info = json_decode((string) $this->get_client($reader_id)->getBody(), true);
        $reader_key = $client_info[ 'public_key' ][ 'curve25519' ];

        $encoded = $this->encrypt_ak($ak, $reader_key);

        $path = $this->uri('v1', 'storage', 'access_keys', $writer_id, $user_id, $reader_id, $type);
        $this->client->request('PUT', $path, ['json' => ['eak' => $encoded]]);
    }

    /**
     * Attempt to find a client based on their email address.
     *
     * @param string $email
     *
     * @return Response PSR7 response object
     */
    function find_client(string $email): Response
    {
        $path = $this->uri('v1', 'storage', 'clients', 'find');
        return $this->client->request('POST', $path, ['query' => ['email' => $email]]);
    }

    /**
     * Get a client's information based on their ID.
     *
     * @param string $client_id
     *
     * @return Response PSR7 response object
     */
    function get_client(string $client_id): Response
    {
        $path = $this->uri('v1', 'storage', 'clients', $client_id);
        return $this->client->request('GET', $path);
    }

    /**
     * Create a new object with E3DB
     *
     * @param string $path   API endpoint to request
     * @param Record $record Record to be created
     *
     * @return Response PSR7 response object
     */
    function post(string $path, Record $record): Response
    {
        return $this->client->request('POST', $path, ['json' => $record]);
    }

    /**
     * Retrieve an object from E3DB
     *
     * @param string $path API endpoint to request
     *
     * @return Response PSR7 response object
     */
    function get(string $path): Response
    {
        return $this->client->request('GET', $path);
    }

    /**
     * Update an object with E3DB
     *
     * @param string $path   API endpoint to request
     * @param Record $record Object to be updated
     *
     * @return Response PSR7 response object
     */
    function put(string $path, Record $record): Response
    {
        return $this->client->request('PUT', $path, ['json' => $record]);
    }

    /**
     * Delete an object from E3DB
     *
     * @param string $path API endpoint to request
     *
     * @return Response PSR7 response object
     */
    function delete(string $path): Response
    {
        return $this->client->request('DELETE', $path);
    }
}