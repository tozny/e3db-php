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
 * Objects implementing JsonUnserializable can customize how raw JSON strings
 * are marshaled into object representations, rather than using <b>json_decode</b>
 * to create a <b>stdClass</b> or array instance.
 *
 * @package Tozny\E3DB\Types
 */
interface JsonUnserializable
{
    /**
     * Specify how data should be unserialized from JSON and marshaled into
     * an object representation.
     *
     * @param string $json Raw JSON string to be decoded
     *
     * @return mixed
     */
    static function decode(string $json);

    /**
     * Specify how an already unserialized JSON array should be marshaled into
     * an object representation.
     *
     * @param array $parsed
     *
     * @return mixed
     */
    static function decodeArray(array $parsed);
}