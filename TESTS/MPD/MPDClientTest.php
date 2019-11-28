<?php


namespace Kolbasyatin\MPD\TESTS\MPD;


use Kolbasyatin\MPD\MPD\Exceptions\MPDClientException;
use Kolbasyatin\MPD\MPD\Exceptions\MPDConnectionException;
use Kolbasyatin\MPD\MPD\MPDClient;
use Kolbasyatin\MPD\MPD\MPDConnection;
use PHPUnit\Framework\TestCase;

class MPDClientTest extends TestCase
{
    public function testSuccessCommand(): void
    {
        $data = ['fake result', 'OK fake result answer'];
        $connection = $this->createMock(MPDConnection::class);
        $connection
            ->expects($this->once())
            ->method('send')
            ->willReturn($data);

        $client = new MPDClient($connection);
        $actual = $client->status();
        array_pop($data);
        $this->assertEquals($data, $actual);
    }

    public function testFailMPDConnection(): void
    {
        $connection = $this->createMock(MPDConnection::class);
        $connection
            ->expects($this->once())
            ->method('send')
            ->willThrowException(new MPDConnectionException());

        $client = new MpdClient($connection);
        $this->expectException(MPDConnectionException::class);
        $client->status();

    }

    public function testFailedCommand(): void
    {
        $data = ['ACK@300 fake result'];
        $connection = $this->createMock(MPDConnection::class);
        $connection
            ->expects($this->once())
            ->method('send')
            ->willReturn($data);

        $client = new MpdClient($connection);
        $this->expectException(MPDClientException::class);
        $client->status();
    }

    public function testRealCommand(): void
    {
        $connection = new MPDConnection(MPDConnectionTest::TEST_MPD_URL, MPDConnectionTest::TEST_PASSWORD);
        $client = new MPDClient($connection);

        $client->clear();
        $listAll = $client->listall();

        $files = array_map(
            static function ($file) {
                return str_replace('file: ', '', $file);
            },
            $listAll
        );

        $this->assertCount(2, $files);

        /** @var MpdClient $client */
        foreach ($files as $file) {
            $client->add("\"$file\"");
        }

        $client->repeat(true);
        $result = $client->play();
        $this->assertEmpty($result);

        $actual = $client->status();
        $values = $this->getValuesAsArray($actual);

        $this->assertEquals('play', $values['state']);
        $this->assertEquals(2, (int)$values['playlistlength']);
        $nextSong = (int)$values['nextsong'];
        $client->next();

        $actual = $client->status();
        $values = $this->getValuesAsArray($actual);
        $this->assertNotEquals($nextSong, (int)$values['nextsong']);

        $client->repeat(false);
        $client->stop();

        $actual = $client->status();
        $values = $this->getValuesAsArray($actual);
        $this->assertEquals('stop', $values['state']);
        $this->assertEquals('0', $values['repeat']);

        $client->disconnect();

    }

    private function getValuesAsArray(array $answer): array
    {
        return array_merge(
            ...array_map(
                static function ($value) {
                    [$key, $value] = explode(':', str_replace(' ', '', $value));

                    return [$key => $value];
                },
                $answer
            )
        );
    }
}