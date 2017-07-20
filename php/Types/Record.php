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
class Record implements \JsonSerializable, JsonUnserializable
{
    /**
     * @var Meta Meta information about the record.
     */
    protected $_meta;

    /**
     * @var array The record's application-specific data.
     */
    public $data;

    public function __construct( Meta $meta, array $data )
    {
        $this->_meta = $meta;
        $this->data = $data;
    }

    /**
     * Magic getter to retrieve read-only properties.
     *
     * @param string $name Property name to retrieve
     *
     * @return mixed
     */
    public function __get( string $name )
    {
        $key = "_{$name}";
        if (property_exists($this, $key)) {
            return $this->$key;
        }

        trigger_error( "Undefined property: Record::{$name}", E_USER_NOTICE );
        return null;
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
     * Specify how data should be unserialized from JSON and marshaled into
     * an object representation.
     *
     * @param string $json Raw JSON string to be decoded
     *
     * @return Record
     *
     * @throws \Exception
     */
    public static function decode( string $json ): Record
    {
        $data = \json_decode( $json, true );

        if ( null === $data ) {
            throw new \Exception( 'Error decoding Record JSON' );
        }

        return self::decodeArray( $data );
    }

    /**
     * Specify how an already unserialized JSON array should be marshaled into
     * an object representation.
     *
     * @param array $parsed
     *
     * @return Record
     */
    public static function decodeArray( array $parsed ): Record
    {
        return new Record( Meta::decodeArray( $parsed[ 'meta' ] ), $parsed[ 'data' ] );
    }
}