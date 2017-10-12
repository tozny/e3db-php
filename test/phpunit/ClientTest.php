<?php
namespace Tozny\E3DB;

use PHPUnit\Framework\TestCase;
use Tozny\E3DB\Connection\Connection;
use Tozny\E3DB\Connection\GuzzleConnection;
use Tozny\E3DB\Exceptions\ConflictException;
use Tozny\E3DB\Exceptions\ImmutabilityException;
use Tozny\E3DB\Exceptions\NotFoundException;
use Tozny\E3DB\Types\Meta;
use Tozny\E3DB\Types\PublicKey;
use Tozny\E3DB\Types\Record;

class ClientTest extends TestCase
{
    /**
     * @var Config
     */
    private static $config;

    /**
     * @var Connection
     */
    private static $conn;

    /**
     * @var Client
     */
    private static $client;

    /**
     * @var Client
     */
    private static $client2;

    /**
     * @var string
     */
    private static $client_2_id;

    /**
     * @var string
     */
    private static $type;

    /**
     * @var Record
     */
    private static $record;

    public static function setUpBeforeClass()
    {
        // Register test clients
        $token = \getenv('REGISTRATION_TOKEN');
        list($client1_public_key, $client1_private_key) = Client::generate_keypair();
        $client1_name = uniqid('test_client_');
        list($client2_public_key, $client2_private_key) = Client::generate_keypair();
        $client2_name = uniqid('share_client_');

        $client1 = Client::register($token, $client1_name, $client1_public_key, '', false, \getenv('API_URL'));
        $client2 = Client::register($token, $client2_name, $client2_public_key, '', false, \getenv('API_URL'));
        self::$client_2_id = $client2->client_id;

        self::$config = new Config(
            $client1->client_id,
            $client1->api_key_id,
            $client1->api_secret,
            $client1->public_key->curve25519,
            $client1_private_key,
            \getenv('API_URL')
        );

        self::$conn = new GuzzleConnection(self::$config);

        self::$client = new Client(self::$config, self::$conn);

        $config2 = new Config(
            $client2->client_id,
            $client2->api_key_id,
            $client2->api_secret,
            $client2->public_key->curve25519,
            $client2_private_key,
            \getenv('API_URL')
        );

        $conn2 = new GuzzleConnection($config2);

        self::$client2 = new Client($config2, $conn2);

        // Write a record
        self::$type = uniqid('type_');
        self::$record = self::$client->write(self::$type, ['test' => 'data']);

        parent::setUpBeforeClass();
    }

    public function test_registration()
    {
        $token = \getenv('REGISTRATION_TOKEN');
        list($public_key, ) = Client::generate_keypair();
        $name = uniqid('test_client_');

        $client = Client::register($token, $name, $public_key, '', false, \getenv('API_URL'));

        $this->assertEquals($name, $client->name);
        $this->assertEquals($public_key, $client->public_key->curve25519);
        $this->assertNotEmpty($client->api_key_id);
        $this->assertNotEmpty($client->api_secret);
        $this->assertNotEmpty($client->client_id);
    }

    public function test_immutability()
    {
        $thrown = false;
        try {
            self::$client->config = new Config('', '', '', '', '', '');
        } catch (ImmutabilityException $ie) {
            $thrown = true;
        }
        $this->assertTrue($thrown);

        $thrown = false;
        try {
            self::$client->conn = new GuzzleConnection(self::$config);
        } catch (ImmutabilityException $ie) {
            $thrown = true;
        }
        $this->assertTrue($thrown);
    }

    public function test_unset_variable()
    {
        // The @ silences the user warning that is otherwise triggered.
        $this->assertNull(@self::$client->noRealProperty);

        self::$client->noRealProperty = 'test';
        $this->assertNull(@self::$client->noRealProperty);
    }

    public function test_client_info()
    {
        $info = self::$client->client_info(self::$config->client_id);

        $this->assertEquals(self::$config->client_id, $info->client_id);
    }

    public function test_client_info_error()
    {
        $this->expectException(NotFoundException::class);

        self::$client->client_info('integration_test+' . uniqid() . '@tozny.com');
    }

    public function test_client_key()
    {
        $key = self::$client->client_key(self::$config->client_id);

        $this->assertEquals(self::$config->public_key, $key->curve25519);

        $second = self::$client->client_key(self::$client_2_id);

        $this->assertNotEquals(self::$config->public_key, $second->curve25519);
    }

    public function test_read_raw()
    {
        $record = self::$client->read_raw(self::$record->meta->record_id);

        $this->assertEquals(self::$record->meta->record_id, $record->meta->record_id);
    }

    public function test_read()
    {
        $record = self::$client->read(self::$record->meta->record_id);

        $this->assertEquals(self::$record->meta->record_id, $record->meta->record_id);
        $this->assertArrayHasKey('test', $record->data);
        $this->assertEquals('data', $record->data['test']);

        // Verify a read with a second client instance, to purge the EAK cache
        $conn = new GuzzleConnection(self::$config);
        $client = new Client(self::$config, $conn);

        $second = $client->read(self::$record->meta->record_id);
        $this->assertEquals($record->meta->record_id, $second->meta->record_id);
    }

    public function test_query()
    {
        $data = self::$client->query(true, false, null, self::$record->meta->record_id);

        $this->assertEquals(1, count($data));

        $record = $data[0];
        $this->assertEquals(self::$record->meta->record_id, $record->meta->record_id);
    }

    public function test_query_iteration()
    {
        // Write some record
        $type = uniqid('type_');

        foreach(range(0, 10) as $i) {
            self::$client->write($type, ['test' => 'data'], ['index' => (string) $i]);
        }

        // Retrieve records
        $records = self::$client->query(true, false, null, null, $type);

        $counted = [];
        foreach($records as $record) {
            $this->assertEquals('data', $record->data['test']);
            $counted[] = $record->meta->plain['index'];
        }

        $total = array_reduce($counted, function ($carry, $item) {
            return $carry + intval($item);
        }, 0);

        $this->assertEquals(55, $total);
    }

    public function test_read_error()
    {
        $record_id = '11111111-7998-441c-8680-3b96e92c2c76';

        $this->expectException(NotFoundException::class);
        self::$client->read($record_id);
    }

    public function test_delete()
    {
        $record_id = '11111111-7998-441c-8680-3b96e92c2c76';

        self::$client->delete($record_id);
        $this->assertTrue(true); // Noop
    }

    public function test_write()
    {
        $data = [
            'first' => 'this is a string',
            'second' => 'test',
        ];

        $record = self::$client->write(uniqid('type_'), $data);

        $this->assertEquals('test', $record->data['second']);
    }

    public function test_update()
    {
        $data = [
            'first' => 'this is a string',
            'second' => 'test',
        ];

        $record = self::$client->write( uniqid( 'type_' ), $data );

        $this->assertArrayNotHasKey( 'third', $record->data );

        $record->data[ 'third' ] = 'Misc';

        self::$client->update( $record );

        // Re-read the data
        $fetched = self::$client->read( $record->meta->record_id );

        $this->assertArrayHasKey( 'third', $fetched->data );
        $this->assertEquals( 'Misc', $fetched->data[ 'third' ] );
    }

    public function test_failed_update()
    {
        $data = [
            'first' => 'this is a string',
            'second' => 'test',
        ];

        $record = self::$client->write( uniqid( 'type_' ), $data );

        // Build up the same record with a bogus version
        $newMeta = Meta::decodeArray([
            'record_id'     => $record->meta->record_id,
            'writer_id'     => $record->meta->writer_id,
            'user_id'       => $record->meta->user_id,
            'type'          => $record->meta->type,
            'plain'         => $record->meta->plain,
            'created'       => '2017-07-04',                          // Doesn't matter ...
            'last_modified' => '2017-07-04',                          // Doesn't matter ...
            'version'       => '11111111-7998-441c-8680-3b96e92c2c76' // Bogus version
        ]);
        $newRecord = new Record($newMeta, $record->data);

        $this->expectException(ConflictException::class);

        self::$client->update( $newRecord );
    }

    public function test_share()
    {
        $data = [
            'first' => 'this is a string',
            'second' => 'test',
        ];
        $type = uniqid('type_');

        $record = self::$client->write($type, $data);

        self::$client->share($type, self::$client_2_id);

        $record2 = self::$client2->read($record->meta->record_id);

        $this->assertEquals($record->data, $record2->data);

        self::$client->revoke($type, self::$client_2_id);

        // If we've gotten to here with no errors or exceptions, then we assume sharing/revocation worked!
        $this->assertTrue(true);
    }
}