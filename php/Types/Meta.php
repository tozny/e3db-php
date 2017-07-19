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

namespace Tozny\E3DB\Types;

/**
 * Describe the meta information attributed to a specific encrypted record.
 *
 * @package Tozny\E3DB\Types
 */
class Meta implements \JsonSerializable
{
    /**
     * @var string Unique ID of the record, or `null` if not yet written
     */
    public $record_id = null;

    /**
     * @var string Unique ID of the client who wrote the record
     */
    public $writer_id;

    /**
     * @var string Unique ID of the record subject
     */
    public $user_id;

    /**
     * @var string Free-form description of the record content type
     */
    public $type;

    /**
     * @var array Map of String->String values describing the record's plaintext meta
     */
    public $plain = null;

    /**
     * @var \DateTime When this record was created, or `null` if unavailable.
     */
    public $created = null;

    /**
     * @var \DateTime When this record last changed, or `null` if unavailable.
     */
    public $last_modified = null;

    /**
     * @var string Opaque version identifier created by the server on changes.
     */
    public $version = null;

    /**
     * Serialize the object to JSON
     */
    function jsonSerialize(): array
    {
        return [
            'record_id'     => $this->record_id,
            'writer_id'     => $this->writer_id,
            'user_id'       => $this->user_id,
            'type'          => $this->type,
            'plain'         => $this->plain,
            'created'       => self::jsonSerializeDate( $this->created ),
            'last_modified' => self::jsonSerializeDate( $this->last_modified ),
            'version'       => $this->version,
        ];
    }

    /**
     * Helper function to coalesce null DateTime values for JSON serialization.
     *
     * @param \DateTime|null $date
     *
     * @return mixed
     */
    protected static function jsonSerializeDate( $date )
    {
        if ( $date !== null ) {
            return $date->format( \DateTime::ISO8601 );
        }

        return null;
    }
}