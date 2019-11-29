<?php


namespace Kolbasyatin\MPD\TESTS\MPD\Answers;


use Kolbasyatin\MPD\MPD\Answers\SimpleAnswer;
use Kolbasyatin\MPD\MPD\MPDClient;
use Kolbasyatin\MPD\MPD\MPDConnection;
use Kolbasyatin\MPD\TESTS\MPD\MPDConnectionTest;
use PHPUnit\Framework\TestCase;

/**
 * Class SimpleAnswerTest
 * @package Kolbasyatin\MPD\TESTS\MPD\Answers
 */
class SimpleAnswerTest extends TestCase
{

    public function testRealAnswer(): void
    {
        $connection = new MPDConnection(MPDConnectionTest::TEST_MPD_URL, MPDConnectionTest::TEST_PASSWORD);
        $answer = new SimpleAnswer();
        $client = new MPDClient($connection, $answer);

        $client->stop();
        /** @var SimpleAnswer $actual */
        $actual = $client->status();
        $this->assertInstanceOf(SimpleAnswer::class, $actual);
        $this->assertTrue($actual->getStatus(), 'Status is not true, as expected.');
        $this->assertEquals('No error.', $actual->getError(), 'Error message wrong, as expected.');
        $this->assertNotEmpty($actual->getDateTime());
        $this->assertEquals('status', $actual->getCommand());
        $this->assertCount(11, $actual->getAnswerKeyList());
        $this->assertCount(11, $actual->getAnswerAsArray(), 'Data as array fetch error.');
        $this->assertCount(11, $actual->getAnswerAsRaw(), 'Raw data fetch error.');

        $this->dataFetchMethods($actual);

        $actual = $client->noSuchCommand();
        $this->assertInstanceOf(SimpleAnswer::class, $actual);

        $this->assertFalse($actual->getStatus(), 'Status is not false, as expected.');
        $this->assertEquals(
            'There is no such command noSuchCommand support yet.',
            $actual->getError(),
            'Error message wrong, as expected.'
        );
        $this->assertNotEmpty($actual->getDateTime());
        $this->assertEquals('noSuchCommand', $actual->getCommand());
        $this->assertEmpty($actual->getAnswerKeyList());
        $this->assertEmpty($actual->getAnswerAsArray(), 'Data as array fetch error.');
        $this->assertEmpty($actual->getAnswerAsRaw(), 'Raw data fetch error.');
    }

    /**
     * @param SimpleAnswer $answer
     */
    private function dataFetchMethods(SimpleAnswer $answer): void
    {
        $this->assertIsString($answer->getSong());
        $this->assertIsString($answer->getVolume());
        $this->assertIsString($answer->getRepeat());
        $this->assertIsString($answer->getRandom());
        $this->assertIsString($answer->getSingle());
        $this->assertIsString($answer->getConsume());
        $this->assertIsString($answer->getPlaylist());
        $this->assertIsString($answer->getPlayListLength());
        $this->assertIsString($answer->getState());
        $this->assertIsString($answer->getSong());
        $this->assertIsString($answer->getSongId());
        $this->assertNull($answer->getWrongData());
    }
}