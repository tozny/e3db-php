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

namespace Tozny\E3DB\Exceptions;

use Throwable;

/**
 * Error thrown when an update request conflicts with an existing object.
 *
 * @package Tozny\E3DB\Exceptions
 *
 * @codeCoverageIgnore
 */
class ConflictException extends \Exception
{
    /**
     * @var string Name of the resource that was requested
     */
    protected $resource;

    /**
     * Custom constructor requires the message to be non-empty.
     *
     * @param string $message
     * @param string $resource
     * @param Throwable|null $previous
     */
    public function __construct(string $message, string $resource = '', Throwable $previous = null)
    {
        $this->resource = strtoupper($resource);

        parent::__construct($message, 409, $previous);
    }

    /**
     * Customize the error output returned for printing to a log.
     *
     * @return string
     */
    public function __toString()
    {
        if (empty($this->resource)) {
            return '409 CONFLICT: ' . $this->message . "\n";
        }

        return "409 {$this->resource} CONFLICT: {$this->message}\n";
    }
}