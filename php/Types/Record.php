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

/**
 * A E3DB record containing data and metadata. Records are
 * a key/value mapping containing data serialized
 * into strings. All records are encrypted prior to sending them
 * to the server for storage, and decrypted in the client after
 * they are read.
 *
 * @property-read Meta $meta Meta information about the record.
 *
 * @package Tozny\E3DB\Types
 */
class Record extends JsonUnserializable
{
    use Accessor;

    /**
     * @var Meta Meta information about the record.
     */
    protected $_meta;

    /**
     * @var array The record's application-specific data.
     */
    public $data;

    /**
     * @var array Fields that cannot be overwritten externally.
     */
    protected $immutableFields = ['meta'];

    public function __construct(Meta $meta, $data)
    {
        $this->_meta = $meta;
        $this->data  = $data;
    }

    /**
     * Serialize the object to JSON
     */
    public function jsonSerialize(): array
    {
        return [
            'meta' => $this->_meta,
            'data' => $this->data
        ];
    }

    /**
     * Specify how an already unserialized JSON array should be marshaled into
     * an object representation.
     *
     * Records consist of two elements, meta and data. The array we deserialize into a Record instance
     * must match this format. The meta element is itself an array representing the Meta class. The
     * data element is a simpler array mapping string keys to either encrypted or plaintext string values.
     *
     * <code>
     * $record = Record::decodeArray([
     *   'meta' => [
     *     'record_id'     => '',
     *     'writer_id'     => '',
     *     'user_id'       => '',
     *     'type'          => '',
     *     'plain'         => [],
     *     'created'       => ''
     *     'last_modified' => ''
     *     'version'       => ''
     *   ],
     *   'data' => [
     *     'key' => 'value',
     *     'key' => 'value'
     *   ]
     * ]);
     * </code>
     *
     * @see \Tozny\E3DB\Types\Meta::decodeArray()
     *
     * @param array[string]string $parsed
     *
     * @return Record
     */
    public static function decodeArray(array $parsed): Record
    {
        return new Record(Meta::decodeArray($parsed[ 'meta' ]), $parsed[ 'data' ]);
    }
}