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

namespace Tozny\E3DB;

use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Response;
use function Sodium\crypto_secretbox;
use function Sodium\crypto_secretbox_open;
use Tozny\E3DB\Connection\Connection;
use function Tozny\E3DB\Crypto\base64decode;
use function Tozny\E3DB\Crypto\base64encode;
use function Tozny\E3DB\Crypto\random_key;
use function Tozny\E3DB\Crypto\random_nonce;
use Tozny\E3DB\Exceptions\NotFoundException;
use Tozny\E3DB\Types\ClientInfo;
use Tozny\E3DB\Types\PublicKey;
use Tozny\E3DB\Types\Record;

/**
 * Core client module used to interact with the E3DB API.
 *
 * @package Tozny\E3DB
 */
class Client
{
    private $config;
    private $conn;

    public function __construct( Config $config, Connection $conn )
    {
        $this->config = $config;
        $this->conn = $conn;
    }

    /**
     * Retrieve information about a client, primarily it's UUID and public key,
     * based either on an already-known client ID or a discoverable client
     * email address.
     *
     * @param string $client_id
     *
     * @return ClientInfo
     *
     * @throws \Exception If no client is found, or if a client is undiscoverable via email.
     */
    public function client_info( string $client_id ) : ClientInfo
    {
        try {
            if ( filter_var( $client_id, FILTER_VALIDATE_EMAIL ) ) {
                $info = $this->conn->find_client($client_id);
            } else {
                $info = $this->conn->get_client($client_id);
            }
        } catch (RequestException $re) {
            throw new NotFoundException('Count not retrieve info from the server.', 'client');
        }

        $data = json_decode($info->getBody(), true);

        if (null === $data) {
            throw new \RuntimeException('Error while decoding client info.');
        }

        $info = new ClientInfo();
        $info->client_id = $data['client_id'];
        $info->public_key = new PublicKey();
        $info->public_key->curve25519 = $data['public_key']['curve25519'];
        $info->validated = $data['validated'];

        return $info;
    }

    /**
     * Retrieve the Curve 25519 public key associated with a known client.
     *
     * @param string $client_id
     *
     * @return PublicKey
     */
    public function client_key( string $client_id ) : PublicKey
    {
        if ($this->config->client_id === $client_id) {
            $key = new PublicKey();
            $key->curve25519 = $this->config->public_key;

            return $key;
        }

        return $this->client_info($client_id)->public_key;
    }

    public function read_raw( string $record_id ): Record
    {
        $path = $this->conn->uri( 'v1', 'storage', 'records', $record_id );
        /** @var Response $resp */
        $resp = $this->conn->get( $path );

        $data = json_decode( $resp->getBody(), true );

        if (null === $data) {
            throw new \Exception('Unable to read record!' );
        }

        return Record::new($data);
    }

    public function read( string $record_id ): Record
    {
        return $this->decrypt_record( $this->read_raw( $record_id ) );
    }

    public function write( string $type, array $data, array $plain )
    {

    }

    public function update( Record $record )
    {

    }

    public function delete( string $record_id )
    {
        $path = $this->conn->uri( 'v1', 'storage', 'records', $record_id );
        $this->conn->delete( $path );
    }

    /**
     * Fetch the access key for a record type and use it to decrypt a given record.
     *
     * @param Record $record Record to be decrypted.
     *
     * @return Record
     */
    private function decrypt_record( Record $record )
    {
        $ak = $this->conn->get_access_key(
            $record->meta->writer_id,
            $record->meta->user_id,
            $this->config->client_id,
            $record->meta->type
        );

        return $this->decrypt_record_with_key( $record, $ak );
    }

    /**
     * Create a clone of a given record, but decrypting each field in turn based on
     * the provided access key.
     *
     * @param Record $encrypted Record to be unwrapped
     * @param string $access_key Access key to use for decrypting each data key.
     *
     * @return Record
     */
    private function decrypt_record_with_key( Record $encrypted, string $access_key )
    {
        $decrypted = new Record();
        $decrypted->meta = $encrypted->meta;
        $data = [];

        array_walk( $encrypted->data, function ( $cipher, $key ) use ( $access_key, &$data ) {
            $fields = explode( '.', $cipher );

            $edk = base64decode( $fields[ 0 ] );
            $edkN = base64decode( $fields[ 1 ] );
            $ef = base64decode( $fields[ 2 ] );
            $efN = base64decode( $fields[ 3 ] );

            $dk = crypto_secretbox_open( $edk, $edkN, $access_key );
            $data[ $key ] = crypto_secretbox_open( $ef, $efN, $dk );
        } );

        $decrypted->data = $data;
        return $decrypted;
    }

    /**
     * Create a clone of a plaintext record, encrypting each field in turn with a random
     * data key and protecting the data key with a set access key.
     *
     * @param Record $record Record to be encrypted.
     *
     * @return Record
     */
    private function encrypt_record( Record $record )
    {
        $encrypted = new Record();
        $encrypted->meta = $record->meta;
        $encrypted->data = [];

        $ak = $this->conn->get_access_key(
            $record->meta->writer_id,
            $record->meta->user_id,
            $this->config->client_id,
            $record->meta->type
        );

        if ( null === $ak ) {
            $ak = random_key();
        }

        array_walk( $record->data, function ( $plain, $key ) use ( $ak, &$encrypted ) {
            $dk = random_key();
            $efN = random_nonce();
            $ef = crypto_secretbox( $plain, $efN, $dk );
            $edkN = random_nonce();
            $edk = crypto_secretbox( $dk, $edkN, $ak );

            $encrypted[ $key ] = sprintf( '%s.%s.%s.%s',
                base64encode( $edk ), base64encode( $edkN ),
                base64encode( $ef ), base64encode( $efN )
            );
        } );

        return $encrypted;
    }
}