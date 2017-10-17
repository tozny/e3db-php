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

    public function test_unset_variable()
    {
        $record = new Record(new Meta('179c3fa3-98d9-42e8-8e2c-25a0db59e0ec', '179c3fa3-98d9-42e8-8e2c-25a0db59e0ec', 'test'), ['key' => 'value']);

        // The @ silences the user warning that is otherwise triggered.
        $this->assertNull(@$record->noRealProperty);

        $record->noRealProperty = 'test';
        $this->assertNull(@$record->noRealProperty);
    }

    public function test_decode_error()
    {
        $this->expectException('\Exception');

        Record::decode('[invalid json}');
    }
}