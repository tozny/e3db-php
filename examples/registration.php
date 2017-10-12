<?php

/**
 * This program provides a simple example illustrating how to programmatically
 * register a client with InnoVault and e3db. In some situations, it's preferable
 * to register a client from the server or system that will be using its
 * credentials (to ensure that all data is truly encrypted from end-to-end
 * with no possibilities of a credential leak). For more detailed information,
 * please see the documentation home page: https://tozny.com/documentation/e3db
 *
 * @author    Eric Mann (eric@tozny.com)
 * @copyright Copyright (c) 2017 Tozny, LLC
 * @license   Public Domain
*/

use Tozny\E3DB\Client;
use Tozny\E3DB\Types\PublicKey;
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

// A registration token is required to set up a client. In this situation,
// we assume an environment variable called REGISTRATION_TOKEN is set
$token = \getenv('REGISTRATION_TOKEN');

// Clients can either create new cryptographic keypairs, or load in a pre-defined
// pair of Curve25519 keys. In this situation, we will generate a new keypair.
list($public_key, $private_key) = Client::generate_keypair();

echo 'Public Key:  ' . $public_key . "\n";
echo 'Private Key: ' . $private_key . "\n";

// Clients must be registered with a name unique to your account to help
// differentiate between different sets of credentials in the Admin Console.
// In this example, the name is set at random
$client_name = uniqid('client_');

echo 'Client Name: ' . $client_name . "\n";

// Passing all of the data above into the registration routine will create
// a new client with the system. Remember to keep your private key private!
$client_info = Client::register($token, $client_name, $public_key);

// Optionally, you can automatically back up the credentials of the newly-created
// client to your InnoVault account (accessible via https://console.tozny.com) by
// passing your private key and a backup flag when registering. The private key is
// not sent anywhere, but is used by the newly-created client to sign an encrypted
// copy of its credentials that is itself stored in e3db for later use.
//
// Client credentials are not backed up by default.

// $client_info = Client::register($token, $client_name, $public_key, $private_key, true);

echo 'Client ID:   ' . $client_info->client_id . "\n";
echo 'API Key ID:  ' . $client_info->api_key_id . "\n";
echo 'API Secret:  ' . $client_info->api_secret . "\n";

/**
 * ---------------------------------------------------------
 * Usage
 * ---------------------------------------------------------
 */

// Once the client is registered, you can use it immediately to create the
// configuration used to instantiate a Client that can communicate with
// e3db directly.

$config = new Config(
    $client_info->client_id,
    $client_info->api_key_id,
    $client_info->api_secret,
    $public_key,
    $private_key
);

// Now create a client using that configuration.
$connection = new GuzzleConnection($config);
$client = new Client($config, $connection);

// From this point on, the new client can be used as any other client to read
// write, delete, and query for records. See the `simple.rb` documentation
// for more complete examples ...