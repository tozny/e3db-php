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
 * Full nformation about a specific E3DB client, including the client's
 * public/private keys for cryptographic operations and API credentials.
 *
 * @property-read string    $authorized_by  ID representing the authorizing user (data writer)
 * @property-read string    $authorizer_id  ID representing the authorizer
 * @property-read string    $record_type    String representing the data record type
 * @property-read string    $user_id        ID representing the data writer
 * @property-read string    $writer_id      ID repreesnting the data writer
 * @property-read string    $created        Date of authorization
 *
 * @package Tozny\E3DB\Types
 */
class AuthorizedBy
{
    use Accessor;

    /**
     * @var string ID representing the authorizing user (data writer)
     */
    protected $_authorized_by;

    /**
     * @var string String representing the data record type
     */
    protected $_record_type;

    /**
     * @var string ID representing the data writer
     */
    protected $_user_id;

    /**
     * @var string ID representing the authorizer
     */
    protected $_authorizer_id;

    /**
     * @var string ID repreesnting the data writer
     */
    protected $_writer_id;

    /**
     * @var string Date of authorization
     */
    protected $_created;
    /**
     * @var array Fields that cannot be overwritten externally.
     */
    protected $immutableFields = ['authorized_by','record_type','user_id','authorized_id','writer_id','created'];

    public function __construct(string $authorized_by, string $record_type, string $user_id, string $authorizer_id, string $writer_id, string $created)
    {
        $this->_authorized_by = $authorized_by;
        $this->_record_type = $record_type;
        $this->_user_id = $user_id;
        $this->_authorizer_id = $authorizer_id;
        $this->_writer_id = $writer_id;
        $this->_created = $created;
    }

    /**
     * Serialize the object to JSON
     */
    public function jsonSerialize(): array
    {
        return [
            'authorized_by'  => $this->_authorized_by,
            'record_type' => $this->_record_type,
            'user_id'  => $this->_user_id,
            'authorizer_id' => $this->_authorizer_id,
            'writer_id' => $this->_writer_id,
            'created' => $this->_created
        ];
    }

    public static function getAuthorizedBy(array $parsed): AuthorizedBy
    {
        $writer = new AuthorizedBy(
            $parsed[ 'authorized_by' ],
            $parsed[ 'record_type' ],
            $parsed[ 'user_id' ],
            $parsed[ 'authorizer_id' ],
            $parsed[ 'writer_id' ],
            $parsed[ 'created' ]
        );
        return $writer;
    }
}