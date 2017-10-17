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