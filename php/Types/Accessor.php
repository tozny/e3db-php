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

use Tozny\E3DB\Exceptions\ImmutabilityException;

trait Accessor
{
    /**
     * Magic getter to retrieve read-only properties.
     *
     * @param string $name Property name to retrieve
     *
     * @return mixed
     */
    public function __get(string $name)
    {
        $key = "_{$name}";
        if (property_exists($this, $key)) {
            return $this->$key;
        }

        trigger_error(sprintf('Undefined property: %s::%s', self::class, $name), E_USER_NOTICE);
        return null;
    }

    /**
     * Magic setter that prevents the changes to read-only properties
     *
     * @param $name
     * @param $value
     *
     * @throws ImmutabilityException
     */
    public function __set($name, $value)
    {
        if (isset($this->immutableFields) && in_array($name, $this->immutableFields)) {
            throw new ImmutabilityException(sprintf('The `%s` field is read-only!', $name));
        }
    }
}