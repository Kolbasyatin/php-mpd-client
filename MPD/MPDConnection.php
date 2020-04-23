<?php


namespace Kolbasyatin\MPD\MPD;


use Kolbasyatin\MPD\MPD\Exceptions\MPDConnectionException;

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
            && get_resource_type($this->socket) === 'Socket'
            && socket_last_error($this->socket) === 0;
    }

    /**
     *
     */
    public function disconnect(): void
    {
        if (null !== $this->socket) {
            socket_close($this->socket);
        }
    }

    /**
     * @throws MPDConnectionException
     */
    public function connect(): void
    {
        ['host' => $url, 'port' => $port] = parse_url($this->url);
        $this->socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        socket_set_option($this->socket, SOL_SOCKET, SO_RCVTIMEO, ['sec' => 2, 'usec' => 0]);
        socket_set_option($this->socket, SOL_SOCKET, SO_SNDTIMEO, ['sec' => 2, 'usec' => 0]);
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
        while ($this->isConnected()) {
            $answer = socket_read($this->socket, 1024);
            $result = explode("\n", trim($answer));
            if ($this->checkIfAnswerGotten(end($result))) {
                return $result;
            }
        }
        throw new MPDConnectionException('You never see this exception.');
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