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