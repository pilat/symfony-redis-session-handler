# Symfony2 Redis Session Handler
[![Build Status](https://travis-ci.org/pilat/symfony-redis-session-handler.png)](https://travis-ci.org/pilat/symfony-redis-session-handler)

This fork adds Predis support

## Use

```php
<?
use Symfony\Component\HttpFoundation\Session\Storage\Handler\RedisSessionHandler;

$sessionTimeout = 60 * 60 * 24 * 7; // 1 week
$redisClient = new \Redis('localhost');

new RedisSessionHandler($redisClient, $sessionTimeout);
```

### Options

This handler supports these options

- `key_prefix` - set a key prefix

#### Set a key prefix

This will allow you to manage your PHP session keys by running `key key:prefix:*`.

```php
<?
// Initalization above

$options = array(
    'key_prefix' => 'php:ses:'
);
new RedisSessionHandler($redisclient, $sessionTimeout, $options);
```
