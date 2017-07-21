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

class PublicKeyTest extends TestCase
{
    public function test_decoding()
    {
        $json = '{"curve25519": "mRcJWM6Fe30w48Dej5ZF_HjasIIRLQVR6Rzn4HJOGTs"}';

        $decoded = PublicKey::decode($json);
        $this->assertEquals('mRcJWM6Fe30w48Dej5ZF_HjasIIRLQVR6Rzn4HJOGTs', $decoded->curve25519);
    }

    public function test_encoding()
    {
        $key = new PublicKey('mRcJWM6Fe30w48Dej5ZF_HjasIIRLQVR6Rzn4HJOGTs');

        $encoded = \json_encode($key);
        $this->assertEquals('{"curve25519":"mRcJWM6Fe30w48Dej5ZF_HjasIIRLQVR6Rzn4HJOGTs"}', $encoded);
    }

    public function test_immutability()
    {
        $key = new PublicKey('mRcJWM6Fe30w48Dej5ZF_HjasIIRLQVR6Rzn4HJOGTs');

        $thrown = false;
        try {
            $key->curve25519 = 'cWesw5-NR3JLdgJjTdbUGIU5bgIIO48arG7j2AXRYmk';
        } catch (ImmutabilityException $ie) {
            $thrown = true;
        }
        $this->assertTrue($thrown);
    }

    public function test_unset_variable()
    {
        $key = new PublicKey('mRcJWM6Fe30w48Dej5ZF_HjasIIRLQVR6Rzn4HJOGTs');

        // The @ silences the user warning that is otherwise triggered.
        $this->assertNull(@$key->noRealProperty);

        $key->noRealProperty = 'test';
        $this->assertNull(@$key->noRealProperty);
    }

    public function test_decode_error()
    {
        $this->expectException('\Exception');

        PublicKey::decode('[invalid json}');
    }
}