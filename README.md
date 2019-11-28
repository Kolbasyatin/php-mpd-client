[![Build Status](https://travis-ci.com/Kolbasyatin/PHP-MPD-CLIENT.svg?branch=master)](https://travis-ci.com/Kolbasyatin/PHP-MPD-CLIENT)

##PHP-MPD-CLIENT

- - -
\#what is it?

This is a simple php [mpd] [1] client 

#####How to install
- - -

`composer require kolbasyatin/php-mpd-client`

#####How to use
- - -

```php
use Kolbasyatin\MPD\MPD\MPDClient;
use Kolbasyatin\MPD\MPD\MPDConnection;


$connection = new MPDConnection('localhost:6600', 'yourpassword');
$client = new MPDClient($connection);

$client->play();
 
```

When error happens, there is `MPDClientException` throws. 


That it is.

#####Commands list
- - -
 [Here] [2] is commands list, or you can see them in MPDClient class.
 

#####Testing
- - -

Before launch `phpunit` you must start docker container.

`docker-compose -f docker/mpd/docker-compose.yml up -d`
 
 
 [1]: https://www.musicpd.org/
 [2]: https://www.musicpd.org/doc/html/protocol.html#command-lists
 
 
