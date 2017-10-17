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

use GuzzleHttp\Psr7\Response;
use const Sodium\CRYPTO_BOX_NONCEBYTES;
use Tozny\E3DB\Config;
use function Tozny\E3DB\Crypto\base64decode;
use function Tozny\E3DB\Crypto\base64encode;
use Tozny\E3DB\Types\Record;

abstract class Connection
{
    /**
     * @var Config Configuration container for API references
     */
    protected $config;

    /**
     * @var array Cache of known access keys
     */
    protected $ak_cache = [];

    public function __construct(Config $config)
    {
        $this->config = $config;
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
    abstract function get_access_key(string $writer_id, string $user_id, string $reader_id, string $type);

    /**
     * Create an access key on the server.
     *
     * @param string $writer_id Writer/Authorizer for the access key
     * @param string $user_id   Record subject
     * @param string $reader_id Authorized reader
     * @param string $type      Record type for which the key will be used
     * @param string $ak        Unencrypted access key
     */
    abstract function put_access_key(string $writer_id, string $user_id, string $reader_id, string $type, string $ak);

    /**
     * Delete an access key on the server.
     *
     * @param string $writer_id Writer/Authorizer for the access key
     * @param string $user_id   Record subject
     * @param string $reader_id Authorized reader
     * @param string $type      Record type for which the key will be used
     */
    abstract function delete_access_key(string $writer_id, string $user_id, string $reader_id, string $type);

    /**
     * Get a client's information based on their ID.
     *
     * @param string $client_id
     *
     * @return Response PSR7 response object
     */
    abstract function get_client(string $client_id): Response;

    /**
     * Create a new object with E3DB
     *
     * @param string $path API endpoint to request
     * @param object $obj  Record to be created
     *
     * @return Response PSR7 response object
     */
    abstract function post(string $path, $obj): Response;

    /**
     * Retrieve an object from E3DB
     *
     * @param string $path API endpoint to request
     *
     * @return Response PSR7 response object
     */
    abstract function get(string $path): Response;

    /**
     * Update an object with E3DB
     *
     * @param string $path API endpoint to request
     * @param object $obj  Object to be updated
     *
     * @return Response PSR7 response object
     */
    abstract function put(string $path, $obj): Response;

    /**
     * Delete an object from E3DB
     *
     * @param string $path API endpoint to request
     *
     * @return Response PSR7 response object
     */
    abstract function delete(string $path): Response;

    /**
     * Build up a URL based on path parameters
     *
     * @param \string[] ...$parts
     *
     * @return string
     */
    public function uri(string ...$parts): string
    {
        return $this->config->api_url . '/' . implode('/', $parts);
    }

    /**
     * Decrypt the access key provided for a specific reader so it can be used
     * to further decrypt a protected record.
     *
     * @param array $json
     *
     * @return string Raw binary string of the access key
     */
    public function decrypt_eak(array $json): string
    {
        $key = $json[ 'authorizer_public_key' ][ 'curve25519' ];
        $public_key = base64decode($key);
        $private_key = base64decode($this->config->private_key);

        $fields = explode('.', $json[ 'eak' ]);
        $ciphertext = base64decode($fields[ 0 ]);
        $nonce = base64decode($fields[ 1 ]);

        // Build keypair
        $keypair = \ParagonIE_Sodium_Compat::crypto_box_keypair_from_secretkey_and_publickey($private_key, $public_key);

        return \ParagonIE_Sodium_Compat::crypto_box_open($ciphertext, $nonce, $keypair);
    }

    /**
     * Encrypt an access key for a given reader.
     *
     * @param string $ak Raw binary string of the access key
     * @param string $reader_key Base64url-encoded public key of the reader
     *
     * @return string Encrypted and encoded access key.
     */
    protected function encrypt_ak(string $ak, string $reader_key): string
    {
        $public_key = base64decode($reader_key);
        $private_key = base64decode($this->config->private_key);

        // Build keypair
        $keypair = \ParagonIE_Sodium_Compat::crypto_box_keypair_from_secretkey_and_publickey($private_key, $public_key);

        $nonce = \random_bytes(CRYPTO_BOX_NONCEBYTES);
        $eak = \ParagonIE_Sodium_Compat::crypto_box($ak, $nonce, $keypair);

        return sprintf('%s.%s', base64encode($eak), base64encode($nonce));
    }
}