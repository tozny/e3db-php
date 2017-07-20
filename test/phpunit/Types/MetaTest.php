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

class MetaTest extends TestCase
{
    public function test_decoding()
    {
        $json = '{"record_id": "179c3fa3-98d9-42e8-8e2c-25a0db59e0ec", "writer_id": "4a732eb3-de77-4be1-96d1-da6ef8d67f2f", "user_id": "4a732eb3-de77-4be1-96d1-da6ef8d67f2f", "type": "test", "plain": null, "created": "2017-05-04T17:09:26.152645Z", "last_modified": "2017-05-04T17:09:26.152645Z", "version": "65d20c86-b8cf-46fb-8a2d-fc326d2fa984"}';
        $decoded = Meta::decode($json);

        $this->assertEquals('179c3fa3-98d9-42e8-8e2c-25a0db59e0ec', $decoded->record_id);
        $this->assertEquals('4a732eb3-de77-4be1-96d1-da6ef8d67f2f', $decoded->writer_id);
        $this->assertEquals('4a732eb3-de77-4be1-96d1-da6ef8d67f2f', $decoded->user_id);
        $this->assertEquals('test', $decoded->type);
        $this->assertNull($decoded->plain);
        $this->assertEquals('2017-05-04', $decoded->created->format('Y-m-d'));
        $this->assertEquals('2017-05-04', $decoded->last_modified->format('Y-m-d'));
        $this->assertEquals('65d20c86-b8cf-46fb-8a2d-fc326d2fa984', $decoded->version);
    }

    public function test_encoding()
    {
        $meta = new Meta('4a732eb3-de77-4be1-96d1-da6ef8d67f2f', '4a732eb3-de77-4be1-96d1-da6ef8d67f2f', 'blah');

        $encoded = \json_encode($meta);
        $this->assertEquals('{"record_id":null,"writer_id":"4a732eb3-de77-4be1-96d1-da6ef8d67f2f","user_id":"4a732eb3-de77-4be1-96d1-da6ef8d67f2f","type":"blah","plain":null,"created":null,"last_modified":null,"version":null}', $encoded);
    }

    public function test_immutability()
    {
        $meta = new Meta('4a732eb3-de77-4be1-96d1-da6ef8d67f2f', '4a732eb3-de77-4be1-96d1-da6ef8d67f2f', 'blah');

        $thrown = false;
        try {
            $meta->record_id = '179c3fa3-98d9-42e8-8e2c-25a0db59e0ec';
        } catch (ImmutabilityException $ie) {
            $thrown = true;
        }
        $this->assertTrue($thrown);

        $thrown = false;
        try {
            $meta->created = new \DateTime();
        } catch (ImmutabilityException $ie) {
            $thrown = true;
        }
        $this->assertTrue($thrown);

        $thrown = false;
        try {
            $meta->last_modified = new \DateTime();
        } catch (ImmutabilityException $ie) {
            $thrown = true;
        }
        $this->assertTrue($thrown);

        $thrown = false;
        try {
            $meta->version = '179c3fa3-98d9-42e8-8e2c-25a0db59e0ec';
        } catch (ImmutabilityException $ie) {
            $thrown = true;
        }
        $this->assertTrue($thrown);
    }
}