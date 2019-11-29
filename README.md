[![Build Status](https://travis-ci.com/Kolbasyatin/PHP-MPD-CLIENT.svg?branch=master)](https://travis-ci.com/Kolbasyatin/PHP-MPD-CLIENT)

## PHP-MPD-CLIENT

- - -
\#what is it?

This is a simple php [mpd][1] client 

##### How to install
- - -

`composer require kolbasyatin/php-mpd-client`

##### How to use
- - -

```php
use Kolbasyatin\MPD\MPD\MPDClient;
use Kolbasyatin\MPD\MPD\MPDConnection;


$connection = new MPDConnection('localhost:6600', 'yourpassword');
$client = new MPDClient($connection);

$client->play();
$client->status();

```
The answer is array in this case.

You can add custom answer format by adding to `MPDClient` constructor the object instance which implements `MPDAnswerInterface`.
There is one simple Answer now.

```php
use Kolbasyatin\MPD\MPD\MPDClient;
use Kolbasyatin\MPD\MPD\MPDConnection;
use Kolbasyatin\MPD\MPD\Answers\SimpleAnswer;


$connection = new MPDConnection('localhost:6600', 'yourpassword');
$answer = new SimpleAnswer();
$client = new MPDClient($connection, $answer);

$client->play();
$answer = $client->status();
```

Answer's methods see in SimpleAnswer class.


When error happens, there is `MPDClientException` throws in no answer case.
If answer exists, you can check answer status by `$answer->getStatus()` 
or see error message by `$answer->getError()` 


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
 
 
