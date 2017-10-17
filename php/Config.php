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

use Tozny\E3DB\Types\Accessor;

/**
 * Configuration and credentials for E3DB.
 *
 * @property-read int    $version     The version number of the configuration format (currently 1)
 * @property-read string $client_id   The client's unique client identifier
 * @property-read string $api_key_id  The client's non-secret API key component
 * @property-read string $api_secret  The client's confidential API key component
 * @property-read string $public_key  The client's Base64URL encoded Curve25519 public key
 * @property-read string $private_key The client's Base64URL encoded Curve25519 private key
 * @property-read string $api_url     The base URL for the E3DB API service
 *
 * @package Tozny\E3DB
 */
class Config
{
    use Accessor;

    /**
     * Default API endpoint location.
     */
    const DEFAULT_API_URL = 'https://api.e3db.com/';

    /**
     * @var int The version number of the configuration format (currently 1)
     */
    protected $_version = 1;

    /**
     * @var string The client's unique client identifier
     */
    protected $_client_id;

    /**
     * @var string The client's non-secret API key component
     */
    protected $_api_key_id;

    /**
     * @var string The client's confidential API key component
     */
    protected $_api_secret;

    /**
     * @var string The client's Base64URL encoded Curve25519 public key
     */
    protected $_public_key;

    /**
     * @var string The client's Base64URL encoded Curve25519 private key
     */
    protected $_private_key;

    /**
     * @var string The base URL for the E3DB API service
     */
    protected $_api_url = self::DEFAULT_API_URL;

    /**
     * @var array Fields that cannot be overwritten externally.
     */
    protected $immutableFields = ['version', 'client_id', 'api_key_id', 'api_secret', 'public_key', 'private_key', 'api_url'];

    public function __construct(
        string $client_id,
        string $api_key_id,
        string $api_secret,
        string $public_key,
        string $private_key,
        string $api_url = self::DEFAULT_API_URL
    )
    {
        $this->_client_id = $client_id;
        $this->_api_key_id = $api_key_id;
        $this->_api_secret = $api_secret;
        $this->_public_key = $public_key;
        $this->_private_key = $private_key;
        $this->_api_url = $api_url;
    }
}