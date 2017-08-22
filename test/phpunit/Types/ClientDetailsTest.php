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