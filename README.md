[![Build Status][travis-image]][travis-url] [![Coverage Status][coveralls-image]][coveralls-url] [![Packagist][packagist-image]][packagist-url]

# Introduction

The Tozny End-to-End Encrypted Database (E3DB) is a storage platform with powerful sharing and consent management features.
[Read more on our blog.](https://tozny.com/blog/announcing-project-e3db-the-end-to-end-encrypted-database/)

E3DB provides a familiar JSON-based NoSQL-style API for reading, writing, and querying data stored securely in the cloud.

# Installation

## Composer

To install with composer add the following to your `composer.json` file:

```
"require": {
    "tozny/e3db-php": "dev-master"
}
```

Then run `php composer.phar install`

## Registering a client

1. Download and install the E3DB Command-Line interface (CLI) from our [GitHub releases page](https://github.com/tozny/e3db-go/releases).

2. Register an account using the CLI:

   ```shell
   $ e3db register me@mycompany.com
   ```

   This will create a new default configuration with a randomly
   generated key pair and API credentials, saving it in `$HOME/.tozny/e3db.json`.
   
## Loading configuration and creating a client

Configuration is managed at runtime using environment variables (loading configuration either from the system itself or from a flat `.env` file at the project root). See the `.env.example` file for variable names.

# Usage

## Writing a record

...

## Querying records

...

# Development

...

## Documentation

General E3DB documentation is [on our web site](https://tozny.com/documentation/e3db/).

## Contributing

Bug reports and pull requests are welcome on GitHub at https://github.com/tozny/e3db-php.

## License

This library is available as open source under the terms of the [MIT License](http://opensource.org/licenses/MIT).

[packagist-image]: https://img.shields.io/packagist/dt/tozny/e3db-php.svg
[packagist-url]: https://packagist.org/packages/tozny/e3db-php
[travis-image]: https://travis-ci.org/tozny/e3db-php.svg?branch=master
[travis-url]: https://travis-ci.org/tozny/e3db-php
[coveralls-image]: https://coveralls.io/repos/github/tozny/e3db-php/badge.svg?branch=master
[coveralls-url]: https://coveralls.io/github/tozny/e3db-php