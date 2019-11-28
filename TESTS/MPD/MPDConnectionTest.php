<?php


namespace Kolbasyatin\MPD\TESTS\MPD;


use Kolbasyatin\MPD\MPD\Exceptions\MPDConnectionException;
use Kolbasyatin\MPD\MPD\MPDConnection;
use PHPUnit\Framework\TestCase;

class MPDConnectionTest extends TestCase
{
    public const TEST_MPD_URL = 'localhost:6600';

    public const TEST_PASSWORD = 'testpassword';

    public function testConnection(): void
    {
        $url = self::TEST_MPD_URL;
        $password = self::TEST_PASSWORD;
        $connection = new MPDConnection($url, $password);
        $connection->connect();
        $this->assertTrue($connection->isConnected());
        $connection->disconnect();

        $wrongPass = 'wrongPass';
        $connection = new MPDConnection($url, $wrongPass);
        $this->expectException(MPDConnectionException::class);
        $this->expectExceptionMessage('Password is incorrect.');
        $connection->connect();
        $connection->disconnect();
    }

    public function testFailConnection(): void
    {
        $connection = new MPDConnection('localhost:9999');
        $this->expectException(MPDConnectionException::class);
        $this->expectExceptionMessage('No connection to url localhost');
        $connection->connect();
    }


}