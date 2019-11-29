<?php


namespace Kolbasyatin\MPD\MPD\Answers;


use Kolbasyatin\MPD\MPD\Exceptions\MPDClientException;

/**
 * Interface MPDAnswerInterface
 * @package Kolbasyatin\MPD\MPD\Answers
 */
interface MPDAnswerInterface
{
    /**
     * @param array $data
     * @param string $commandName
     * @param array $arguments
     * @return MPDAnswerInterface
     */
    public function createSuccess(array $data, string $commandName, array $arguments): MPDAnswerInterface;

    /**
     * @param MPDClientException $exception
     * @param string $commandName
     * @param array $arguments
     * @return MPDAnswerInterface
     */
    public function createError(MPDClientException $exception, string $commandName, array $arguments): MPDAnswerInterface;

    /**
     * @return mixed
     */
    public function __clone();
}