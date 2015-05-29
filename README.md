Riot API for PHP
================

A PHP wrapper for [Riot's API](https://developer.riotgames.com/api/methods).

Assuming that you've copied the RiotApi folder into your project, create an instance of the API as follows:

```php
require 'RiotApi/Api.php';

$apiKey = 'YOUR-API-KEY';
$region = 'NA';
$api = new \RiotApi\Api($apiKey, $region);
```

See the [Api.php file](https://github.com/mc10/riot-api-php/blob/master/src/RiotApi/Api.php) for further details about available API method calls.
