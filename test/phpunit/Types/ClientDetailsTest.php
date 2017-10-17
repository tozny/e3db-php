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

class ClientDetailsTest extends TestCase
{
    public function test_decoding()
    {
        $json = '{"client_id": "1234", "api_key_id": "abcdefg", "api_secret": "123456", "public_key": {"curve25519": "mRcJWM6Fe30w48Dej5ZF_HjasIIRLQVR6Rzn4HJOGTs"}, "name": "test"}';
        $decoded = ClientDetails::decode($json);

        $this->assertEquals('1234', $decoded->client_id);
        $this->assertEquals('abcdefg', $decoded->api_key_id);
        $this->assertEquals('123456', $decoded->api_secret);
        $this->assertEquals('mRcJWM6Fe30w48Dej5ZF_HjasIIRLQVR6Rzn4HJOGTs', $decoded->public_key->curve25519);
        $this->assertEquals('test', $decoded->name);
    }

    public function test_encoding()
    {
        $json = '{"client_id": "1234", "api_key_id": "abcdefg", "api_secret": "123456", "public_key": {"curve25519": "mRcJWM6Fe30w48Dej5ZF_HjasIIRLQVR6Rzn4HJOGTs"}, "name": "test"}';
        $details = ClientDetails::decode($json);

        $encoded = \json_encode($details);
        $this->assertEquals('{"client_id":"1234","api_key_id":"abcdefg","api_secret":"123456","public_key":{"curve25519":"mRcJWM6Fe30w48Dej5ZF_HjasIIRLQVR6Rzn4HJOGTs"},"name":"test"}', $encoded);
    }

    public function test_immutability()
    {
        $json = '{"client_id": "1234", "api_key_id": "abcdefg", "api_secret": "123456", "public_key": {"curve25519": "mRcJWM6Fe30w48Dej5ZF_HjasIIRLQVR6Rzn4HJOGTs"}, "name": "test"}';
        $details = ClientDetails::decode($json);

        $thrown = false;
        try {
            $details->client_id = 'dddd';
        } catch (ImmutabilityException $ie) {
            $thrown = true;
        }
        $this->assertTrue($thrown);

        $thrown = false;
        try {
            $details->api_key_id = 'blah';
        } catch (ImmutabilityException $ie) {
            $thrown = true;
        }
        $this->assertTrue($thrown);

        $thrown = false;
        try {
            $details->api_secret = 'blah';
        } catch (ImmutabilityException $ie) {
            $thrown = true;
        }
        $this->assertTrue($thrown);

        $thrown = false;
        try {
            $details->public_key = new PublicKey('cWesw5-NR3JLdgJjTdbUGIU5bgIIO48arG7j2AXRYmk');
        } catch (ImmutabilityException $ie) {
            $thrown = true;
        }
        $this->assertTrue($thrown);

        $thrown = false;
        try {
            $details->name = 'blah';
        } catch (ImmutabilityException $ie) {
            $thrown = true;
        }
        $this->assertTrue($thrown);
    }

    public function test_unset_variable()
    {
        $json = '{"client_id": "1234", "api_key_id": "abcdefg", "api_secret": "123456", "public_key": {"curve25519": "mRcJWM6Fe30w48Dej5ZF_HjasIIRLQVR6Rzn4HJOGTs"}, "name": "test"}';
        $details = ClientDetails::decode($json);

        // The @ silences the user warning that is otherwise triggered.
        $this->assertNull(@$details->noRealProperty);

        $details->noRealProperty = 'test';
        $this->assertNull(@$details->noRealProperty);
    }

    public function test_decode_error()
    {
        $this->expectException('\Exception');

        ClientDetails::decode('[invalid json}');
    }
}