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

class QueryTest extends TestCase
{
    public function test_encoding()
    {
        // All fields
        $query1 = new Query(0, true, '4a732eb3-de77-4be1-96d1-da6ef8d67f2f', '4a732eb3-de77-4be1-96d1-da6ef8d67f2f', 'test', ['meta' => 'exists'], '4a732eb3-de77-4be1-96d1-da6ef8d67f2f', 5, false);

        $encoded = \json_encode($query1);
        $this->assertEquals(
            '{"count":5,"include_data":true,"writer_ids":["4a732eb3-de77-4be1-96d1-da6ef8d67f2f"],"user_ids":["4a732eb3-de77-4be1-96d1-da6ef8d67f2f"],"record_ids":["4a732eb3-de77-4be1-96d1-da6ef8d67f2f"],"content_types":["test"],"plain":{"meta":"exists"},"after_index":0,"include_all_writers":false}',
            $encoded
        );

        // Null fields
        $query2 = new Query();

        $encoded = \json_encode($query2);
        $this->assertEquals('{"count":100,"include_data":false,"after_index":0,"include_all_writers":false}', $encoded);
    }
}