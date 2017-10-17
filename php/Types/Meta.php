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
 * Describe the meta information attributed to a specific encrypted record.
 *
 * @property-read string $record_id     Unique ID of the record, or `null` if not yet written
 * @property-read \DateTime $created       When this record was created, or `null` if unavailable.
 * @property-read \DateTime $last_modified When this record last changed, or `null` if unavailable.
 * @property-read string $version       Opaque version identifier created by the server on changes.
 *
 * @package Tozny\E3DB\Types
 */
class Meta extends JsonUnserializable
{
    use Accessor;

    /**
     * @var string Unique ID of the record, or `null` if not yet written
     */
    protected $_record_id = null;

    /**
     * @var \DateTime When this record was created, or `null` if unavailable.
     */
    protected $_created = null;

    /**
     * @var \DateTime When this record last changed, or `null` if unavailable.
     */
    protected $_last_modified = null;

    /**
     * @var string Opaque version identifier created by the server on changes.
     */
    protected $_version = null;

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
     * @var array Fields that cannot be overwritten externally.
     */
    protected $immutableFields = ['record_id', 'created', 'last_modified', 'version'];

    public function __construct(string $writer_id, string $user_id, string $type, array $plain = null)
    {
        $this->_record_id     = null;
        $this->writer_id      = $writer_id;
        $this->user_id        = $user_id;
        $this->type           = $type;
        $this->plain          = $plain;
        $this->_created       = null;
        $this->_last_modified = null;
        $this->_version       = null;
    }

    /**
     * Serialize the object to JSON
     */
    public function jsonSerialize(): array
    {
        if (empty($this->plain)) {
            $plain = null;
        } else {
            $plain = $this->plain;
        }

        return [
            'record_id'     => $this->_record_id,
            'writer_id'     => $this->writer_id,
            'user_id'       => $this->user_id,
            'type'          => $this->type,
            'plain'         => $plain,
            'created'       => self::jsonSerializeDate($this->_created),
            'last_modified' => self::jsonSerializeDate($this->_last_modified),
            'version'       => $this->_version,
        ];
    }

    /**
     * Specify how an already unserialized JSON array should be marshaled into
     * an object representation.
     *
     * Meta objects consist of both mutable and immutable information describing
     * the record to which they're attached. Ownership, type, and datetime information
     * is fixed and only updated by the server, but the plaintext fields attributed
     * to a record can be controlled by the user. This mutable field is a map of
     * strings to strings (an associative array) and is stored in plaintext on the
     * server. The array expected for deserializing back into an object requires:
     *
     * <code>
     * $meta = Meta::decodeArray([
     *   'record_id'     => '',
     *   'writer_id'     => '',
     *   'user_id'       => '',
     *   'type'          => '',
     *   'plain'         => [],
     *   'created'       => ''
     *   'last_modified' => ''
     *   'version'       => ''
     * ]);
     * </code>
     *
     * @param array $parsed
     *
     * @return Meta
     */
    public static function decodeArray(array $parsed): Meta
    {
        $meta = new Meta(
            $parsed[ 'writer_id' ],
            $parsed[ 'user_id' ],
            $parsed[ 'type' ],
            $parsed[ 'plain' ]
        );
        $meta->_record_id     = $parsed[ 'record_id' ];
        $meta->_created       = new \DateTime($parsed[ 'created' ]);
        $meta->_last_modified = new \DateTime($parsed[ 'last_modified' ]);
        $meta->_version       = $parsed[ 'version' ];

        return $meta;
    }

    /**
     * Helper function to coalesce null DateTime values for JSON serialization.
     *
     * @param \DateTime|null $date
     *
     * @return mixed
     */
    protected static function jsonSerializeDate($date)
    {
        if ($date !== null) {
            return $date->format(\DateTime::ISO8601);
        }

        return null;
    }
}