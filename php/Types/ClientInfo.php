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
 * Information about a specific E3DB client, including the client's
 * public key to be used for cryptographic operations.
 *
 * @property-read string $client_id  UUID representing the client.
 * @property-read PublicKey $public_key Curve 25519 public key for the client.
 * @property-read bool $validated  Flag whether or not the client has been validated.
 *
 * @package Tozny\E3DB\Types
 */
class ClientInfo extends JsonUnserializable
{
    use Accessor;

    /**
     * @var string UUID representing the client.
     */
    protected $_client_id;

    /**
     * @var PublicKey Curve 25519 public key for the client.
     */
    protected $_public_key;

    /**
     * @var bool Flag whether or not the client has been validated.
     */
    protected $_validated;

    /**
     * @var array Fields that cannot be overwritten externally.
     */
    protected $immutableFields = ['client_id', 'public_key', 'validated'];

    public function __construct(string $client_id, PublicKey $public_key, bool $validated)
    {
        $this->_client_id  = $client_id;
        $this->_public_key = $public_key;
        $this->_validated  = $validated;
    }

    /**
     * Serialize the object to JSON
     */
    public function jsonSerialize(): array
    {
        return [
            'client_id'  => $this->_client_id,
            'public_key' => $this->_public_key,
            'validated'  => $this->_validated,
        ];
    }

    /**
     * Specify how an already unserialized JSON array should be marshaled into
     * an object representation.
     *
     * Client information contains the ID of the client, a Curve25519 public key
     * component, and a flag describing whether or not the client has been validated.
     *
     * <code>
     * $info = ClientInfo::decodeArray([
     *   'client_id'  => '',
     *   'public_key' => [
     *     'curve25519' => ''
     *   ],
     *   'validated'  => true
     * ]);
     * <code>
     *
     * @see \Tozny\E3DB\Types\PublicKey::decodeArray()
     *
     * @param array $parsed
     *
     * @return ClientInfo
     */
    public static function decodeArray(array $parsed): ClientInfo
    {
        $info = new ClientInfo(
            $parsed[ 'client_id' ],
            PublicKey::decodeArray($parsed[ 'public_key' ]),
            $parsed[ 'validated' ]
        );

        return $info;
    }
}