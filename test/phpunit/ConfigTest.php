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

namespace Tozny\E3DB;

use PHPUnit\Framework\TestCase;
use Tozny\E3DB\Exceptions\ImmutabilityException;

class ConfigTest extends TestCase
{
    public function test_immutability()
    {
        $config = new Config(
            'client',
            'api_key',
            'secret',
            'public',
            'private',
            'api_url'
        );

        $thrown = false;
        try {
            $config->version = 2;
        } catch (ImmutabilityException $ie) {
            $thrown = true;
        }
        $this->assertTrue($thrown);

        $thrown = false;
        try {
            $config->client_id = 'different';
        } catch (ImmutabilityException $ie) {
            $thrown = true;
        }
        $this->assertTrue($thrown);

        $thrown = false;
        try {
            $config->api_key_id = 'different';
        } catch (ImmutabilityException $ie) {
            $thrown = true;
        }
        $this->assertTrue($thrown);

        $thrown = false;
        try {
            $config->api_secret = 'different';
        } catch (ImmutabilityException $ie) {
            $thrown = true;
        }
        $this->assertTrue($thrown);

        $thrown = false;
        try {
            $config->public_key = 'different';
        } catch (ImmutabilityException $ie) {
            $thrown = true;
        }
        $this->assertTrue($thrown);

        $thrown = false;
        try {
            $config->private_key = 'different';
        } catch (ImmutabilityException $ie) {
            $thrown = true;
        }
        $this->assertTrue($thrown);

        $thrown = false;
        try {
            $config->api_url = 'different';
        } catch (ImmutabilityException $ie) {
            $thrown = true;
        }
        $this->assertTrue($thrown);
    }

    public function test_unset_variable()
    {
        $config = new Config(
            'client',
            'api_key',
            'secret',
            'public',
            'private',
            'api_url'
        );

        // The @ silences the user warning that is otherwise triggered.
        $this->assertNull(@$config->noRealProperty);

        $config->noRealProperty = 'test';
        $this->assertNull(@$config->noRealProperty);
    }
}