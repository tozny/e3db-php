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