<?php

/**
 * This program provides a few simple examples of reading, writing, and
 * querying e3db records. For more detailed information, please see the
 * documentation home page: https://tozny.com/documentation/e3db/
 *
 * @copyright  Copyright (c) 2017 Tozny, LLC (https://tozny.com)
 * @license    MIT License
 */

use Tozny\E3DB\Client;
use Tozny\E3DB\Config;
use Tozny\E3DB\Connection\GuzzleConnection;

/**
 * ---------------------------------------------------------
 * Initialization
 * ---------------------------------------------------------
 */

require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = new Dotenv\Dotenv(__DIR__ . '/..');
$dotenv->load();

// Configuration values must be set in an immutable configuration object.
// You can use whatever "profiles" or client credentials you want.
$config = new Config(
    \getenv('CLIENT_ID'),
    \getenv('API_KEY_ID'),
    \getenv('API_SECRET'),
    \getenv('PUBLIC_KEY'),
    \getenv('PRIVATE_KEY'),
    \getenv('API_URL')
);

// Now create a client using that configuration and a Guzzle-powered connection
$connection = new GuzzleConnection($config);
$client = new Client($config, $connection);

/**
 * ---------------------------------------------------------
 * Writing a record
 * ---------------------------------------------------------
 */

// Create a record by first creating a local version as an array:
$data = [
    'name' => 'Jon Snow',
    'what_he_knows' => 'Nothing',
];

// Now encrypt the *value* part of the record, write it to the server and
// the server returns the newly created record:
$record = $client->write('test-contact', $data);
$record_id = $record->meta->record_id;
echo "Wrote:    " . $record_id . "\n";

/**
 * ---------------------------------------------------------
 * Simple reading and queries
 * ---------------------------------------------------------
 */

// Use the new record's unique ID to read the same record again from E3DB:
$newRecord = $client->read($record->meta->record_id);
echo 'Record:   ' . $newRecord->data[ 'name' ] . ' ' . $record->data[ 'what_he_knows' ] . "\n";

// Query for all records of type 'test-contact' and print out
// a little bit of data and metadata.
$data = true;
$raw = false;
$writer = null;
$record = null;
$type = 'test-contact';

$queryResult = $client->query($data, $raw, $writer, $record, $type);
foreach ($queryResult as $record) {
    echo 'Data:     ' . $record->data[ 'name' ] . ' ' . $record->data[ 'what_he_knows' ] . "\n";
    echo 'Metadata: ' . $record->meta->record_id . ' ' . $record->meta->type . "\n";
}

/**
 * ---------------------------------------------------------
 * Simple sharing by record type
 * ---------------------------------------------------------
 */

// Share all of the records of type 'test-contact' with Isaac's client ID:
$isaac_client_id = 'db1744b9-3fb6-4458-a291-0bc677dba08b';
$client->share('test-contact', $isaac_client_id);

/**
 * ---------------------------------------------------------
 * More complex queries
 * ---------------------------------------------------------
 */

// Create some new records of the same type (note that they are also shared
// automatically since they are a type that we have shared above. We
// will also add some "plain" fields that are not secret but can be used
// for efficient querying:

$bran_data = ['name' => 'Bran', 'what_he_knows' => 'Crow'];
$bran_plain = ['house' => 'Stark', 'ageRange' => 'child'];
$client->write('test-contact', $bran_data, $bran_plain);

$hodor_data = ['name' => 'Hodor', 'what_he_knows' => 'Hodor'];
$hodor_plain = ['house' => 'Stark', 'ageRange' => 'adult'];
$client->write('test-contact', $hodor_data, $hodor_plain);

$doran_data = ['name' => 'Doran', 'what_he_knows' => 'Oberyn'];
$doran_plain = ['house' => 'Martell', 'ageRange' => 'adult'];
$client->write('test-contact', $doran_data, $doran_plain);

// Create a query that finds everyone from house Stark, but not others:
$queryWesteros = ['eq' => ['name' => 'house', 'value' => 'Stark']];

// Execute that query:
$data = true;
$raw = false;
$writer = null;
$record = null;
$type = null;

$queryResult = $client->query($data, $raw, $writer, $record, $type, $queryWesteros);
foreach ($queryResult as $record) {
    echo $record->data[ 'name' ] . "\n";
}

// Now create a  more complex query with only the adults from house Stark:
$queryWesteros = [
    'and' => [
        ['eq' => ['name' => 'house', 'value' => 'Stark']],
        ['eq' => ['name' => 'ageRange', 'value' => 'adult']]
    ]
];

// Execute that query:
$data = true;
$raw = false;
$writer = null;
$record = null;
$type = null;

$queryResult = $client->query($data, $raw, $writer, $record, $type, $queryWesteros);
foreach ($queryResult as $record) {
    echo $record->data[ 'name' ] . "\n";
}

/**
 * ---------------------------------------------------------
 * Learning about other clients
 * ---------------------------------------------------------
 */
$isaac_client_info = $client->client_info($isaac_client_id);
var_dump($isaac_client_info);

// Fetch the public key:
$isaac_pub_key = $client->client_key($isaac_client_id);
var_dump($isaac_pub_key);

/**
 * ---------------------------------------------------------
 * Clean up - Comment these out if you want to experiment
 * ---------------------------------------------------------
 */

// Revoke the sharing created by the client.share
$client->revoke('test-contact', $isaac_client_id);

// Delete the record we created above
$client->delete($record_id);

// Delete all of the records of type test-contact from previous runs:
$data = false;
$raw = false;
$writer = null;
$record = null;
$type = 'test-contact';

$queryResult = $client->query($data, $raw, $writer, $record, $type);
foreach ($queryResult as $record) {
    $client->delete($record->meta->record_id);
}