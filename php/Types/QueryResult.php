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

namespace Tozny\E3DB\Types;

use GuzzleHttp\Exception\RequestException;
use Tozny\E3DB\Client;


/**
 * Describe a query result returned from E3DB API.
 *
 * @package Tozny\E3DB\Types
 */
class QueryResult implements \Iterator
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

    public function __construct(Client $client, Query $query, bool $raw)
    {
        $this->client = $client;
        $this->query = $query;
        $this->raw = $raw;

        // Execute the query and store the results internally

    }

    protected function results(Query $query)
    {
        $path = $this->client->conn->uri('v1', 'storage', 'search');
        try {
            $response = $this->client->conn->post($path, $query);
        } catch (RequestException $re) {
            throw new \RuntimeException('Error sending query data to the API!');
        }

        $data = \json_decode((string) $response->getBody());
        var_dump($data);
    }

    /**
     * Return the current element
     * @link http://php.net/manual/en/iterator.current.php
     * @return mixed Can return any type.
     * @since 5.0.0
     */
    public function current()
    {
        // TODO: Implement current() method.
    }

    /**
     * Move forward to next element
     * @link http://php.net/manual/en/iterator.next.php
     * @return void Any returned value is ignored.
     * @since 5.0.0
     */
    public function next()
    {
        // TODO: Implement next() method.
    }

    /**
     * Return the key of the current element
     * @link http://php.net/manual/en/iterator.key.php
     * @return mixed scalar on success, or null on failure.
     * @since 5.0.0
     */
    public function key()
    {
        // TODO: Implement key() method.
    }

    /**
     * Checks if current position is valid
     * @link http://php.net/manual/en/iterator.valid.php
     * @return boolean The return value will be casted to boolean and then evaluated.
     * Returns true on success or false on failure.
     * @since 5.0.0
     */
    public function valid()
    {
        // TODO: Implement valid() method.
    }

    /**
     * Rewind the Iterator to the first element
     * @link http://php.net/manual/en/iterator.rewind.php
     * @return void Any returned value is ignored.
     * @since 5.0.0
     */
    public function rewind()
    {
        // TODO: Implement rewind() method.
    }

}