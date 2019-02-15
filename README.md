![PHP 7.0+][php-image] [![Build Status][travis-image]][travis-url] [![Coverage Status][coveralls-image]][coveralls-url] [![Packagist][packagist-image]][packagist-url]

# Introduction

The Tozny End-to-End Encrypted Database (E3DB) is a storage platform with powerful sharing and consent management features.
[Read more on our blog.](https://tozny.com/blog/announcing-project-e3db-the-end-to-end-encrypted-database/)

E3DB provides a familiar JSON-based NoSQL-style API for reading, writing, and querying data stored securely in the cloud.

# Installation

## Composer

To install with composer add the following to your `composer.json` file:

```
"require": {
    "tozny/e3db": "1.2.0"
}
```

Then run `php composer.phar install`

## Registering a client

Register an account with [InnoVault](https://innovault.io) to get started. From the Admin Console you can create clients directly (and grab their credentials from the console) or create registration tokens to dynamically create clients with `Tozny\E3DB\Client::register()`. Clients registered from within the console will automatically back their credentials up to your account. Clients created dynamically via the SDK can _optionally_ back their credentials up to your account.

For a more complete walkthrough, see [`/examples/registration.php`](https://github.com/tozny/e3db-php/blob/master/examples/registration.php).

### Without Credential Backup

```php
$token = '...';
$client_name = '...';

list($public_key, $private_key) = \Tozny\E3DB\Client::generate_keypair();
$client_info = \Tozny\E3DB\Client::register($token, $client_name, $public_key);
```

The object returned from the server contains the client's UUID, API key, and API secret (as well as echos back the public key passed during registration). It's your responsibility to store this information locally as it _will not be recoverable_ without credential backup.

### With Credential Backup

```php
$token = '...';
$client_name = '...';

list($public_key, $private_key) = \Tozny\E3DB\Client::generate_keypair();
$client_info = \Tozny\E3DB\Client::register($token, $client_name, $public_key, $private_key, true);
```

The private key must be passed to the registration handler when backing up credentials as it is used to cryptographically sign the encrypted backup file stored on the server. The private key never leaves the system, and the stored credentials will only be accessible to the newly-registered client itself or the account with which it is registered.

## Loading configuration and creating a client

Configuration is managed at runtime by instantiating a `Tozny\E3DB\Config` object with your client's credentials.

```php
/**
 * Assuming your credentials are stored as defined constants in the
 * application, pass them each into the configuration constructor as
 * follows:
 */
$config = new \Tozny\E3DB\Config(
  CLIENT_ID,
  API_KEY_ID,
  API_SECRET,
  PUBLIC_KEY,
  PRIVATE_KEY,
  API_URL
);

/**
 * Pass the configuration to the default coonection handler, which
 * uses Guzzle for requests. If you need a different library for
 * requests, subclass `\Tozny\E3DB\Connection` and pass an instance
 * of your custom implementation to the client instead.
 */
$connection = new \Tozny\E3DB\Connection\GuzzleConnection($config);

/**
 * Pass both the configuration and connection handler when building
 * a new client instance.
 */
$client = new \Tozny\E3DB\Client($config, $connection);
```

# Usage

## Writing a record

To write new records to the database, call the `Tozny\E3DB\Client::write` method with a string describing the type of data to be written, along with an associative array containing the fields of the record. `Tozny\E3DB\Client::write` returns the newly created record.

```php
$record = $client->write('contact', [
  'first_name' => 'Jon',
  'last_name'  => 'Snow',
  'phone'      => '555-555-1212',
]);

echo sprintf("Wrote record %s\n", $record->meta->record_id);
```

## Querying records

E3DB supports many options for querying records based on the fields stored in record metadata. Refer to the API documentation for the complete set of options that can be passed to `Tozny\E3DB\Client::query`.

For example, to list all records of type `contact` and print a simple report containing names and phone numbers:

```php
$data = true;
$raw = false;
$writer = null;
$record = null;
$type = 'contact';

$records = $client->query($data, $raw, $writer, $record, $type);
foreach($records as $record) {
  $fullname = $record->data['first_name'] . ' ' . $record->data['last_name'];
  echo sprintf("%-40s %s\n", $fullname, $record->data['phone']);
}
```

In this example, the `Tozny\E3DB\Client::query` method returns an iterator that contains each record that matches the query.

## More examples

See [the simple example code](https://github.com/tozny/e3db-php/blob/master/examples/simple.php) for runnable detailed examples.

# Development

Before running tests, create a registration token through your [InnoVault](https://innovault.io) account.

Store the registration token in a `.env` file at the project root (see `.env.example` for the example file layout). The integration tests will use this token to dynamically create test clients.

After checking out the repo, install dependencies using `composer install` then run PHPUnit with `./vendor/bin/phpunit` to execute all of the integration tests.

## Documentation

General E3DB documentation is [on our web site](https://tozny.com/documentation/e3db/).

## Contributing

Bug reports and pull requests are welcome on GitHub at https://github.com/tozny/e3db-php.

[php-image]: https://img.shields.io/badge/php-7.0%2B-green.svg
[packagist-image]: https://img.shields.io/packagist/dt/tozny/e3db.svg
[packagist-url]: https://packagist.org/packages/tozny/e3db
[travis-image]: https://travis-ci.org/tozny/e3db-php.svg?branch=master
[travis-url]: https://travis-ci.org/tozny/e3db-php
[coveralls-image]: https://coveralls.io/repos/github/tozny/e3db-php/badge.svg?branch=master
[coveralls-url]: https://coveralls.io/github/tozny/e3db-php
