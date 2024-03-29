<?php


namespace Kolbasyatin\MPD\MPD;


use Kolbasyatin\MPD\MPD\Exceptions\MPDConnectionException;
use Socket;

/**
 * Class MPDConnection
 * @package Kolbasyatin\MPD\MPD
 */
class MPDConnection
{
    /**
     * @var
     */
    private $socket;

    /** @var string */
    private $url;

    /** @var string */
    private $password;

    /** @var int */
    private $socketTimeOut = 2;

    /**
     * MPDConnection constructor.
     * @param string $url
     * @param string $password
     */
    public function __construct(string $url, string $password = '')
    {
        $this->url = $url;
        $this->password = $password;
    }


    /**
     * @param string|array $command
     * @return array
     * @throws MPDConnectionException
     */
    public function send($command): array
    {
        if (!$this->isConnected()) {
            $this->connect();

        }
        if (!is_array($command)) {
            $command = (array)$command;
        }
        $this->sendQuestion($command);

        return $this->receiveAnswer();
    }


    /**
     * @return bool
     */
    public function isConnected(): bool
    {
        return $this->socket
            && $this->checkSocketResourceType()
            && socket_last_error($this->socket) === 0;
    }

    private function checkSocketResourceType(): bool
    {
        if (PHP_VERSION_ID < 80000) {
            return get_resource_type($this->socket) === 'Socket';
        }

        return $this->socket instanceof Socket;
    }

    /**
     * Close the socket
     */
    public function disconnect(): void
    {
        if (null !== $this->socket) {
            socket_close($this->socket);
        }
    }

    /**
     * Create, setup and connect
     * @throws MPDConnectionException
     */
    public function connect(): void
    {
        ['host' => $url, 'port' => $port] = parse_url($this->url);
        $this->socketSetup();
        //** Sorry for the '@', no another way.  */
        if (false === @socket_connect($this->socket, $url, $port)) {
            throw new MPDConnectionException('No connection to url '.$url);
        }
        $this->skipFirstAnswer();
        if ($this->password) {
            $this->sendPassword();
            $answer = $this->receiveAnswer();
            $err = 'ACK [3@0]';
            if (strncmp($err, trim(end($answer)), strlen($err)) === 0) {
                $this->disconnect();
                throw new MPDConnectionException('Password is incorrect.');
            }
        }
    }

    /**
     * @throws MPDConnectionException
     */
    private function socketSetup(): void
    {
        $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        if (null === $socket) {
            throw new MPDConnectionException('Can\'t setup socket cause socket isn\'t created');
        }
        socket_set_option($socket, SOL_SOCKET, SO_RCVTIMEO, ['sec' => $this->socketTimeOut, 'usec' => 0]);
        socket_set_option($socket, SOL_SOCKET, SO_SNDTIMEO, ['sec' => $this->socketTimeOut, 'usec' => 0]);
        $this->socket = $socket;
    }

    public function setSocketTimeOut(int $sec): void
    {
        if (!$this->isConnected()) {
            $this->socketTimeOut = $sec;
        }
        if ($this->isConnected()) {
            throw new MPDConnectionException('You can\'t set socket timeout while the connection is active');
        }
    }

    /**
     * @throws MPDConnectionException
     */
    private function sendPassword(): void
    {
        if ($this->password) {
            $this->sendQuestion((array)sprintf('password %s', $this->password));
        }
    }

    /**
     * @throws MPDConnectionException
     */
    private function skipFirstAnswer(): void
    {
        $this->receiveAnswer();
    }

    /**
     * @param array $questionArray
     * @throws MPDConnectionException
     */
    private function sendQuestion(array $questionArray): void
    {
        $question = implode("\n", $questionArray) . "\n";
        $result = socket_write($this->socket, $question, 1024);
        if (false === $result) {
            throw new MPDConnectionException('Can not write to socket!');
        }
    }

    /**
     * @return array|null
     * @throws MPDConnectionException
     */
    private function receiveAnswer(): ?array
    {
        $result = array();
        while ($this->isConnected()) {
            $answer = socket_read($this->socket, 4096, PHP_NORMAL_READ);
            $tmpResult = explode("\n", trim($answer));
            $result = array_merge($result,$tmpResult);
            if ($this->checkIfAnswerGotten(end($result))) {
                return $result;
            }
        }
        throw new MPDConnectionException('You never will see this exception.');
    }

    /**
     * @param string $answer
     * @return bool
     */
    private function checkIfAnswerGotten(string $answer): bool
    {
        return (strncmp('OK', $answer, strlen('OK')) === 0) || (strncmp('ACK', $answer, strlen('ACK')) === 0);
    }
}
