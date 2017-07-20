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
 * Describe a Curve25519 public key for use in Sodium-powered cryptographic
 * operations.
 *
 * @property-read string $curve25519 Public component of the Curve25519 key.
 *
 * @package Tozny\E3DB\Types
 */
class PublicKey
{
    /**
     * @var string Public component of the Curve25519 key.
     */
    protected $_curve25519;

    public function __construct(string $curve25519)
    {
        $this->_curve25519 = $curve25519;
    }

    /**
     * Magic getter to retrieve read-only properties.
     *
     * @param string $name Property name to retrieve
     *
     * @return mixed
     */
    public function __get(string $name)
    {
        $key = "_{$name}";
        if (property_exists($this, $key)) {
            return $this->$key;
        }

        trigger_error("Undefined property: PublicKey::{$name}", E_USER_NOTICE);
        return null;
    }

    /**
     * Specify how data should be unserialized from JSON and marshaled into
     * an object representation.
     *
     * @param string $json Raw JSON string to be decoded
     *
     * @return PublicKey
     *
     * @throws \Exception
     */
    public static function decode(string $json): PublicKey
    {
        $data = \json_decode($json, true);

        if (null === $data) {
            throw new \Exception('Error decoding PublicKey JSON');
        }

        return self::decodeArray($data);
    }

    /**
     * Specify how an already unserialized JSON array should be marshaled into
     * an object representation.
     *
     * @param array $parsed
     *
     * @return PublicKey
     */
    public static function decodeArray(array $parsed): PublicKey
    {
        return new PublicKey($parsed[ 'curve25519' ]);
    }
}