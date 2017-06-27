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

namespace Tozny\E3DB\Crypto;

use const Sodium\CRYPTO_SECRETBOX_KEYBYTES;
use const Sodium\CRYPTO_SECRETBOX_NONCEBYTES;

/**
 * Encode a string of bytes in URL-safe Base64.
 *
 * @param string $data
 *
 * @return string
 */
function base64encode(string $data)
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

function random_nonce()
{
    return \random_bytes(CRYPTO_SECRETBOX_NONCEBYTES);
}

function random_key()
{
    return \random_bytes(CRYPTO_SECRETBOX_KEYBYTES);
}