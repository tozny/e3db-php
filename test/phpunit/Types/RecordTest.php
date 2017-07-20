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

class RecordTest extends TestCase
{
    public function test_decoding()
    {
        $json = '{"meta":{"record_id":"4a732eb3-de77-4be1-96d1-da6ef8d67f2f","writer_id":"179c3fa3-98d9-42e8-8e2c-25a0db59e0ec","user_id":"179c3fa3-98d9-42e8-8e2c-25a0db59e0ec","type":"test","plain":null,"created":"2017-05-04T17:09:26.152645Z","last_modified":"2017-05-04T17:09:26.152645Z","version":"65d20c86-b8cf-46fb-8a2d-fc326d2fa984"},"data":{"key":"value"}}';

        $decoded = Record::decode($json);
        $this->assertArrayHasKey('key', $decoded->data);
        $this->assertEquals('4a732eb3-de77-4be1-96d1-da6ef8d67f2f', $decoded->meta->record_id);
        $this->assertEquals('179c3fa3-98d9-42e8-8e2c-25a0db59e0ec', $decoded->meta->writer_id);
        $this->assertEquals('179c3fa3-98d9-42e8-8e2c-25a0db59e0ec', $decoded->meta->user_id);
        $this->assertEquals('test', $decoded->meta->type);
        $this->assertNull($decoded->meta->plain);
        $this->assertEquals('2017-05-04', $decoded->meta->created->format('Y-m-d'));
        $this->assertEquals('2017-05-04', $decoded->meta->last_modified->format('Y-m-d'));
        $this->assertEquals('65d20c86-b8cf-46fb-8a2d-fc326d2fa984', $decoded->meta->version);
    }

    public function test_encoding()
    {
        $record = new Record(new Meta('179c3fa3-98d9-42e8-8e2c-25a0db59e0ec', '179c3fa3-98d9-42e8-8e2c-25a0db59e0ec', 'test'), ['key' => 'value']);

        $encoded = \json_encode($record);
        $this->assertEquals('{"meta":{"record_id":null,"writer_id":"179c3fa3-98d9-42e8-8e2c-25a0db59e0ec","user_id":"179c3fa3-98d9-42e8-8e2c-25a0db59e0ec","type":"test","plain":null,"created":null,"last_modified":null,"version":null},"data":{"key":"value"}}', $encoded);
    }

    public function test_immutability()
    {
        $record = new Record(new Meta('179c3fa3-98d9-42e8-8e2c-25a0db59e0ec', '179c3fa3-98d9-42e8-8e2c-25a0db59e0ec', 'test'), ['key' => 'value']);

        $thrown = false;
        try {
            $record->meta = new Meta('65d20c86-b8cf-46fb-8a2d-fc326d2fa984', '65d20c86-b8cf-46fb-8a2d-fc326d2fa984', 'test');
        } catch (ImmutabilityException $ie) {
            $thrown = true;
        }
        $this->assertTrue($thrown);
    }
}