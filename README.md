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

To write new records to the database, call the `Tozny\E3DB\Client::write` method with a string describing the type of data to be written, along with an associative array containing the fields of the record. `Tozny\E3DB\Client::write` returns the newly created record.

```
<?php
$record = $client->write('contact', [
  'first_name' => 'Jon',
  'last_name'  => 'Snoq',
  'phone'      => '555-555-1212',
]);

echo sprintf("Wrote record %s\n", $record->meta->record_id);
```

## Querying records

E3DB supports many options for querying records based on the fields stored in record metadata. Refer to the API documentation for the complete set of options that can be passed to `Tozny\E3DB\Client::query`.

For example, to list all records of type `contact` and print a simple report containing names and phone numbers:

```
$records = $client->query(true, false, null, null, 'contact');
foreach($records as $record) {
  $fullname = $record->data['first_name'] . ' ' . $record->data['last_name'];
  echo sprintf("%-40s %s\n", $fullname, $record->data['phone']);
}
```

In this example, the `Tozny\E3DB\Client::query` method returns an iterator that contains each record that matches the query.

## More examples

See [the simple example code](https://github.com/tozny/e3db-php/blob/master/examples/simple.php) for runnable detailed examples.

# Development

Before running tests, create _two_ integration test clients with the E3DB command line tool:

```
$ e3db -p integration-test register me+test@mycompany.com
```

Store the credentials returned for both clients in a `.env` file at the project root (see `.env.example` for the example file layout).

After checking out the repo, install dependencies using `composer install` then run PHPUnit with `./vendor/bin/phpunit` to execute all of the integration tests.

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