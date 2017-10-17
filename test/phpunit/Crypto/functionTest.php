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

namespace Tozny\E3DB\Crypto;

use PHPUnit\Framework\TestCase;
use const Sodium\CRYPTO_SECRETBOX_KEYBYTES;
use const Sodium\CRYPTO_SECRETBOX_NONCEBYTES;

class FunctionTest extends TestCase
{
    public function test_base64encode()
    {
        $unencoded = 'This is a test!';
        $expected = 'VGhpcyBpcyBhIHRlc3Qh';

        $this->assertEquals($expected, base64encode($unencoded));

        $unencoded = 'This has some padding ...';
        $expected = 'VGhpcyBoYXMgc29tZSBwYWRkaW5nIC4uLg';

        $this->assertEquals($expected, base64encode($unencoded));

        $unencoded = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890!@#$%^&*(){}:"[];\'<>?,./ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890!@#$%^&*(){}:"[];\'<>?,./';
        $expected = 'QUJDREVGR0hJSktMTU5PUFFSU1RVVldYWVphYmNkZWZnaGlqa2xtbm9wcXJzdHV2d3h5ejEyMzQ1Njc4OTAhQCMkJV4mKigpe306IltdOyc8Pj8sLi9BQkNERUZHSElKS0xNTk9QUVJTVFVWV1hZWmFiY2RlZmdoaWprbG1ub3BxcnN0dXZ3eHl6MTIzNDU2Nzg5MCFAIyQlXiYqKCl7fToiW107Jzw-PywuLw';

        $this->assertEquals($expected, base64encode($unencoded));
    }

    public function test_base64decode()
    {
        $expected = 'This is a test!';
        $unencoded = 'VGhpcyBpcyBhIHRlc3Qh';

        $this->assertEquals($expected, base64decode($unencoded));

        $expected = 'This has some padding ...';
        $unencoded = 'VGhpcyBoYXMgc29tZSBwYWRkaW5nIC4uLg';

        $this->assertEquals($expected, base64decode($unencoded));

        $expected = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890!@#$%^&*(){}:"[];\'<>?,./ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890!@#$%^&*(){}:"[];\'<>?,./';
        $unencoded = 'QUJDREVGR0hJSktMTU5PUFFSU1RVVldYWVphYmNkZWZnaGlqa2xtbm9wcXJzdHV2d3h5ejEyMzQ1Njc4OTAhQCMkJV4mKigpe306IltdOyc8Pj8sLi9BQkNERUZHSElKS0xNTk9QUVJTVFVWV1hZWmFiY2RlZmdoaWprbG1ub3BxcnN0dXZ3eHl6MTIzNDU2Nzg5MCFAIyQlXiYqKCl7fToiW107Jzw-PywuLw';

        $this->assertEquals($expected, base64decode($unencoded));
    }

    public function test_random_nonce()
    {
        $bytes = random_nonce();

        $this->assertEquals(CRYPTO_SECRETBOX_NONCEBYTES, strlen($bytes));

        $second = random_nonce();

        $this->assertNotEquals($bytes, $second);
    }

    public function test_random_key()
    {
        $bytes = random_key();

        $this->assertEquals(CRYPTO_SECRETBOX_KEYBYTES, strlen($bytes));

        $second = random_key();

        $this->assertNotEquals($bytes, $second);
    }
}