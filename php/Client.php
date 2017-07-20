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
use function Sodium\crypto_secretbox;
use function Sodium\crypto_secretbox_open;
use Tozny\E3DB\Connection\Connection;
use function Tozny\E3DB\Crypto\base64decode;
use function Tozny\E3DB\Crypto\base64encode;
use function Tozny\E3DB\Crypto\random_key;
use function Tozny\E3DB\Crypto\random_nonce;
use Tozny\E3DB\Exceptions\ConflictException;
use Tozny\E3DB\Exceptions\NotFoundException;
use Tozny\E3DB\Types\ClientInfo;
use Tozny\E3DB\Types\Meta;
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

    public function __construct(Config $config, Connection $conn)
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
     * @throws NotFoundException If no client is found, or if a client is undiscoverable via email.
     * @throws \RuntimeException If there is an error deserializing the data from the server
     */
    public function client_info(string $client_id): ClientInfo
    {
        try {
            if (filter_var($client_id, FILTER_VALIDATE_EMAIL)) {
                $info = $this->conn->find_client($client_id);
            } else {
                $info = $this->conn->get_client($client_id);
            }
        } catch (RequestException $re) {
            throw new NotFoundException('Count not retrieve info from the server.', 'client');
        }

        return ClientInfo::decode($info->getBody());
    }

    /**
     * Retrieve the Curve 25519 public key associated with a known client.
     *
     * @param string $client_id
     *
     * @return PublicKey
     */
    public function client_key(string $client_id): PublicKey
    {
        if ($this->config->client_id === $client_id) {
            return new PublicKey($this->config->public_key);
        }

        return $this->client_info($client_id)->public_key;
    }

    /**
     * Read a raw record from the E3DB system and return it, still encrypted, to the
     * original requester.
     *
     * @param string $record_id
     *
     * @return Record
     *
     * @throws NotFoundException If no record is found or if the record is unreadable.
     * @throws \RuntimeException If there is an error deserializing the data from the server.
     */
    public function read_raw(string $record_id): Record
    {
        $path = $this->conn->uri('v1', 'storage', 'records', $record_id);

        try {
            $resp = $this->conn->get($path);
        } catch (RequestException $re) {
            throw new NotFoundException('Count not retrieve data from the server.', 'record');
        }

        return Record::decode($resp->getBody());
    }

    /**
     * Reads a record from the E3DB system and decrypts it automatically.
     *
     * @param string $record_id
     *
     * @return Record
     */
    public function read(string $record_id): Record
    {
        return $this->decrypt_record($this->read_raw($record_id));
    }

    /**
     * Create a new record entry with E3DB.
     *
     * @param string $type The content type with which to associate the record.
     * @param array $data A hashmap of the data to encrypt and store
     * @param array|null [$plain] Optional hashmap of data to store with the record's meta in plaintext.
     *
     * @return Record
     *
     * @throws \RuntimeException If there is an error while persisting the data with E3DB.
     */
    public function write(string $type, array $data, array $plain = null): Record
    {
        $path = $this->conn->uri('v1', 'storage', 'records');
        $writer = $this->config->client_id;

        // Build up the record
        $meta = new Meta($writer, $writer, $type, $plain);

        $record = new Record($meta, $data);

        try {
            $resp = $this->conn->post($path, $this->encrypt_record($record));
        } catch (RequestException $re) {
            throw new \RuntimeException('Error while writing record data!');
        }

        return $this->decrypt_record(Record::decode($resp->getBody()));
    }

    /**
     * Update a record, with optimistic concurrent locking, that already exists in the E3DB system.
     *
     * @param Record $record Record to be updated.
     *
     * @throws ConflictException If the version ID in the record does not match the latest version stored on the server.
     */
    public function update(Record $record): void
    {
        $record_id = $record->meta->record_id;
        $version = $record->meta->version;

        $path = $this->conn->uri('v1', 'storage', 'records', 'safe', $record_id, $version);
        try {
            $this->conn->put($path, $this->encrypt_record($record));
        } catch (RequestException $re) {
            if ($re->getResponse()->getStatusCode() === 409) {
                throw new ConflictException("Conflict updating record ID {$record_id}", 'record');
            }

            throw $re;
        }
    }

    /**
     * Deletes a record from the E3DB system
     *
     * @param string $record_id
     */
    public function delete(string $record_id): void
    {
        $path = $this->conn->uri('v1', 'storage', 'records', $record_id);
        try {
            $this->conn->delete($path);
        } catch (RequestException $re) {
            switch ($re->getResponse()->getStatusCode()) {
                case 404:
                case 410:
                    // If the record never existed, or is already missing, return
                    return;
                default:
                    // Something else went wrong!
                    throw new \RuntimeException('Error while deleting record data!');
                    break;
            }
        }
    }

    /**
     * Fetch the access key for a record type and use it to decrypt a given record.
     *
     * @param Record $record Record to be decrypted.
     *
     * @return Record
     */
    private function decrypt_record(Record $record): Record
    {
        $ak = $this->conn->get_access_key(
            $record->meta->writer_id,
            $record->meta->user_id,
            $this->config->client_id,
            $record->meta->type
        );

        return $this->decrypt_record_with_key($record, $ak);
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
    private function decrypt_record_with_key(Record $encrypted, string $access_key): Record
    {
        $data = [];

        array_walk($encrypted->data, function ($cipher, $key) use ($access_key, &$data) {
            $fields = explode('.', $cipher);

            $edk = base64decode($fields[ 0 ]);
            $edkN = base64decode($fields[ 1 ]);
            $ef = base64decode($fields[ 2 ]);
            $efN = base64decode($fields[ 3 ]);

            $dk = crypto_secretbox_open($edk, $edkN, $access_key);
            $data[ $key ] = crypto_secretbox_open($ef, $efN, $dk);
        });

        return new Record($encrypted->meta, $data);
    }

    /**
     * Create a clone of a plaintext record, encrypting each field in turn with a random
     * data key and protecting the data key with a set access key.
     *
     * @param Record $record Record to be encrypted.
     *
     * @return Record
     */
    private function encrypt_record(Record $record): Record
    {
        $data = [];

        try {
            $ak = $this->conn->get_access_key(
                $record->meta->writer_id,
                $record->meta->user_id,
                $this->config->client_id,
                $record->meta->type
            );
        } catch (RequestException $re) {
            switch ($re->getResponse()->getStatusCode()) {
                case 404:
                    // Create a random AK
                    $ak = random_key();

                    // Store the AK with the system
                    $this->conn->put_access_key(
                        $record->meta->writer_id,
                        $record->meta->user_id,
                        $this->config->client_id,
                        $record->meta->type,
                        $ak
                    );
                    break;
                default:
                    throw new \RuntimeException('Error while retrieving access keys!');
            }
        }

        array_walk($record->data, function ($plain, $key) use ($ak, &$data) {
            $dk = random_key();
            $efN = random_nonce();
            $ef = crypto_secretbox($plain, $efN, $dk);
            $edkN = random_nonce();
            $edk = crypto_secretbox($dk, $edkN, $ak);

            $data[ $key ] = sprintf('%s.%s.%s.%s',
                base64encode($edk), base64encode($edkN),
                base64encode($ef), base64encode($efN)
            );
        });

        return new Record($record->meta, $data);
    }
}