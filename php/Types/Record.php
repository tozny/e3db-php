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

/**
 * A E3DB record containing data and metadata. Records are
 * a key/value mapping containing data serialized
 * into strings. All records are encrypted prior to sending them
 * to the server for storage, and decrypted in the client after
 * they are read.
 *
 * @property-read Meta $meta Meta information about the record.
 *
 * @package Tozny\E3DB\Types
 */
class Record extends JsonUnserializable
{
    use Accessor;

    /**
     * @var Meta Meta information about the record.
     */
    protected $_meta;

    /**
     * @var array The record's application-specific data.
     */
    public $data;

    /**
     * @var array Fields that cannot be overwritten externally.
     */
    protected $immutableFields = ['meta'];

    public function __construct(Meta $meta, $data)
    {
        $this->_meta = $meta;
        $this->data  = $data;
    }

    /**
     * Serialize the object to JSON
     */
    public function jsonSerialize(): array
    {
        return [
            'meta' => $this->_meta,
            'data' => $this->data
        ];
    }

    /**
     * Specify how an already unserialized JSON array should be marshaled into
     * an object representation.
     *
     * Records consist of two elements, meta and data. The array we deserialize into a Record instance
     * must match this format. The meta element is itself an array representing the Meta class. The
     * data element is a simpler array mapping string keys to either encrypted or plaintext string values.
     *
     * <code>
     * $record = Record::decodeArray([
     *   'meta' => [
     *     'record_id'     => '',
     *     'writer_id'     => '',
     *     'user_id'       => '',
     *     'type'          => '',
     *     'plain'         => [],
     *     'created'       => ''
     *     'last_modified' => ''
     *     'version'       => ''
     *   ],
     *   'data' => [
     *     'key' => 'value',
     *     'key' => 'value'
     *   ]
     * ]);
     * </code>
     *
     * @see \Tozny\E3DB\Types\Meta::decodeArray()
     *
     * @param array[string]string $parsed
     *
     * @return Record
     */
    public static function decodeArray(array $parsed): Record
    {
        return new Record(Meta::decodeArray($parsed[ 'meta' ]), $parsed[ 'data' ]);
    }
}