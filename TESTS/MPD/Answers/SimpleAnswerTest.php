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
        self::assertInstanceOf(SimpleAnswer::class, $actual);
        self::assertTrue($actual->getStatus(), 'Status is not true, as expected.');
        self::assertEquals('No error.', $actual->getError(), 'Error message wrong, as expected.');
        self::assertNotEmpty($actual->getDateTime());
        self::assertEquals('status', $actual->getCommand());
        self::assertNotEmpty($actual->getAnswerKeyList());
        self::assertNotEmpty($actual->getAnswerAsArray(), 'Data as array fetch error.');
        self::assertNotEmpty($actual->getAnswerAsRaw(), 'Raw data fetch error.');

        $this->dataFetchMethods($actual);

        $actual = $client->noSuchCommand();
        self::assertInstanceOf(SimpleAnswer::class, $actual);

        self::assertFalse($actual->getStatus(), 'Status is not false, as expected.');
        self::assertEquals(
            'There is no such command noSuchCommand support yet.',
            $actual->getError(),
            'Error message wrong, as expected.'
        );
        self::assertNotEmpty($actual->getDateTime());
        self::assertEquals('noSuchCommand', $actual->getCommand());
        self::assertEmpty($actual->getAnswerKeyList());
        self::assertEmpty($actual->getAnswerAsArray(), 'Data as array fetch error.');
        self::assertEmpty($actual->getAnswerAsRaw(), 'Raw data fetch error.');
    }

    /**
     * @param SimpleAnswer $answer
     */
    private function dataFetchMethods(SimpleAnswer $answer): void
    {
        $list = $answer->getAnswerKeyList();
        foreach ($list as $key) {
            $method = 'get'.ucfirst($key);
            self::assertIsString($answer->$method());
        }
        self::assertNull($answer->getWrongData());
    }
}