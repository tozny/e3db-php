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

/**
 * Describe a query request against the E3DB API.
 *
 * @package Tozny\E3DB\Types
 */
class Query implements \JsonSerializable
{
    const DEFAULT_QUERY_COUNT = 100;

    public $count = self::DEFAULT_QUERY_COUNT;

    public $include_data;

    public $writer_ids;

    public $user_ids = [];

    public $record_ids = [];

    public $content_types = [];

    public $plain = [];

    public $after_index;

    public $include_all_writers = true;

    public function __construct(
        int $after_index          = 0,
        bool $include_data        = false,
        $writer_ids               = null,
        $record_ids               = null,
        $content_types            = null,
        $plain                    = null,
        $user_ids                 = null,
        int $count                = self::DEFAULT_QUERY_COUNT,
        bool $include_all_writers = false
    )
    {
        $this->after_index = $after_index;
        $this->include_data = $include_data;
        if (is_array($writer_ids)) {
            $this->writer_ids = $writer_ids;
        } else if ($writer_ids !== null) {
            $this->writer_ids = [$writer_ids];
        }
        if (is_array($record_ids)) {
            $this->record_ids = $record_ids;
        } else if ($record_ids !== null) {
            $this->record_ids = [$record_ids];
        }
        if (is_array($content_types)) {
            $this->content_types = $content_types;
        } else if ($content_types !== null) {
            $this->content_types = [$content_types];
        }
        if (is_array($plain)) {
            $this->plain = $plain;
        } else {
            $this->plain = null;
        }
        if (is_array($user_ids)) {
            $this->user_ids = $user_ids;
        } else if ($user_ids !== null) {
            $this->user_ids = [$user_ids];
        }
        $this->count = $count;
        $this->include_all_writers = $include_all_writers;
    }

    /**
     * Serialize the object to JSON
     */
    public function jsonSerialize(): array
    {
        $json = [];

        if ($this->count !== null) $json[ 'count' ] = intval( $this->count );
        if ($this->include_data !== null) $json[ 'include_data' ] = !! $this->include_data;
        if ($this->writer_ids !== null && !empty($this->writer_ids)) $json[ 'writer_ids' ] = $this->writer_ids;
        if ($this->user_ids !== null && !empty($this->user_ids)) $json[ 'user_ids' ] = $this->user_ids;
        if ($this->record_ids !== null && !empty($this->record_ids)) $json[ 'record_ids' ] = $this->record_ids;
        if ($this->content_types !== null && !empty($this->content_types)) $json[ 'content_types' ] = $this->content_types;
        if ($this->plain !== null && !empty($this->plain)) $json[ 'plain' ] = $this->plain;
        if ($this->after_index !== null) $json[ 'after_index' ] = intval( $this->after_index );
        if ($this->include_all_writers !== null) $json[ 'include_all_writers' ] = !! $this->include_all_writers;

        return $json;
    }
}