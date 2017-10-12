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

use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Tozny\E3DB\Config;
use function Tozny\E3DB\Crypto\random_key;
use Tozny\E3DB\Types\Record;

class ConnectionTest extends TestCase
{
    public function test_uri()
    {
        $conn = new ConcreteConnection();

        $uri = $conn->uri('v1', 'test', 'path', 'UUID');
        $this->assertEquals('http://localhost/v1/test/path/UUID', $uri);
    }

    public function test_ak_crypto()
    {
        $conn = new ConcreteConnection();

        $ak = base64_decode('QJv5wmdRqlTzpxE1ehSWQ6X4Zgn2K0i1nzaYwbi/SxY='); // Random, pre-generated AK
        $reader_key = 'mRcJWM6Fe30w48Dej5ZF_HjasIIRLQVR6Rzn4HJOGTs';

        $encrypted = $conn->encrypt_ak($ak, $reader_key);

        // Now decrypt what we just encrypted ... it uses a random nonce, so no way to do this deterministically ...
        $decrypted = $conn->decrypt_eak([
            'authorizer_public_key' => ['curve25519' => 'mRcJWM6Fe30w48Dej5ZF_HjasIIRLQVR6Rzn4HJOGTs'],
            'eak'                   => $encrypted,
        ]);

        $this->assertEquals($ak, $decrypted);
    }
}

/**
 * Stub test class to verify concrete methods within the object.
 *
 * @package Tozny\E3DB\Connection
 */
class ConcreteConnection extends Connection
{
    public function __construct()
    {
        $config = new Config('', '', '', '', 'IjgGxAj4bKu6-PMshy6QUYAyLfQiZ3ZAP8KigAq8hB0', 'http://localhost');

        parent::__construct($config);
    }

    function get_access_key(string $writer_id, string $user_id, string $reader_id, string $type)
    {
        // TODO: Implement get_access_key() method.
    }

    function put_access_key(string $writer_id, string $user_id, string $reader_id, string $type, string $ak): void
    {
        // TODO: Implement put_access_key() method.
    }

    function delete_access_key(string $writer_id, string $user_id, string $reader_id, string $type)
    {
        // TODO: Implement delete_access_key() method.
    }

    function get_client(string $client_id): Response
    {
        // TODO: Implement get_client() method.
    }

    function post(string $path, $obj): Response
    {
        // TODO: Implement post() method.
    }

    function get(string $path): Response
    {
        // TODO: Implement get() method.
    }

    function put(string $path, $obj): Response
    {
        // TODO: Implement put() method.
    }

    function delete(string $path): Response
    {
        // TODO: Implement delete() method.
    }

    public function decrypt_eak(array $json): string
    {
        return parent::decrypt_eak($json);
    }

    public function encrypt_ak(string $ak, string $reader_key): string
    {
        return parent::encrypt_ak($ak, $reader_key);
    }
}