<?php
namespace Tozny\E3DB;

use PHPUnit\Framework\TestCase;
use Tozny\E3DB\Connection\Connection;
use Tozny\E3DB\Connection\GuzzleConnection;
use Tozny\E3DB\Exceptions\ImmutabilityException;
use Tozny\E3DB\Exceptions\NotFoundException;
use Tozny\E3DB\Types\Record;

class ClientTest extends TestCase
{
    const TEST_SHARE_CLIENT = 'dac7899f-c474-4386-9ab8-f638dcc50dec';
    const TEST_SHARE_EMAIL = 'ijones+feedback@tozny.com';

    /**
     * @var Config
     */
    private $config;

    /**
     * @var Connection
     */
    private $conn;

    /**
     * @var Client
     */
    private $client;

    /**
     * @var string
     */
    private $type;

    /**
     * @var Record
     */
    private $record;

    public function setUp()
    {
        $this->config = new Config();
        $this->config->version = 1;
        $this->config->api_key_id = \getenv('API_KEY_ID');
        $this->config->api_secret = \getenv('API_SECRET');
        $this->config->api_url = \getenv('API_URL');
        $this->config->client_id = \getenv('CLIENT_ID');
        $this->config->public_key = \getenv('PUBLIC_KEY');
        $this->config->private_key = \getenv('PRIVATE_KEY');

        $this->conn = new GuzzleConnection($this->config);

        $this->client = new Client($this->config, $this->conn);

        // Write a record
        $this->type = uniqid('type_');
        $this->record = $this->client->write($this->type, ['test' => 'data']);

        parent::setUp();
    }

    public function test_immutability()
    {
        $thrown = false;
        try {
            $this->client->config = new Config();
        } catch (ImmutabilityException $ie) {
            $thrown = true;
        }
        $this->assertTrue($thrown);

        $thrown = false;
        try {
            $this->client->conn = new GuzzleConnection($this->config);
        } catch (ImmutabilityException $ie) {
            $thrown = true;
        }
        $this->assertTrue($thrown);
    }

    public function test_client_info()
    {
        $info = $this->client->client_info($this->config->client_id);

        $this->assertEquals($this->config->client_id, $info->client_id);
    }

    public function test_client_info_error()
    {
        $this->expectException(NotFoundException::class);

        $this->client->client_info('nosuchemail@tozny.com');
    }

    public function test_client_key()
    {
        $key = $this->client->client_key($this->config->client_id);

        $this->assertEquals($this->config->public_key, $key->curve25519);

        $second = $this->client->client_key('26a4b5f7-1abe-4ca2-a049-249e259f04a8');

        $this->assertNotEquals($this->config->public_key, $second->curve25519);
    }

    public function test_read_raw()
    {
        $record = $this->client->read_raw($this->record->meta->record_id);

        $this->assertEquals($this->record->meta->record_id, $record->meta->record_id);
    }

    public function test_read()
    {
        $record = $this->client->read($this->record->meta->record_id);

        $this->assertEquals($this->record->meta->record_id, $record->meta->record_id);
        $this->assertArrayHasKey('test', $record->data);
        $this->assertEquals('data', $record->data['test']);
    }

    public function test_query()
    {
        $data = $this->client->query(true, false, null, $this->record->meta->record_id);

        $this->assertEquals(1, count($data));

        $record = $data[0];
        $this->assertEquals($this->record->meta->record_id, $record->meta->record_id);
    }

    public function test_query_iteration()
    {
        // Write some record
        $type = uniqid('type_');

        foreach(range(0, 10) as $i) {
            $this->client->write($type, ['test' => 'data'], ['index' => (string) $i]);
        }

        // Retrieve records
        $records = $this->client->query(true, false, null, null, $type);

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
        $this->client->read($record_id);
    }

    public function test_delete()
    {
        $record_id = '11111111-7998-441c-8680-3b96e92c2c76';

        $this->client->delete($record_id);
        $this->assertTrue(true); // Noop
    }

    public function test_write()
    {
         $data = [
            'first' => 'this is a string',
            'second' => 'test',
        ];

        $record = $this->client->write(uniqid('type_'), $data);

        $this->assertEquals('test', $record->data['second']);
    }

    public function test_update()
    {
        $data = [
            'first' => 'this is a string',
            'second' => 'test',
        ];

        $record = $this->client->write( uniqid( 'type_' ), $data );

        $this->assertArrayNotHasKey( 'third', $record->data );

        $record->data[ 'third' ] = 'Misc';

        $this->client->update( $record );

        // Re-read the data
        $fetched = $this->client->read( $record->meta->record_id );

        $this->assertArrayHasKey( 'third', $fetched->data );
        $this->assertEquals( 'Misc', $fetched->data[ 'third' ] );
    }

    public function test_share()
    {
        $this->client->share($this->type, self::TEST_SHARE_CLIENT);

        $this->client->revoke($this->type, self::TEST_SHARE_CLIENT);

        $this->client->share($this->type, self::TEST_SHARE_EMAIL);

        $this->client->revoke($this->type, self::TEST_SHARE_EMAIL);

        // If we've gotten to here with no errors or exceptions, then we assume sharing/revocation worked!
        $this->assertTrue(true);
    }
}