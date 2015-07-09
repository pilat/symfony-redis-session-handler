<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\HttpFoundation\Session\Storage\Handler;

/**
 * PredisSessionHandler
 *
 * @author Markus Bachmann <markus.bachmann@bachi.biz>
 * @author Ryan Schumacher <ryan@38pages.com>
 * @author Vladimir Urushev <urushev@yandex.ru>
 */
class PredisSessionHandler implements \SessionHandlerInterface
{
    /**
     * @var \Predis\Client
     */
    private $client;

    /**
     * @var integer
     */
    private $lifetime;

    /**
     * @var array
     */
    private $options;

    /**
     * Constructor
     *
     * List of available options:
     *  * key_prefix: The key prefix [default: '']
     *
     * @param \Predis\ClientInterface $predisClient The redis instance
     * @param integer $lifetime Max lifetime in seconds to keep sessions stored.
     * @param array $options Options for the session handler
     *
     * @throws \InvalidArgumentException When Redis instance not provided
     */
    public function __construct($predisClient, $lifetime, array $options = array())
    {
        if (!$predisClient instanceof \Predis\ClientInterface) {
            throw new \InvalidArgumentException('Predis\Client instance required');
        }

        $this->client = $predisClient;
        $this->lifetime = $lifetime;

        if(!is_array($options)) $options = array();
        $this->options = array_merge(array(
            'key_prefix' => ''
        ), $options);
    }

    /**
     * {@inheritDoc}
     */
    public function open($savePath, $sessionName)
    {
        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function read($sessionId)
    {
        $key = $this->getKey($sessionId);
        return (string) $this->client->get($key);
    }

    /**
     * {@inheritDoc}
     */
    public function write($sessionId, $data)
    {
        $key = $this->getKey($sessionId);
        return $this->client->setex($key, $this->lifetime, $data);
    }

    /**
     * {@inheritDoc}
     */
    public function destroy($sessionId)
    {
        $key = $this->getKey($sessionId);
        return 1 === $this->client->del([$key]);
    }

    /**
     * {@inheritDoc}
     */
    public function gc($lifetime)
    {
        /* Note: Redis will handle the expiration of keys with SETEX command
         * See: http://redis.io/commands/setex
         */
        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function close()
    {
        return true;
    }

    /**
     * Get the redis key
     *
     * @param string $sessionId session id
     *
     * @return string
     */
    protected function getKey($sessionId)
    {
        if(is_string($this->options['key_prefix'])) {
            return $this->options['key_prefix'].$sessionId;
        }
        return $sessionId;
    }
}