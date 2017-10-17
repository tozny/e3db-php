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