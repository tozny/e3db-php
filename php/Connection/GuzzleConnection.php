<?php
/**
 * Tozny E3DB
 *
 * LICENSE
 *
 * Tozny dual licenses this product. For commercial use, please contact
 * info@tozny.com. For non-commercial use, the contents of this file are
 * subject to the TOZNY NON-COMMERCIAL LICENSE (the "License") which
 * permits use of the software only by government agencies, schools,
 * universities, non-profit organizations or individuals on projects that
 * do not receive external funding other than government research grants
 * and contracts.  Any other use requires a commercial license. You may
 * not use this file except in compliance with the License. You may obtain
 * a copy of the License at https://tozny.com/legal/non-commercial-license.
 * Software distributed under the License is distributed on an "AS IS"
 * basis, WITHOUT WARRANTY OF ANY KIND, either express or implied. See the
 * License for the specific language governing rights and limitations under
 * the License. Portions of the software are Copyright (c) TOZNY LLC, 2017.
 * All rights reserved.
 *
 * @package    Tozny\E3DB
 * @copyright  Copyright (c) 2017 Tozny, LLC (https://tozny.com)
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
    function put_access_key(string $writer_id, string $user_id, string $reader_id, string $type, string $ak)
    {
        // Get the reader's public key
        $client_info = json_decode((string) $this->get_client($reader_id)->getBody(), true);
        $reader_key = $client_info[ 'public_key' ][ 'curve25519' ];

        $encoded = $this->encrypt_ak($ak, $reader_key);

        $path = $this->uri('v1', 'storage', 'access_keys', $writer_id, $user_id, $reader_id, $type);
        $this->client->request('PUT', $path, ['json' => ['eak' => $encoded]]);

        // Cache the key for later, but only after the PUT has succeeded
        $cache_key = "{$writer_id}.{$user_id}.{$type}";
        $this->ak_cache[ $cache_key ] = $ak;
    }

    /**
     * Delete an access key on the server.
     *
     * @param string $writer_id Writer/Authorizer for the access key
     * @param string $user_id   Record subject
     * @param string $reader_id Authorized reader
     * @param string $type      Record type for which the key will be used
     */
    function delete_access_key(string $writer_id, string $user_id, string $reader_id, string $type)
    {
        $path = $this->uri('v1', 'storage', 'access_keys', $writer_id, $user_id, $reader_id, $type);
        $this->client->request('DELETE', $path);

        // Remove any cached keys
        $cache_key = "{$writer_id}.{$user_id}.{$type}";
        unset($this->ak_cache[ $cache_key ]);
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
     * @param string $path API endpoint to request
     * @param object $obj  Data to be created
     *
     * @return Response PSR7 response object
     */
    function post(string $path, $obj): Response
    {
        return $this->client->request('POST', $path, ['json' => $obj]);
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
     * @param string $path API endpoint to request
     * @param object $obj  Object to be updated
     *
     * @return Response PSR7 response object
     */
    function put(string $path, $obj): Response
    {
        return $this->client->request('PUT', $path, ['json' => $obj]);
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