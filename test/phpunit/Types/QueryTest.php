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

use PHPUnit\Framework\TestCase;
use Tozny\E3DB\Exceptions\ImmutabilityException;

class QueryTest extends TestCase
{
    public function test_encoding()
    {
        // All fields
        $query1 = new Query(0, true, '4a732eb3-de77-4be1-96d1-da6ef8d67f2f', '4a732eb3-de77-4be1-96d1-da6ef8d67f2f', 'test', ['meta' => 'exists'], '4a732eb3-de77-4be1-96d1-da6ef8d67f2f', 5, false);

        $encoded = \json_encode($query1);
        $this->assertEquals(
            '{"count":5,"include_data":true,"writer_ids":["4a732eb3-de77-4be1-96d1-da6ef8d67f2f"],"user_ids":["4a732eb3-de77-4be1-96d1-da6ef8d67f2f"],"record_ids":["4a732eb3-de77-4be1-96d1-da6ef8d67f2f"],"content_types":["test"],"plain":{"meta":"exists"},"after_index":0,"include_all_writers":false}',
            $encoded
        );

        // Null fields
        $query2 = new Query();

        $encoded = \json_encode($query2);
        $this->assertEquals('{"count":100,"include_data":false,"after_index":0,"include_all_writers":false}', $encoded);
    }
}