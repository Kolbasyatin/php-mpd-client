<?php


namespace Kolbasyatin\MPD\MPD;

use Kolbasyatin\MPD\MPD\Answers\MPDAnswerInterface;
use Kolbasyatin\MPD\MPD\Exceptions\MPDClientException;
use function in_array;

/**
 * TODO: There is a need to create appropriate commands description
 *
 * Class MpdClient
 * @method array add($URI)
 * @method array addid
 * @method array addtagid
 * @method array channels
 * @method array clear
 * @method array clearerror
 * @method array cleartagid
 * @method array close
 * @method array commands
 * @method array config
 * @method array consume
 * @method array count
 * @method array crossfade
 * @method array currentsong
 * @method array decoders
 * @method array delete
 * @method array deleteid
 * @method array disableoutput
 * @method array enableoutput
 * @method array find
 * @method array findadd
 * @method array idle
 * @method array kill
 * @method array list
 * @method array listall
 * @method array listallinfo
 * @method array listfiles
 * @method array listmounts
 * @method array listplaylist
 * @method array listplaylistinfo
 * @method array listplaylists
 * @method array load
 * @method array lsinfo
 * @method array mixrampdb
 * @method array mixrampdelay
 * @method array mount
 * @method array move
 * @method array moveid
 * @method array next
 * @method array notcommands
 * @method array outputs
 * @method array password
 * @method array pause
 * @method array ping
 * @method array play
 * @method array playid
 * @method array playlist
 * @method array playlistadd
 * @method array playlistclear
 * @method array playlistdelete
 * @method array playlistfind
 * @method array playlistid($songId = null)
 * @method array playlistinfo
 * @method array playlistmove
 * @method array playlistsearch
 * @method array plchanges
 * @method array plchangesposid
 * @method array previous
 * @method array prio
 * @method array prioid
 * @method array random
 * @method array rangeid
 * @method array readcomments
 * @method array readmessages
 * @method array rename
 * @method array repeat($bool)
 * @method array replay_gain_mode
 * @method array replay_gain_status
 * @method array rescan
 * @method array rm
 * @method array save
 * @method array search
 * @method array searchadd
 * @method array searchaddpl
 * @method array seek
 * @method array seekcur
 * @method array seekid
 * @method array sendmessage
 * @method array setvol
 * @method array shuffle
 * @method array single
 * @method array stats
 * @method array status
 * @method array sticker
 * @method array stop
 * @method array subscribe
 * @method array swap
 * @method array swapid
 * @method array tagtypes
 * @method array toggleoutput
 * @method array unmount
 * @method array unsubscribe
 * @method array update
 * @method array urlhandlers
 * @method array volume
 * @throws MPDClientException
 */
class MPDClient
{
    /** @var array */
    private const COMMAND_LIST = [
        'add',
        'addid',
        'addtagid',
        'channels',
        'clear',
        'clearerror',
        'cleartagid',
        'close',
        'commands',
        'config',
        'consume',
        'count',
        'crossfade',
        'currentsong',
        'decoders',
        'delete',
        'deleteid',
        'disableoutput',
        'enableoutput',
        'find',
        'findadd',
        'idle',
        'kill',
        'list',
        'listall',
        'listallinfo',
        'listfiles',
        'listmounts',
        'listplaylist',
        'listplaylistinfo',
        'listplaylists',
        'load',
        'lsinfo',
        'mixrampdb',
        'mixrampdelay',
        'mount',
        'move',
        'moveid',
        'next',
        'notcommands',
        'outputs',
        'password',
        'pause',
        'ping',
        'play',
        'playid',
        'playlist',
        'playlistadd',
        'playlistclear',
        'playlistdelete',
        'playlistfind',
        'playlistid',
        'playlistinfo',
        'playlistmove',
        'playlistsearch',
        'plchanges',
        'plchangesposid',
        'previous',
        'prio',
        'prioid',
        'random',
        'rangeid',
        'readcomments',
        'readmessages',
        'rename',
        'repeat',
        'replay_gain_mode',
        'replay_gain_status',
        'rescan',
        'rm',
        'save',
        'search',
        'searchadd',
        'searchaddpl',
        'seek',
        'seekcur',
        'seekid',
        'sendmessage',
        'setvol',
        'shuffle',
        'single',
        'stats',
        'status',
        'sticker',
        'stop',
        'subscribe',
        'swap',
        'swapid',
        'tagtypes',
        'toggleoutput',
        'unmount',
        'unsubscribe',
        'update',
        'urlhandlers',
        'volume',
    ];

    /** @var MPDConnection */
    private $connection;
    /**
     * @var MPDAnswerInterface|null
     */
    private $answerObject;

    /**
     * MpdClient constructor.
     * @param MPDConnection $connection
     * @param MPDAnswerInterface|null $answerClass
     */
    public function __construct(MPDConnection $connection, ?MPDAnswerInterface $answerClass = null)
    {
        $this->connection = $connection;
        $this->answerObject = $answerClass;
    }

    /**
     * @param $name
     * @param $arguments
     * @return array|MPDAnswerInterface
     * @throws MPDClientException
     */
    public function __call($name, $arguments)
    {
        if (null === $this->answerObject) {
            return $this->doCall($name, $arguments);
        }

        $answer = clone $this->answerObject;
        try {
            $result = $answer->createSuccess($this->doCall($name, $arguments), $name, $arguments);
        } catch (MPDClientException $e) {
            $result = $answer->createError($e, $name, $arguments);
        }

        return $result;
    }

    /**
     * @param $name
     * @param $arguments
     * @return array
     * @throws Exceptions\MPDConnectionException
     * @throws MPDClientException
     */
    private function doCall($name, $arguments): array
    {
        if (!in_array($name, self::COMMAND_LIST, true)) {
            throw new MPDClientException(sprintf('There is no such command %s support yet.', $name));
        }
        $command = $name.' '.implode(' ', $this->toStringFalseArguments($arguments));
        $result = $this->connection->send($command);
        $this->checkResult($result);
        array_pop($result);

        return $result;
    }


    /**
     * @param array $data
     * @throws MPDClientException
     */
    private function checkResult(array $data): void
    {
        $answerString = end($data);
        $answer = substr($answerString, 0, 2);
        if ($answer !== 'OK') {
            throw new MPDClientException($answerString);
        }

    }

    /**
     * @param array $arguments
     * @return array
     */
    private function toStringFalseArguments(array $arguments): array
    {
        array_walk(
            $arguments,
            static function (&$argument) {
                if (false === $argument) {
                    $argument = '0';
                }
            }
        );

        return $arguments;
    }
}
