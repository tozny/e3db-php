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