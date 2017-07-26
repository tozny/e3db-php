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
 * Describe a Curve25519 public key for use in Sodium-powered cryptographic
 * operations.
 *
 * @property-read string $curve25519 Public component of the Curve25519 key.
 *
 * @package Tozny\E3DB\Types
 */
class PublicKey extends JsonUnserializable
{
    use Accessor;

    /**
     * @var string Public component of the Curve25519 key.
     */
    protected $_curve25519;

    protected $immutableFields = ['curve25519'];

    public function __construct(string $curve25519)
    {
        $this->_curve25519 = $curve25519;
    }

    /**
     * Serialize the object to JSON
     */
    public function jsonSerialize(): array
    {
        return [
            'curve25519'  => $this->_curve25519,
        ];
    }

    /**
     * Specify how an already unserialized JSON array should be marshaled into
     * an object representation.
     *
     * The public key component of a Curve25519 key alone is serialized for transmission between
     * various parties.
     *
     * <code>
     * $key = PublicKey::decodeArray([
     *   'curve25519' => ''
     * ]);
     * </code>
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