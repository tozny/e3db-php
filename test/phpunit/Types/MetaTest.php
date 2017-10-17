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

    public function test_unset_variable()
    {
        $meta = new Meta('4a732eb3-de77-4be1-96d1-da6ef8d67f2f', '4a732eb3-de77-4be1-96d1-da6ef8d67f2f', 'blah');

        // The @ silences the user warning that is otherwise triggered.
        $this->assertNull(@$meta->noRealProperty);

        $meta->noRealProperty = 'test';
        $this->assertNull(@$meta->noRealProperty);
    }

    public function test_decode_error()
    {
        $this->expectException('\Exception');

        Meta::decode('[invalid json}');
    }
}