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