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

    abstract function get_access_key(string $writer_id, string $user_id, string $reader_id, string $type);

    abstract function put_access_key(string $writer_id, string $user_id, string $reader_id, string $type, string $ak);

    abstract function find_client(string $email): Response;

    abstract function get_client(string $client_id): Response;

    abstract function post(string $path, Record $record): Response;

    abstract function get(string $path): Response;

    abstract function put(string $path, Record $record): Response;

    abstract function delete(string $path): Response;

    /**
     * Build up a URL based on path parameters
     *
     * @param \string[] ...$parts
     *
     * @return string
     */
    function uri(string ...$parts): string
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
    protected function decrypt_eak(array $json): string
    {
        $key = $json[ 'authorizer_public_key' ][ 'curve25519' ];
        $public_key = base64decode($key);
        $private_key = base64decode($this->config->private_key);

        $fields = explode('.', $json[ 'eak' ]);
        $ciphertext = base64decode($fields[ 0 ]);
        $nonce = base64decode($fields[ 1 ]);

        // Build keypair
        $keypair = sodium_crypto_box_keypair_from_secretkey_and_publickey($private_key, $public_key);

        return sodium_crypto_box_open($ciphertext, $nonce, $keypair);
    }

    /**
     * Encrypt an access key for a given reader.
     *
     * @param string $ak Raw binary string of the access key
     * @param string $reader_key Base64url-encoded public key of the reader
     *
     * @return string Encryted and encoded access key.
     */
    protected function encrypt_ak(string $ak, string $reader_key): string
    {
        $public_key = base64decode($reader_key);
        $private_key = base64decode($this->config->private_key);

        // Build keypair
        $keypair = sodium_crypto_box_keypair_from_secretkey_and_publickey($private_key, $public_key);

        $nonce = \random_bytes(CRYPTO_BOX_NONCEBYTES);
        $eak = sodium_crypto_box($ak, $nonce, $keypair);

        return sprintf('%s.%s', base64encode($eak), base64encode($nonce));
    }
}