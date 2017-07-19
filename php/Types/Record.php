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
 * A E3DB record containing data and metadata. Records are
 * a key/value mapping containing data serialized
 * into strings. All records are encrypted prior to sending them
 * to the server for storage, and decrypted in the client after
 * they are read.
 *
 * @package Tozny\E3DB\Types
 */
class Record
{
    public static function new( array $raw ): Record
    {
        $record = new self();

        $record->meta = new Meta();
        $record->meta->record_id = $raw['meta']['record_id'];
        $record->meta->writer_id = $raw['meta']['writer_id'];
        $record->meta->user_id = $raw['meta']['user_id'];
        $record->meta->type = $raw['meta']['type'];
        $record->meta->plain = $raw['meta']['plain'];
        $record->meta->created = new \DateTime( $raw['meta']['created'] );
        $record->meta->last_modified = new \DateTime( $raw['meta']['last_modified'] );
        $record->meta->version = $raw['meta']['version'];

        $record->data = $raw['data'];

        return $record;
    }

    /**
     * @var Meta Meta information about the record.
     */
    public $meta;

    /**
     * @var array The record's application-specific data.
     */
    public $data;
}