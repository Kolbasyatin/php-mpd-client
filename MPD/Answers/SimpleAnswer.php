<?php


namespace Kolbasyatin\MPD\MPD\Answers;


use DateTime;
use Exception;
use Kolbasyatin\MPD\MPD\Exceptions\MPDClientException;

/**
 * Class SimpleAnswer
 * @package Kolbasyatin\MPD\MPD\Answers
 * @method string getVolume()
 * @method string getRepeat()
 * @method string getRandom()
 * @method string getSingle()
 * @method string getConsume()
 * @method string getPlaylist()
 * @method string getPlayListLength()
 * @method string getState()
 * @method string getSong()
 * @method string getSongId()
 */
class SimpleAnswer implements MPDAnswerInterface
{
    /** @var string */
    private $command;
    /** @var array */
    private $arguments;
    /** @var DateTime */
    private $dateTime;
    /** @var array */
    private $data = [];
    /** @var array */
    private $rawData = [];
    /** @var bool */
    private $status;
    /** @var string */
    private $error;


    /**
     * @return DateTime
     */
    public function getDateTime(): DateTime
    {
        return $this->dateTime;
    }

    /**
     * @return bool
     */
    public function getStatus(): bool
    {
        return $this->status;
    }

    /**
     * @return string
     */
    public function getError(): string
    {
        if ($this->status) {
            return 'No error.';
        }

        return $this->error;
    }

    /**
     * @return string
     */
    public function getCommand(): string
    {
        return $this->command.implode(' ', $this->arguments);
    }

    /**
     * @return array
     */
    public function getAnswerKeyList(): array
    {
        return array_keys($this->data);
    }

    /**
     * @return array
     */
    public function getAnswerAsArray(): array
    {
        return $this->data;
    }

    /**
     * @return array
     */
    public function getAnswerAsRaw(): array
    {
        return $this->rawData;
    }

    /**
     * @param $name
     * @param $arguments
     * @return mixed|null
     */
    public function __call($name, $arguments)
    {
        $property = lcfirst(substr($name, 3));
        if (strpos($name, 'get') === 0) {
            return $this->data[strtolower($property)] ?? null;
        }

        throw new \Error(
            sprintf(
                'Error : Call to undefined method %s', self::class.'::'.$name

            )
        );
    }

    /**
     * @param array $data
     * @param string $commandName
     * @param array $arguments
     * @return MPDAnswerInterface
     * @throws Exception
     */
    public function createSuccess(array $data, string $commandName, array $arguments): MPDAnswerInterface
    {
        $this->status = true;
        $this->init($commandName, $arguments);
        $this->rawData = $data;
        $this->data = $this->parseDataToHash($data);

        return $this;
    }

    /**
     * @param MPDClientException $exception
     * @param string $commandName
     * @param array $arguments
     * @return MPDAnswerInterface
     * @throws Exception
     */
    public function createError(
        MPDClientException $exception,
        string $commandName,
        array $arguments
    ): MPDAnswerInterface {
        $this->status = false;
        $this->init($commandName, $arguments);
        $this->error = $exception->getMessage();

        return $this;
    }

    /**
     * @return mixed|void
     */
    public function __clone()
    {
        // TODO: Implement __clone() method.
    }

    /**
     * @param $command
     * @param $arguments
     * @throws Exception
     */
    private function init($command, $arguments): void
    {
        $this->command = $command;
        $this->arguments = $arguments;
        $this->dateTime = new DateTime('now');
    }

    /**
     * @param array $data
     * @return array
     */
    private function parseDataToHash(array $data): array
    {
        if ($data) {
            return array_merge(
                ...array_map(
                    static function ($value) {
                        [$key, $value] = explode(':', str_replace(' ', '', $value));

                        return [$key => $value];
                    },
                    $data
                )
            );
        }

        return $data;
    }

}