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

namespace Tozny\E3DB\Crypto;

/**
 * Encode a string of bytes in URL-safe Base64.
 *
 * @param string $data
 *
 * @return string
 */
function base64encode(string $data): string
{
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

/**
 * Decode a URL-safe Base64 string as raw bytes
 *
 * @param string $raw
 * @return bool|string
 */
function base64decode(string $raw)
{
    return base64_decode(str_pad(strtr($raw, '-_', '+/'), strlen($raw) % 4, '=', STR_PAD_RIGHT));
}

/**
 * Generate a random nonce for use with Sodium's secretbox abstraction.
 *
 * @return string
 */
function random_nonce(): string
{
    return \random_bytes(\ParagonIE_Sodium_Compat::CRYPTO_SECRETBOX_NONCEBYTES);
}

/**
 * Generate a random Sodium secretbox encryption key.
 *
 * @return string
 */
function random_key(): string
{
    return \random_bytes(\ParagonIE_Sodium_Compat::CRYPTO_SECRETBOX_KEYBYTES);
}