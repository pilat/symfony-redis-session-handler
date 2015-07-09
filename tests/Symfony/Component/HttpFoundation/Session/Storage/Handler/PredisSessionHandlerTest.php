<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\HttpFoundation\Tests\Session\Storage\Handler;

use Symfony\Component\HttpFoundation\Session\Storage\Handler\PredisSessionHandler;

/**
 * @author Ryan Schumacher <ryan@38pages.com>
 * @author Vladimir Urushev <urushev@yandex.ru>
 */
class PredisSessionHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $predisClient;
    private $storage;
    public $options;
    private $lifetime;

    protected function setUp()
    {
        if (!extension_loaded('redis')) {
            $this->markTestSkipped('PredisSessionHandler requires the PHP "redis" extension.');
        }

        $this->predisClient = $this->getMock('Predis\\Client', array('get', 'set', 'del', 'setex'));
        $this->lifetime = 1;

        $this->options = array(
            'key_prefix' => 'foo:'
        );

        $this->storage = new PredisSessionHandler($this->predisClient, $this->lifetime, $this->options);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testConstructorShouldThrowExceptionForInvalidRedis()
    {
        new PredisSessionHandler(new \stdClass(), $this->lifetime);
    }

    public function testOpenMethodAlwaysReturnTrue()
    {
        $this->assertTrue($this->storage->open('test', 'test'), 'The "open" method should always return true');
    }

    public function testCloseMethodAlwaysReturnTrue()
    {
        $this->assertTrue($this->storage->close(), 'The "close" method should always return true');
    }

    public function testGcMethodAlwaysReturnTrue()
    {
        $this->assertTrue($this->storage->gc(1), 'The "gc" method should always return true');
    }

    public function testReadWithKeyPrefix()
    {
        $that = $this;

        $this->predisClient->expects($this->once())
            ->method('get')
            ->will($this->returnCallback(function ($key) use ($that) {
                $that->assertEquals('foo:bar', $key);

                return 'foo-bar';
            }));

        $this->assertEquals('foo-bar', $this->storage->read('bar'));
    }

    public function testWriteWithKeyPrefix()
    {
        $that = $this;

        $this->predisClient->expects($this->once())
            ->method('setex')
            ->will($this->returnCallback(function ($key, $data) use ($that) {
                $that->assertEquals('foo:bar', $key);

                return true;
            }));

        $this->assertTrue($this->storage->write('bar', 1));
    }

    public function testDestroyWithKeyPrefix()
    {
        $that = $this;

        $this->predisClient->expects($this->once())
            ->method('del')
            ->will($this->returnCallback(function ($key) use ($that) {
                $that->assertCount(1, $key);
                $that->assertEquals('foo:bar', $key[0]);

                return 1;
            }));

        $this->assertTrue($this->storage->destroy('bar'));
    }

    public function testGetKey()
    {
        $method = new \ReflectionMethod($this->storage, 'getKey');
        $method->setAccessible(true);

        $this->assertEquals($this->options['key_prefix'] . 'bar', $method->invoke($this->storage, 'bar'));
    }
}