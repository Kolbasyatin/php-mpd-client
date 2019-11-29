<?php


namespace Kolbasyatin\MPD\TESTS\MPD;


use Kolbasyatin\MPD\MPD\Exceptions\MPDConnectionException;
use Kolbasyatin\MPD\MPD\MPDConnection;
use PHPUnit\Framework\TestCase;

/**
 * Class MPDConnectionTest
 * @package Kolbasyatin\MPD\TESTS\MPD
 */
class MPDConnectionTest extends TestCase
{
    /** @var string  */
    public const TEST_MPD_URL = 'localhost:6600';
    /** @var string  */
    public const TEST_PASSWORD = 'testpassword';

    /**
     * @throws MPDConnectionException
     */
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

    /**
     * @throws MPDConnectionException
     */
    public function testFailConnection(): void
    {
        $connection = new MPDConnection('localhost:9999');
        $this->expectException(MPDConnectionException::class);
        $this->expectExceptionMessage('No connection to url localhost');
        $connection->connect();
    }


}