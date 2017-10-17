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
 * Full nformation about a specific E3DB client, including the client's
 * public/private keys for cryptographic operations and API credentials.
 *
 * @property-read string    $client_id  UUID representing the client.
 * @property-read string    $api_key_id API key to be used when authenticating with e3db
 * @property-read string    $api_secret API password to be used when authenticating with e3db
 * @property-read PublicKey $public_key Curve 25519 public key for the client.
 * @property-read string    $name       Description of the client
 *
 * @package Tozny\E3DB\Types
 */
class ClientDetails extends JsonUnserializable
{
    use Accessor;

    /**
     * @var string UUID representing the client.
     */
    protected $_client_id;

    /**
     * @var string API key to be used when authenticating with e3db
     */
    protected $_api_key_id;

    /**
     * @var string API password to be used when authenticating with e3db
     */
    protected $_api_secret;

    /**
     * @var PublicKey Curve 25519 public key for the client.
     */
    protected $_public_key;

    /**
     * @var string Description of the client
     */
    protected $_name;

    /**
     * @var array Fields that cannot be overwritten externally.
     */
    protected $immutableFields = ['client_id', 'api_key_id', 'api_secret', 'public_key', 'name'];

    /**
     * Constructor is private as this object cannot and should not be instantiated outside of
     * deserialization.
     *
     * @param string    $client_id
     * @param string    $api_key_id
     * @param string    $api_secret
     * @param PublicKey $public_key
     * @param string    $name
     */
    private function __construct(string $client_id, string $api_key_id, string $api_secret, PublicKey $public_key, string $name)
    {
        $this->_client_id = $client_id;
        $this->_api_key_id = $api_key_id;
        $this->_api_secret = $api_secret;
        $this->_public_key = $public_key;
        $this->_name = $name;
    }

    /**
     * Serialize the object to JSON
     */
    public function jsonSerialize(): array
    {
        return [
            'client_id'  => $this->_client_id,
            'api_key_id' => $this->_api_key_id,
            'api_secret' => $this->_api_secret,
            'public_key' => $this->_public_key,
            'name'       => $this->_name,
        ];
    }

    /**
     * Specify how an already unserialized JSON array should be marshaled into
     * an object representation.
     *
     * Client information contains the ID of the client, API credentials for interacting
     * with the e3db server, a Curve25519 public key component, and a description of the
     * client as specified during creation.
     *
     * <code>
     * $info = ClientDetails::decodeArray([
     *   'client_id'  => '',
     *   'api_key_id' => '',
     *   'api_secret' => '',
     *   'public_key' => [
     *     'curve25519' => ''
     *   ],
     *   'name'       => ''
     * ]);
     * <code>
     *
     * @see \Tozny\E3DB\Types\PublicKey::decodeArray()
     *
     * @param array $parsed
     *
     * @return ClientDetails
     */
    public static function decodeArray(array $parsed): ClientDetails
    {
        $details = new ClientDetails(
            $parsed['client_id'],
            $parsed['api_key_id'],
            $parsed['api_secret'],
            PublicKey::decodeArray($parsed['public_key']),
            $parsed['name']
        );

        return $details;
    }
}