[![Build Status](https://travis-ci.com/Kolbasyatin/PHP-MPD-CLIENT.svg?branch=master)](https://travis-ci.com/Kolbasyatin/PHP-MPD-CLIENT)

## PHP-MPD-CLIENT

- - -
###What is it?

This is a simple php written [mpd][1] client library. 

##### How to install
- - -

`composer require kolbasyatin/php-mpd-client`

##### How to use
- - -

###### Usage

```php
use Kolbasyatin\MPD\MPD\MPDClient;
use Kolbasyatin\MPD\MPD\MPDConnection;


$connection = new MPDConnection('localhost:6600', 'yourpassword');
$client = new MPDClient($connection);

$client->play(); // Send the command
$answer = $client->status(); // Get the result

```
The answer is array in example above.
- - -
You may add an answer with custom format by adding the object instance which implements `MPDAnswerInterface` to `MPDClient` constructor .
There is one simple Answer yet.

```php
use Kolbasyatin\MPD\MPD\MPDClient;
use Kolbasyatin\MPD\MPD\MPDConnection;
use Kolbasyatin\MPD\MPD\Answers\SimpleAnswer;


$connection = new MPDConnection('localhost:6600', 'yourpassword');
$answer = new SimpleAnswer();
$client = new MPDClient($connection, $answer);

$client->play();

/** @var SimpleAnswer $answer */
$answer = $client->status(); // $answer is instance of SimpleAnswer.
$answer->getAnswerAsRaw(); // You also can get a raw answer from a server.
$answer->getState(); // state

```
There are the methods list of SimpleAnswer:
````
 * Class SimpleAnswer
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

````
- - -
Since default socket timeout is long enough, it was changed to 2 sec,
however you can to change timeout as you like.
```php
use Kolbasyatin\MPD\MPD\MPDConnection;

$connection = new MPDConnection('localhost:6600', 'yourpassword');
$connection->setSocketTimeOut(1);
``` 
---
######Errors

When the error occurred, an `MPDClientException` will be thrown.

In case when SimpleAnswer was used, exception will not appear, and you may
check the status in an answer object.
```
$answer->getStatus();
$answer->getError();
```

That it is.

##### Commands list
- - -
 [Here][2] is commands list, or you can see them in MPDClient class.
 

##### Testing
- - -

Before launch `phpunit` you must start docker container.

`docker-compose -f docker/mpd/docker-compose.yml up -d`
 
 
 [1]: https://www.musicpd.org/
 [2]: https://www.musicpd.org/doc/html/protocol.html#command-lists
 
 
