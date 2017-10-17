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

namespace Tozny\E3DB\Types;

use GuzzleHttp\Exception\RequestException;
use Tozny\E3DB\Client;


/**
 * Describe a query result returned from E3DB API.
 *
 * @package Tozny\E3DB\Types
 */
class QueryResult implements \Iterator, \Countable, \ArrayAccess
{
    /**
     * @var Client E3DB client instance for performing actual queries and crypto work.
     */
    private $client;

    /**
     * @var Query The query being performed against the E3DB API
     */
    private $query;

    /**
     * @var bool Flag whether or not to decrypt data while iterating
     */
    private $raw;

    private $position = 0;

    private $data = [];

    public function __construct(Client $client, Query $query, bool $raw)
    {
        $this->client = $client;
        $this->query = $query;
        $this->raw = $raw;

        // Execute the query and store the results internally
        $this->data = $this->results($query);
    }

    protected function results(Query $query)
    {
        $path = $this->client->conn->uri('v1', 'storage', 'search');
        try {
            $response = $this->client->conn->post($path, $query);
        } catch (RequestException $re) {
            throw new \RuntimeException('Error sending query data to the API!');
        }

        $data = \json_decode((string) $response->getBody(), true);

        return \array_map(function($result) use ($query) {
            $record = new Record(Meta::decodeArray($result['meta']), $result['record_data']);

            if ($query->include_data && ! $this->raw) {
                $eak = $result['access_key'];
                $access_key = $this->client->conn->decrypt_eak($eak);

                $record = $this->client->decrypt_record_with_key($record, $access_key);
            }

            return $record;
        }, $data['results']);
    }

    /**
     * Return the current result
     *
     * @link http://php.net/manual/en/iterator.current.php
     *
     * @return Record
     */
    public function current(): Record
    {
        return $this->data[$this->position];
    }

    /**
     * Move forward to next result
     *
     * @link http://php.net/manual/en/iterator.next.php
     */
    public function next()
    {
        ++$this->position;
    }

    /**
     * Return the key of the current result
     *
     * @link http://php.net/manual/en/iterator.key.php
     *
     * @return mixed scalar on success, or null on failure.
     */
    public function key()
    {
        return $this->position;
    }

    /**
     * Checks if current position is valid
     *
     * @link http://php.net/manual/en/iterator.valid.php
     *
     * @return boolean True on success or false on failure.
     */
    public function valid(): bool
    {
        return isset($this->data[$this->position]);
    }

    /**
     * Rewind the Iterator to the first record
     *
     * @link http://php.net/manual/en/iterator.rewind.php
     */
    public function rewind()
    {
        $this->position = 0;
    }

    /**
     * Count records in the result set.
     *
     * @link http://php.net/manual/en/countable.count.php
     *
     * @return int The custom count as an integer.
     */
    public function count()
    {
        return count($this->data);
    }

    /**
     * Whether a offset exists
     *
     * @link http://php.net/manual/en/arrayaccess.offsetexists.php
     *
     * @param mixed $offset An offset to check for.
     * @return boolean true on success or false on failure.
     */
    public function offsetExists($offset): bool
    {
        return isset($this->data[$offset]);
    }

    /**
     * Offset to retrieve
     *
     * @link http://php.net/manual/en/arrayaccess.offsetget.php
     *
     * @param mixed $offset The offset to retrieve.
     *
     * @return Record
     */
    public function offsetGet($offset): Record
    {
        return $this->data[$offset];
    }

    /**
     * Offset to set
     *
     * @link http://php.net/manual/en/arrayaccess.offsetset.php
     *
     * @param int $offset The offset to assign the value to.
     *
     * @param Record $value The value to set.
     */
    public function offsetSet($offset, $value)
    {
        $this->data[$offset] = $value;
    }

    /**
     * Offset to unset
     *
     * @link http://php.net/manual/en/arrayaccess.offsetunset.php
     *
     * @param mixed $offset The offset to unset.
     */
    public function offsetUnset($offset)
    {
        unset($this->data[$offset]);
    }


}