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

/**
 * Default API endpoint location.
 */
const DEFAULT_API_URL = 'https://api.e3db.com/';

/**
 * Configuration and credentials for E3DB.
 *
 * @package Tozny\E3DB
 */
class Config
{
    /**
     * @var int The version number of the configuration format (currently 1)
     */
    public $version;

    /**
     * @var string The client's unique client identifier
     */
    public $client_id;

    /**
     * @var string The client's non-secret API key component
     */
    public $api_key_id;

    /**
     * @var string The client's confidential API key component
     */
    public $api_secret;

    /**
     * @var string The client's Base64URL encoded Curve25519 public key
     */
    public $public_key;

    /**
     * @var string The client's Base64URL encoded Curve25519 private key
     */
    public $private_key;

    /**
     * @var string The base URL for the E3DB API service
     */
    public $api_url = DEFAULT_API_URL;

    /**
     * @var bool A flag to enable HTTP logging when true
     */
    public $logging = false;
}