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
 * Objects implementing JsonUnserializable can customize how raw JSON strings
 * are marshaled into object representations, rather than using <b>json_decode</b>
 * to create a <b>stdClass</b> or array instance.
 *
 * @package Tozny\E3DB\Types
 */
abstract class JsonUnserializable implements \JsonSerializable
{
    /**
     * Specify how data should be unserialized from JSON and marshaled into
     * an object representation.
     *
     * @param string $json Raw JSON string to be decoded
     *
     * @return mixed
     *
     * @throws \Exception
     */
    public static function decode(string $json)
    {
        $data = \json_decode($json, true);

        if (null === $data) {
            throw new \Exception(sprintf('Error decoding %s JSON', static::class));
        }

        return static::decodeArray($data);
    }

    /**
     * Specify how an already unserialized JSON array should be marshaled into
     * an object representation.
     *
     * @param array[string]string $parsed
     *
     * @return mixed
     */
    abstract static function decodeArray(array $parsed);
}