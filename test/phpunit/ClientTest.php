<?php
namespace Tozny\E3DB;

use PHPUnit\Framework\TestCase;
use Tozny\E3DB\Connection\Connection;
use Tozny\E3DB\Connection\GuzzleConnection;
use Tozny\E3DB\Exceptions\NotFoundException;

class ClientTest extends TestCase
{
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

        parent::setUp();
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
        $record_id = '41214987-7998-441c-8680-3b96e92c2c76';

        $record = $this->client->read_raw($record_id);

        $this->assertEquals($record_id, $record->meta->record_id);
    }

    public function test_read()
    {
        $record_id = '41214987-7998-441c-8680-3b96e92c2c76';

        $record = $this->client->read($record_id);

        $this->assertEquals($record_id, $record->meta->record_id);
        $this->assertArrayHasKey('test', $record->data);
        $this->assertEquals('123', $record->data['test']);
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
}